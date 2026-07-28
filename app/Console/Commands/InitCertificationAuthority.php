<?php

namespace App\Console\Commands;

use App\Support\CertificationAuthority;
use Illuminate\Console\Command;

/**
 * The key ceremony.
 *
 * Deliberately a separate, explicit act rather than something that happens on
 * first boot: the key underwrites every certificate the platform will ever
 * issue, and losing or replacing it invalidates all of them.
 */
class InitCertificationAuthority extends Command
{
    protected $signature = 'certificates:init-authority {--force : Replace an existing key, invalidating every certificate signed with it}';

    protected $description = "Create the Certification Authority's Ed25519 signing key";

    public function handle(): int
    {
        if ($this->option('force') && ! $this->confirm('Replacing the key invalidates every certificate already issued. Continue?', false)) {
            $this->warn('Cancelled. Nothing was changed.');

            return self::FAILURE;
        }

        try {
            $result = CertificationAuthority::generate((bool) $this->option('force'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Pass --force only if you intend to invalidate the existing certificates.');

            return self::FAILURE;
        }

        $this->info('Certification Authority key created.');
        $this->newLine();
        $this->line("  Key id      {$result['kid']}");
        $this->line("  Public key  {$result['public']}");
        $this->line("  Private key {$result['path']}");
        $this->newLine();
        $this->warn('Back up the private key now, and keep it out of the repository and off the web root.');
        $this->line('The public key is served at /.well-known/jwks.json for third-party verification.');

        return self::SUCCESS;
    }
}
