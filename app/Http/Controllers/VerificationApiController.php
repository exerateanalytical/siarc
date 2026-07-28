<?php

namespace App\Http\Controllers;

use App\Support\CertificateDirectory;
use App\Support\CertificationAuthority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The machine-readable half of certificate verification.
 *
 * Everything the platform prints carries a verification address, and until now
 * that address only ever led to an HTML page. The readers these documents are
 * actually written for — a registrar accessioning an object, an underwriter
 * pricing a policy, a customs officer clearing a crate — do not read pages.
 * They check from inside their own system, which speaks HTTP and JSON. A
 * verification service reachable only by a human with a browser is, from their
 * side, indistinguishable from no verification service at all.
 *
 * Four decisions here are worth the words:
 *
 * **A status is an answer, not an error.** Revoked, superseded and expired all
 * return 200. It is tempting to treat "this certificate is no good" as a
 * failure and reach for 4xx, and it would be a serious mistake: a 404 is what a
 * caller also gets from a typo, a stale deployment or an outage, and a seller
 * holding a revoked certificate could truthfully say the check "didn't come
 * back". 404 is reserved for a reference no register holds, and 400 for one
 * that could not be a reference at all.
 *
 * **No personal data, at any status.** This endpoint is unauthenticated and the
 * certificate namespace is sequential and therefore guessable. Owner names,
 * addresses, contact details and identity-document fragments would make a
 * walkable list of who holds which valuable object and where — a shopping list
 * dressed as a public good. The question a verifier asks is "is this document
 * good", never "who owns it", and the answer here is built from a fixed field
 * list rather than by filtering a database row, so a column added later cannot
 * leak by default.
 *
 * **The response carries the exact bytes that were signed.** A verdict this
 * server computes about its own signature is worth very little; a caller who
 * has to trust our "signature: valid" has gained nothing over trusting the
 * paper. So the response ships the canonical payload string verbatim, the
 * detached signature, the key id and the JWKS URL — enough to verify with any
 * Ed25519 library, offline, years from now, against a key the caller pinned
 * themselves. That capability is the point of the whole endpoint.
 *
 * **The spec is generated, not written.** /api/v1/openapi.json is derived from
 * the same FIELDS table the responses are assembled from, because a
 * hand-written document describing fields nobody built is worse than no
 * document: it is believed, and then it is wrong.
 *
 * Resolution itself is not reimplemented here. CertificateDirectory is the one
 * authority on which register holds a reference and what its status means,
 * including the constant-time PIN comparison; this controller is a
 * presentation layer over it.
 */
class VerificationApiController extends Controller
{
    /**
     * A reference is a certificate number or a UUID. Both are drawn from
     * [A-Za-z0-9-] and neither exceeds the directory's 64-character ceiling, so
     * anything else is not a mistyped certificate, it is not a certificate.
     * Rejecting it with 400 rather than 404 keeps "we have no such document"
     * meaning exactly that.
     */
    private const REFERENCE = '/^[A-Za-z0-9\-]{1,64}$/';

