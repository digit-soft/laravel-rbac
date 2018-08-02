<?php

namespace DigitSoft\LaravelRbac\Migrations;

/**
 * Class MigrationCreator with own stubs
 * @package DigitSoft\LaravelRbac\Migrations
 */
class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /**
     * @inheritdoc
     */
    protected function getStub($table, $create)
    {
        $stubPath = $this->stubPath() . '/' . $table . '.stub';
        if ($this->files->exists($stubPath)) {
            return $this->files->get($stubPath);
        }
        return parent::getStub($table, $create);
    }

    /**
     * @inheritdoc
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs';
    }
}