<?php

namespace DigitSoft\LaravelRbac\Commands;

use DigitSoft\LaravelRbac\Contracts\Item;
use DigitSoft\LaravelRbac\Migrations\MigrationCreator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Class CreateItemMigrationsCommand
 * @package DigitSoft\LaravelRbac\Commands
 */
class CreateItemMigrationsCommand extends Command
{
    protected $name = 'rbac:tables';

    protected $description = 'Create migrations for items and assignments in DB';
    /**
     * @var MigrationCreator|null
     */
    protected $migrationCreator;
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * CreateItemMigrationsCommand constructor.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Handle command
     * @throws \Exception
     */
    public function handle()
    {
        $tables = [Item::DB_TABLE_ITEMS, Item::DB_TABLE_CHILDREN, Item::DB_TABLE_ASSIGNS];
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
            $this->migrationCreator = new MigrationCreator($this->files);
        }
        return $this->migrationCreator;
    }
}