    /**
     * The single source of truth for the response shape.
     *
     * Both the JSON that goes over the wire and the OpenAPI schema are built
     * from this table, so the document cannot describe a field the endpoint
     * does not return, nor miss one it does. The test asserts that parity.
     *
     * @var array<string, array<string, mixed>>
     */
    private const FIELDS = [
        'reference' => [
            'type'        => 'string',
            'description' => 'The reference as it was asked for, echoed back so a caller batching lookups can match answers to questions.',
            'example'     => 'AHC-COA-2026-000012345',
        ],
        'type' => [
            'type'        => 'string',
            'enum'        => ['coa', 'otc', 'avc', 'eac', 'unknown'],
            'description' => 'Which register holds the document. "unknown" when no register does — never the shape of the reference guessed at, which would confirm a number format to somebody probing.',
        ],
        'type_name' => [
            'type'        => ['string', 'null'],
            'description' => 'The plain English name of that document type.',
            'example'     => 'Certificate of Authenticity',
        ],
        'status' => [
            'type'        => 'string',
            'enum'        => ['valid', 'superseded', 'expired', 'revoked', 'pin_mismatch', 'notfound', 'malformed'],
            'description' => 'The verification verdict. Every value other than notfound and malformed is returned with HTTP 200: an unusable certificate is an answer, not a failure of the service.',
        ],
        'found' => [
            'type'        => 'boolean',
            'description' => 'Whether a register holds this reference and released its details. False for notfound, malformed, and for a PIN that did not match.',
        ],
        'issued_at' => [
            'type'        => ['string', 'null'],
            'format'      => 'date-time',
            'description' => 'ISO 8601 date and time of issue. Null when the certificate was not released.',
        ],
        'expires_at' => [
            'type'        => ['string', 'null'],
            'format'      => 'date-time',
            'description' => 'ISO 8601 expiry, where the document type has one. Null means the document does not expire, not that it never will.',
        ],
        'verification_count' => [
            'type'        => ['integer', 'null'],
            'description' => 'How many times this certificate has been verified, including the present call.',
        ],
        'signature' => [
            'type'        => 'object',
            'description' => 'The Ed25519 signature over the certified facts, and everything needed to check it without this server.',
            'properties'  => [
                'state'          => ['type' => 'string', 'enum' => ['valid', 'invalid', 'unsigned', 'unknown'], 'description' => 'This server\'s own verdict. Informative only — verify the payload yourself.'],
                'kid'            => ['type' => ['string', 'null'], 'description' => 'Identifier of the signing key, matching a "kid" in the JWKS.'],
                'algorithm'      => ['type' => 'string', 'description' => 'JOSE algorithm name.', 'example' => 'EdDSA'],
                'curve'          => ['type' => 'string', 'description' => 'Signature curve.', 'example' => 'Ed25519'],
                'value'          => ['type' => ['string', 'null'], 'description' => 'The detached signature, base64url encoded, unpadded.'],
                'payload'        => ['type' => ['string', 'null'], 'description' => 'The exact byte string that was signed. Verify this, not a reconstruction of it.'],
                'payload_recipe' => ['type' => 'object', 'description' => 'How that payload is composed, so a verifier can rebuild it from a printed certificate.'],
                'jwks_url'       => ['type' => 'string', 'format' => 'uri', 'description' => 'Where the public key is published, in RFC 8037 JWK form.'],
            ],
        ],
        'links' => [
            'type'        => 'object',
            'description' => 'Addresses for the human-facing documents. No personal data is reachable without the credentials each of those pages requires.',
            'properties'  => [
                'self'                    => ['type' => 'string', 'format' => 'uri'],
                'document'                => ['type' => ['string', 'null'], 'format' => 'uri', 'description' => 'The human-readable certificate.'],
                'verification_page'       => ['type' => 'string', 'format' => 'uri', 'description' => 'The human verification page for this reference.'],
                'jwks'                    => ['type' => 'string', 'format' => 'uri'],
                'certification_authority' => ['type' => 'string', 'format' => 'uri'],
            ],
        ],
        'issuer' => [
            'type'        => 'object',
            'description' => 'Who signed. A private company incorporated in Cameroon; not a public administration and not acting for one.',
            'properties'  => [
                'name'    => ['type' => 'string'],
                'country' => ['type' => 'string', 'description' => 'ISO 3166-1 alpha-2.', 'example' => 'CM'],
            ],
        ],
        'checked_at' => [
            'type'        => 'string',
            'format'      => 'date-time',
            'description' => 'ISO 8601 time this answer was computed. A verification is a statement about a moment, not a permanent fact.',
        ],
    ];

    /* ──────────────────────────── Verification ─────────────────────────── */

    public function verify(Request $request, string $reference): JsonResponse
    {
        $pin = $request->query('pin');
        $pin = is_string($pin) && $pin !== '' ? $pin : null;

        if (! preg_match(self::REFERENCE, $reference)) {
            return $this->json($this->envelope($reference, [
                'type'   => 'unknown',
                'status' => 'malformed',
            ]), 400);
        }

        $result = CertificateDirectory::resolve($reference, $pin);

        // 404 only where no register holds the reference. Everything else — a
        // revocation, a supersession, an expiry, a PIN that did not match — is
        // a successful answer to the question that was asked.
        $code = $result['status'] === 'notfound' ? 404 : 200;

        return $this->json($this->envelope($reference, $result), $code);
    }

