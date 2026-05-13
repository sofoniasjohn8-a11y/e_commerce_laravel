<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('migrate:with-fallback {--connection=} {--fallback=pgsql}', function () {
    $connection = $this->option('connection') ?: env('DB_CONNECTION', 'mysql');
    $fallback = $this->option('fallback');
    $attempts = [$connection];

    if ($fallback && $fallback !== $connection) {
        $attempts[] = $fallback;
    }

    foreach ($attempts as $attempt) {
        $this->info("Running migrations using connection: {$attempt}");

        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--database' => $attempt,
            ]);
        } catch (\Throwable $e) {
            $this->error("Migration failed on {$attempt}: {$e->getMessage()}");
            $exitCode = 1;
        }

        if ($exitCode === 0) {
            $this->info("Migrations succeeded on {$attempt}.");
            return 0;
        }

        $this->warn("Connection {$attempt} failed. " . ($attempt === end($attempts) ? 'No fallback left.' : 'Trying fallback...'));
    }

    $this->error('Migration failed on all configured connections.');
    return 1;
})->purpose('Run migrations on the default DB connection and fallback to PostgreSQL if it fails.');
