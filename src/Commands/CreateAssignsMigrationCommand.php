<?php

namespace DigitSoft\LaravelRbac\Commands;

use DigitSoft\LaravelRbac\Migrations\MigrationCreator;
use DigitSoft\LaravelRbac\Sources\DbSource;
use Illuminate\Console\Command;

/**
 * Class CreateAssignsMigrationCommand
 * @package DigitSoft\LaravelRbac\Commands
 */
class CreateAssignsMigrationCommand extends Command
{
    protected $name = 'rbac:migration-assign';

    protected $description = 'Create migration for item => user assign';
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
        $table = DbSource::TABLE_ASSIGNS;
        $migrationName = 'create_' . $table . '_table';
        $this->getMigrationCreator()->create($migrationName, $this->laravel->databasePath() . '/migrations', $table);
        $this->info("Migration for table ${table} created");
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