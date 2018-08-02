<?php

namespace DigitSoft\LaravelRbac\Commands;

use DigitSoft\LaravelRbac\Migrations\MigrationCreator;
use DigitSoft\LaravelRbac\Sources\DbSource;
use Illuminate\Console\Command;

/**
 * Class CreateItemMigrationsCommand
 * @package DigitSoft\LaravelRbac\Commands
 */
class CreateItemMigrationsCommand extends Command
{
    protected $name = 'rbac:migration-item';

    protected $description = 'Create migrations for items in DB';
    /**
     * @var MigrationCreator|null
     */
    protected $migrationCreator;

    /**
     * Handle command
     * @throws \Exception
     */
    public function handle()
    {
        $tables = [DbSource::TABLE_ITEMS, DbSource::TABLE_CHILDREN];
        foreach ($tables as $num => $table) {
            if ($num) {
                // keep order of migrations
                sleep(1);
            }
            $migrationName = 'create_' . $table . '_table';
            $this->getMigrationCreator()->create($migrationName, $this->laravel->databasePath() . '/migrations', $table);
            $this->info("Migration for table ${table} created");
        }
    }

    /**
     * Get migration creator instance
     * @return MigrationCreator
     */
    protected function getMigrationCreator()
    {
        if ($this->migrationCreator === null) {
            $this->migrationCreator = new MigrationCreator($this->laravel->make('files'));
        }
        return $this->migrationCreator;
    }
}