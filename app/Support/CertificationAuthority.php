<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The ArtisanHub237 Certification Authority.
 *
 * Every certificate the platform issues carries a real detached signature over
 * its certified facts, made with an Ed25519 private key this server holds and
 * verifiable by anyone with the matching public key — which is published at
 * /.well-known/jwks.json in the standard JOSE form (kty OKP, crv Ed25519,
 * alg EdDSA, RFC 8032/8037).
 *
 * That last part is the whole point, and the difference between this and the
 * HMAC it replaces. An HMAC keyed on the application secret proves a document
 * came from us only to us: a museum or an insurer cannot check it without
 * asking us and believing the answer. A published public key means a third
 * party can verify a certificate offline, years later, against a key they
 * pinned themselves — which is what "digitally signed" is understood to mean
 * on a provenance document, and what the specification asks for.
 *
 * Ed25519 rather than RSA because this build of PHP has openssl without an
 * openssl.cnf and cannot generate RSA or EC keys at all, while libsodium needs
 * no configuration; it is also the modern default for new signing systems and
 * produces 64-byte signatures that fit on a printed page.
 *
 * The private key never enters the repository or the web root. It lives at the
 * path in config('certificates.ca.key_path'), and the platform degrades to
 * unsigned-but-hashed certificates if it is absent rather than refusing to
 * issue — an artisan should not be blocked from registering a product because
 * an operator has not run the key ceremony yet.
 */
class CertificationAuthority
{
    /** Signature scheme, named on the certificate and in the JWKS. */
    public const ALG = 'EdDSA';
    public const CRV = 'Ed25519';

    private static ?array $cache = null;

    /* ───────────────────────────── Key material ────────────────────────── */

    private static function keyPath(): string
    {
        return (string) config('certificates.ca.key_path', storage_path('app/ca/ah237-ca.key'));
    }

    /**
     * Creates the authority's key pair. Refuses to overwrite an existing key:
     * replacing it would silently invalidate every certificate ever issued, so
     * that has to be a deliberate, separate act.
     */
    public static function generate(bool $force = false): array
    {
        $path = self::keyPath();

        if (is_file($path) && ! $force) {
            throw new RuntimeException("A signing key already exists at {$path}. Refusing to overwrite it — every certificate issued so far was signed with it.");
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0700, true);
        }

        $pair   = sodium_crypto_sign_keypair();
        $secret = sodium_crypto_sign_secretkey($pair);
        $public = sodium_crypto_sign_publickey($pair);

        file_put_contents($path, base64_encode($secret), LOCK_EX);
        @chmod($path, 0600);
        file_put_contents($path . '.pub', base64_encode($public), LOCK_EX);

        self::$cache = null;

