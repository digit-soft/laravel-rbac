<?php

return [
    'admin_roles' => ['Admin'],
    //'storage' => 'DigitSoft\LaravelRbac\Storages\PhpFileStorage',
    'storage' => 'DigitSoft\LaravelRbac\Storages\DbStorage',
    'checker' => 'DigitSoft\LaravelRbac\Checkers\Basic',
    'item_file' => 'rbac/items.php',
    'assigns_file' => 'rbac/assigns.php',
    'users_table' => 'users',
    'cache_enable' => false,
    'cache_duration' => 1 //in minutes
];