    /**
     * Mirror of /.well-known/jwks.json.
     *
     * The same bytes at an address inside the API surface, because an
     * integration configured with an API base URL should not have to know that
     * one of its four calls lives at the site root, and because the CORS policy
     * that lets a browser-side tool read the rest applies to api/* only.
     */
    public function jwks(): JsonResponse
    {
        return $this->json(CertificationAuthority::jwks(), 200)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function openapi(): JsonResponse
    {
        return $this->json($this->spec(), 200)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /* ───────────────────────────── Assembly ────────────────────────────── */

    /**
     * Builds the response from FIELDS, never from the database row.
     *
     * This is the mechanical guarantee behind "no personal data": the row that
     * CertificateDirectory hands back holds owner names, consignee details and
     * more, and none of it is reachable from here because nothing iterates over
     * it. A column added to any register next year appears in no response.
     */
    private function envelope(string $reference, array $r): array
    {
        $cert  = $r['certificate'] ?? null;
        $type  = $r['type'] ?? 'unknown';
        $found = $cert !== null;

        $payload = null;
        $value   = null;

        if ($found && ($cert->ca_signature ?? null)) {
            $payload = CertificationAuthority::payload(
                $type,
                (string) $cert->certificate_no,
                (string) $cert->content_hash,
                $cert->issued_at ? Carbon::parse($cert->issued_at)->toIso8601String() : null
            );
            $value = (string) $cert->ca_signature;
        }

        return [
            'reference'          => $reference,
            'type'               => $type,
            'type_name'          => CertificateDirectory::name($type, 'en'),
            'status'             => $r['status'],
            'found'              => $found,
            'issued_at'          => $this->iso($r['issued_at'] ?? null),
            'expires_at'         => $this->iso($r['expires_at'] ?? null),
            'verification_count' => $r['verification_count'] ?? null,
            'signature'          => [
                'state'          => $r['signature']['state'] ?? 'unknown',
                'kid'            => $r['signature']['kid'] ?? null,
                'algorithm'      => CertificationAuthority::ALG,
                'curve'          => CertificationAuthority::CRV,
                'value'          => $value,
                'payload'        => $payload,
                'payload_recipe' => $this->recipe(),
                'jwks_url'       => route('ca.jwks'),
            ],
            'links' => [
                'self'                    => route('api.verification.verify', ['reference' => $reference]),
                'document'                => $this->documentLink($type, $r['document_url'] ?? null),
                'verification_page'       => route('product.certificate.verify', ['ref' => $reference]),
                'jwks'                    => route('api.verification.jwks'),
                'certification_authority' => route('ca.page'),
            ],
            'issuer' => [
                'name'    => (string) config('certificates.ca.name', 'ArtisanHub237 Certification Authority'),
                'country' => 'CM',
            ],
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * How the signed bytes are composed.
     *
     * Written out rather than merely pointed at, so a verifier holding only a
     * printed certificate and this response can rebuild the payload character
     * for character. The joiner is a single newline; the type is lowercased;
     * an absent issue date contributes an empty final line rather than being
     * dropped, which would shorten the string and break the signature.
     */
    private function recipe(): array
    {
        return [
            'algorithm' => 'Ed25519 detached signature (RFC 8032), base64url encoded without padding',
            'join'      => "\n",
            'fields'    => ['literal:ah237.certificate.v1', 'type (lowercase)', 'certificate_no', 'content_hash', 'issued_at (ISO 8601, empty string when absent)'],
            'steps'     => [
                'Join the five field values with a single newline, in the order given.',
                'Fetch the public key from jwks_url and select the key whose "kid" matches signature.kid.',
                'Base64url-decode the JWK "x" member to 32 raw bytes, and signature.value to 64 raw bytes.',
                'Verify the detached Ed25519 signature over the joined string.',
            ],
            'note' => 'signature.payload in this response is that joined string already. Rebuild it yourself only when verifying from paper.',
        ];
    }

    /**
     * The human document's address, where publishing it names nobody.
     *
     * Three of the four document pages are addressed either by the certificate
     * reference itself or by the slug of the object certified, and neither of
     * those tells a caller anything they did not already supply. The artisan
     * verification certificate is different: its page is addressed by the
     * business slug, and a business slug for a sole trader is the artisan's own
     * name spelled out in the URL. Returning it would put a name in a response
     * whose entire premise is that it contains none, so that one link is
     * withheld and the reference-addressed verification page — which a human
     * can follow onward — is offered instead.
     *
     * The honest limit: a product slug can also carry its maker's name when the
     * maker named the piece after themselves. This rule removes the systematic
     * leak, not every possible one.
     */
    private function documentLink(string $type, ?string $url): ?string
    {
        return $type === 'avc' ? null : $url;
    }

    private function iso(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->toIso8601String() : null;
    }

    private function json(array $body, int $status): JsonResponse
    {
        return response()->json($body, $status, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /* ────────────────────────────── OpenAPI ────────────────────────────── */

    /**
     * OpenAPI 3.1 generated from FIELDS and from the routes themselves.
     *
     * Nothing here is asserted about the implementation from memory: the
     * response schema is the field table the responses are built from, and the
     * paths are the URIs the router actually has.
     */
    private function spec(): array
    {
        $verify = route('api.verification.verify', ['reference' => '__REF__'], false);
        $verify = str_replace('__REF__', '{reference}', $verify);

        return [
            'openapi' => '3.1.0',
            'info'    => [
                'title'       => 'ArtisanHub237 Certificate Verification API',
                'version'     => '1.0.0',
                'description' => 'Public, unauthenticated, read-only verification of the certificates ArtisanHub237 issues. Returns the status of a document and everything needed to verify its Ed25519 signature offline. It deliberately returns no information about the people or organisations named on a certificate.',
                'contact'     => ['url' => route('ca.page')],
            ],
            'servers' => [['url' => url('/')]],
            'paths'   => [
                $verify => [
                    'get' => [
                        'operationId' => 'verifyCertificate',
                        'summary'     => 'Verify a certificate by number or UUID',
                        'description' => 'A resolved certificate returns 200 whatever its status — valid, superseded, expired, revoked. 404 means no register holds the reference; 400 means the reference could not be one.',
                        'parameters'  => [
                            [
                                'name'        => 'reference',
                                'in'          => 'path',
                                'required'    => true,
                                'description' => 'Certificate number or UUID.',
                                'schema'      => ['type' => 'string', 'pattern' => '^[A-Za-z0-9\\-]{1,64}$'],
                            ],
                            [
                                'name'        => 'pin',
                                'in'          => 'query',
                                'required'    => false,
                                'description' => 'Optional verification PIN printed on the certificate. When supplied and wrong, the answer is status pin_mismatch with none of the certificate\'s contents.',
                                'schema'      => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => $this->responseObject('The verification result, whatever the verdict.'),
                            '400' => $this->responseObject('The reference is malformed. status is "malformed".'),
                            '404' => $this->responseObject('No register holds this reference. status is "notfound".'),
                            '429' => ['description' => 'Rate limit exceeded.'],
                        ],
                    ],
                ],
                route('api.verification.jwks', [], false) => [
                    'get' => [
                        'operationId' => 'jwks',
                        'summary'     => 'The certification authority public keys',
                        'description' => 'RFC 8037 OKP JWK Set, identical to /.well-known/jwks.json.',
                        'responses'   => [
                            '200' => [
                                'description' => 'JWK Set.',
                                'content'     => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/JwkSet']]],
                            ],
                        ],
                    ],
                ],
                route('api.verification.openapi', [], false) => [
                    'get' => [
                        'operationId' => 'openapi',
                        'summary'     => 'This document',
                        'responses'   => ['200' => ['description' => 'The OpenAPI description of this API.']],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'VerificationResult' => [
                        'type'        => 'object',
                        'description' => 'Contains no personal data by construction: it is assembled from a fixed field list, not from a database row.',
                        'required'    => array_keys(self::FIELDS),
                        'properties'  => self::FIELDS,
                    ],
                    'JwkSet' => [
                        'type'       => 'object',
                        'properties' => [
                            'keys' => [
                                'type'  => 'array',
                                'items' => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'kty' => ['type' => 'string', 'example' => 'OKP'],
                                        'crv' => ['type' => 'string', 'example' => 'Ed25519'],
                                        'alg' => ['type' => 'string', 'example' => 'EdDSA'],
                                        'use' => ['type' => 'string', 'example' => 'sig'],
                                        'kid' => ['type' => 'string'],
                                        'x'   => ['type' => 'string', 'description' => 'base64url public key, 32 bytes decoded.'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function responseObject(string $description): array
    {
        return [
            'description' => $description,
            'content'     => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/VerificationResult']]],
        ];
    }
}
