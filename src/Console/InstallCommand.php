<?php

namespace KaziSTM\AlgeriaGeo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use KaziSTM\AlgeriaGeo\Database\Seeders\AlgeriaGeoDatabaseSeeder;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'algeria-geo:install
                            {--seed : Seed the database with Algerian geo data}
                            {--force : Force run migrations and seeders even if tables exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Algeria Geo package: run migrations and optionally seed data, checks if already installed.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Checking Algeria Geo Package installation status...');

        $force = $this->option('force');
        $shouldSeed = $this->option('seed');

        $tablesExist = Schema::hasTable('cities') || Schema::hasTable('communes');

        if ($tablesExist && !$force && !$shouldSeed) {
            $this->warn('Algeria Geo tables (cities/communes) already exist.');
            $this->line('Skipping migrations and seeding.');
            $this->line('Use the --force option to run them anyway (use with caution!).');
            $this->line('If you want to reset, consider using `php artisan migrate:fresh --seed`.');
            return 0;
        }

        if ($tablesExist && $force) {
            $this->warn('Using --force option. Running migrations and potentially seeders even though tables exist...');
        }

        $migrationPath = is_dir(base_path('vendor/kazistm/algeria-geo/src/Database/Migrations'))
            ? base_path('vendor/kazistm/algeria-geo/src/Database/Migrations')
            : realpath(__DIR__ . '/../Database/Migrations');

        $this->line('Running migrations...');

        $migrateExitCode = Artisan::call('migrate', [
            '--path' => $migrationPath,
            '--realpath' => true,
            '--force' => true,
        ]);


        if ($migrateExitCode === 0) {
            $output = Artisan::output();
            if (str_contains($output, 'Nothing to migrate')) {
                $this->info('Migrations already up to date.');
            } else {
                $this->info('Migrations ran successfully.');
            }
        } else {
            $this->error('Migration failed.');
            $this->line(Artisan::output());
            return 1;
        }

        if ($shouldSeed || $force) {
            if (!$shouldSeed && $force) {
                $this->warn('Forcing seeding because --force was used.');
            }
            $this->line('Seeding database...');
            $seedExitCode = Artisan::call('db:seed', [
                '--class' => AlgeriaGeoDatabaseSeeder::class,
                '--force' => true
            ]);

            if ($seedExitCode === 0) {
                $this->info('Database seeded successfully.');
            } else {
                $this->error('Database seeding failed.');
                $this->line(Artisan::output());
                return 1;
            }
        } elseif (!$shouldSeed && !$force) {
            if (!$tablesExist) {
                $this->comment('Skipping database seeding. Use the --seed option to run seeders.');
            }
        }

        $this->info('Algeria Geo Package setup finished!');
        return 0;
    }
}