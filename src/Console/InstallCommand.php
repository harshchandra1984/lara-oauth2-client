<?php

namespace Larawizards\LaraOAuth2Client\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lara-oauth2-client:install
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Lara OAuth2 Client package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Lara OAuth2 Client...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'lara-oauth2-client-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'lara-oauth2-client-migrations',
            '--force' => $this->option('force'),
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'lara-oauth2-client-views',
            '--force' => $this->option('force'),
        ]);

        $this->info('Lara OAuth2 Client installed successfully!');
        $this->info('Please configure your OAuth2 credentials in .env file.');
        $this->info('Run migrations: php artisan migrate');

        return Command::SUCCESS;
    }
}