        return ['kid' => self::kid($public), 'public' => base64_encode($public), 'path' => $path];
    }

    /** @return array{secret:?string, public:?string} raw bytes */
    private static function keys(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = self::keyPath();

        if (! is_file($path)) {
            // A public key alone is enough to verify, which matters on a
            // read-only replica that should never hold signing material.
            $pub = is_file($path . '.pub') ? base64_decode((string) file_get_contents($path . '.pub')) : null;

            return self::$cache = ['secret' => null, 'public' => $pub ?: null];
        }

        $secret = base64_decode((string) file_get_contents($path));

        return self::$cache = [
            'secret' => $secret,
            'public' => sodium_crypto_sign_publickey_from_secretkey($secret),
        ];
    }

    public static function isConfigured(): bool
    {
        return (bool) self::keys()['secret'];
    }

    public static function publicKey(): ?string
    {
        $pub = self::keys()['public'];

        return $pub ? base64_encode($pub) : null;
    }

    /** Stable key identifier: the first 16 bytes of SHA-256 over the public key. */
    public static function kid(?string $publicRaw = null): ?string
    {
        $pub = $publicRaw ?? self::keys()['public'];

        return $pub ? strtoupper(substr(hash('sha256', $pub), 0, 32)) : null;
    }

    /* ────────────────────────────── Signing ────────────────────────────── */

    /**
     * Detached signature over the canonical payload, base64url encoded.
     *
     * Returns null when no signing key is installed, so callers store null
     * rather than something that looks like a signature but verifies against
     * nothing.
     */
    public static function sign(string $payload): ?string
    {
        $secret = self::keys()['secret'];

        if (! $secret) {
            return null;
        }

        return self::b64u(sodium_crypto_sign_detached($payload, $secret));
    }

    public static function verify(string $payload, ?string $signature): bool
    {
        $pub = self::keys()['public'];

        if (! $pub || ! $signature) {
            return false;
        }

        $raw = self::unb64u($signature);

        if ($raw === false || strlen($raw) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return false;
        }

        try {
            return sodium_crypto_sign_verify_detached($raw, $payload, $pub);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * The exact bytes that get signed.
     *
     * Pinned here and nowhere else: a verifier reconstructing this string from
     * the printed fields is what makes an offline check possible, so the recipe
     * must be one thing in one place.
     */
    public static function payload(string $type, string $certificateNo, string $contentHash, ?string $issuedAt = null): string
    {
        return implode("\n", [
            'ah237.certificate.v1',
            strtolower($type),
            $certificateNo,
            $contentHash,
            $issuedAt ?? '',
        ]);
    }

    /** Signs a certificate and returns [signature, kid]. */
    public static function signCertificate(string $type, string $certificateNo, string $contentHash, ?string $issuedAt = null): array
    {
        $payload = self::payload($type, $certificateNo, $contentHash, $issuedAt);

        return [self::sign($payload), self::kid()];
    }

    public static function verifyCertificate(string $type, string $certificateNo, string $contentHash, ?string $issuedAt, ?string $signature): bool
    {
        return self::verify(self::payload($type, $certificateNo, $contentHash, $issuedAt), $signature);
    }

    /* ────────────────────────────── Publication ────────────────────────── */

    /** RFC 8037 OKP key, the form a third-party verifier expects. */
    public static function jwks(): array
    {
        $pub = self::keys()['public'];

        if (! $pub) {
            return ['keys' => []];
        }

        return ['keys' => [[
            'kty' => 'OKP',
            'crv' => self::CRV,
            'alg' => self::ALG,
            'use' => 'sig',
            'kid' => self::kid(),
            'x'   => self::b64u($pub),
        ]]];
    }

    /* ─────────────────── Tamper-evident certificate log ────────────────── */

    /**
     * Appends an event to the authority's hash chain.
     *
     * Each entry commits to the one before it, so altering or removing any past
     * entry breaks every link after it. This is the property the specification
     * asks a blockchain for, obtained without one: it does not need a network
     * of strangers, only that the chain head be checkable — which `head()` and
     * the verification page expose.
     *
     * It is honest about what it is not: a single operator holding the whole
     * log could in principle rewrite all of it and recompute the chain. What it
     * defeats is silent alteration of history, which is the realistic threat.
     */
    public static function appendToChain(string $type, int $id, string $event, ?string $note = null, ?string $actor = null): string
    {
        $prev = DB::table('certificate_events')->orderByDesc('id')->value('entry_hash') ?: str_repeat('0', 64);

        $occurredAt = now();
        $entry      = hash('sha256', implode('|', [$prev, $type, $id, $event, $note ?? '', $occurredAt->toIso8601String()]));

        DB::table('certificate_events')->insert([
            'certificate_type' => $type,
            'certificate_id'   => $id,
            'event'            => $event,
            'actor_user_id'    => $actor,
            'note'             => $note,
            'prev_hash'        => $prev,
            'entry_hash'       => $entry,
            'occurred_at'      => $occurredAt,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return $entry;
    }

    /** The current chain head — what an auditor pins to detect later rewrites. */
    public static function head(): ?string
    {
        return DB::table('certificate_events')->orderByDesc('id')->value('entry_hash');
    }

    /**
     * Recomputes the whole chain.
     *
     * @return array{ok:bool, checked:int, broken_at:?int}
     */
    public static function verifyChain(): array
    {
        $prev    = str_repeat('0', 64);
        $checked = 0;

        foreach (DB::table('certificate_events')->orderBy('id')->cursor() as $row) {
            $expected = hash('sha256', implode('|', [
                $prev, $row->certificate_type, $row->certificate_id, $row->event,
                $row->note ?? '', \Illuminate\Support\Carbon::parse($row->occurred_at)->toIso8601String(),
            ]));

            if ($row->prev_hash !== $prev || $row->entry_hash !== $expected) {
                return ['ok' => false, 'checked' => $checked, 'broken_at' => (int) $row->id];
            }

            $prev = $row->entry_hash;
            $checked++;
        }

        return ['ok' => true, 'checked' => $checked, 'broken_at' => null];
    }

    /* ────────────────────────────── Helpers ────────────────────────────── */

    private static function b64u(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function unb64u(string $s): string|false
    {
        return base64_decode(strtr($s, '-_', '+/'), true);
    }
}
