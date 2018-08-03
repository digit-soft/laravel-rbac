<?php

namespace DigitSoft\LaravelRbac\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CreateItemsFileCommand
 * @package DigitSoft\LaravelRbac\Commands
 */
class CreateItemFilesCommand extends Command
{
    protected $name = 'rbac:files';

    protected $description = 'Create php files with items and assignments. (For PhpFileStorage)';
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * CreateItemsFileCommand constructor.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force files overwrite');
    }

    /**
     * Handle command
     */
    public function handle()
    {
        $this->createItemsFile();
        $this->createAssignsFile();
    }

    /**
     * Create file for items
     */
    protected function createItemsFile()
    {
        $path = $this->getItemsFilePath();
        if (!$this->files->exists($path) || $this->option('force')) {
            $this->files->put($path, $this->getItemsFileContent());
            $this->info("File ${path} written (for items)");
        } else {
            $this->error("File ${path} already exists, use option --force to overwrite it");
        }
    }

    /**
     * Create file for assignments
     */
    protected function createAssignsFile()
    {
        $path = $this->getAssignsFilePath();
        if (!$this->files->exists($path) || $this->option('force')) {
            $this->files->put($path, $this->getAssignsFileContent());
            $this->info("File ${path} written (for user assignments)");
        } else {
            $this->error("File ${path} already exists, use option --force to overwrite it");
        }
    }

    /**
     * Get items example file content
     * @return string
     */
    protected function getItemsFileContent()
    {
        $content =<<<EOF
<?php

return [
    // administrator role (read config file at `admin_roles`)
    'Admin' => [
        'type' => \DigitSoft\LaravelRbac\Contracts\Item::TYPE_ROLE,
        'title' => 'Administrator',
    ],
    // manager role
    'Manager' => [
        'type' => \DigitSoft\LaravelRbac\Contracts\Item::TYPE_ROLE,
        'title' => 'Manager',
        'children' => [
            'articles.create',
            'articles.update',
        ],
    ],
    // permissions
    'articles.create' => [
        'title' => 'Create articles',
        // you can omit `type` option for permissions
        'type' => \DigitSoft\LaravelRbac\Contracts\Item::TYPE_PERMISSION,
    ],
    'articles.update' => [
        'title' => 'Update articles',
        'children' => [
            'articles.delete',
        ],
    ],
    'articles.delete' => [
        'title' => 'Delete articles',
    ],
];

EOF;
        return $content;
    }

    /**
     * Get assignments example file content
     * @return string
     */
    protected function getAssignsFileContent()
    {
        $content =<<<EOF
<?php

return [
    // Admin user
    1 => [
        'Admin',
    ],
    // Manager user
    2 => [
        'Manager',
        'articles.check',
        'articles.update',
    ],
    // simple mapping for user
    3 => [
        'articles.create',
    ],
];

EOF;
        return $content;
    }

    /**
     * Get items file path
     * @return string
     */
    protected function getItemsFilePath()
    {
        $filePath = app()->resourcePath(config('rbac.item_file', 'rbac/items.php'));
        $this->checkFilePath($filePath);
        return $filePath;
    }

    /**
     * Get assigns file path
     * @return string
     */
    protected function getAssignsFilePath()
    {
        $filePath = app()->resourcePath(config('rbac.assigns_file', 'rbac/assigns.php'));
        $this->checkFilePath($filePath);
        return $filePath;
    }

    /**
     * Check that directory exists for file
     * @param string $filePath
     * @throws \Exception
     */
    private function checkFilePath($filePath)
    {
        $dirPath = $this->files->dirname($filePath);
        if (!$this->files->exists($dirPath)) {
            throw new \Exception("Directory ${dirPath} does not exist");
        }
        if (!$this->files->isDirectory($dirPath)) {
            throw new \Exception("${dirPath} is not a directory");
        }
    }
}