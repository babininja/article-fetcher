<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:self-interface {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make an Interface Class';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $repoPath = $this->getRepositoryFilePath();
        $interfacePath = $this->getInterfaceFilePath();

        $this->makeDirectory(dirname($repoPath));
        $this->makeDirectory(dirname($interfacePath));

        $repositoryFile = $this->getRepositoryFile();
        $interfaceFile = $this->getInterfaceFile();

        if (!$this->files->exists($repoPath)) {
            $this->files->put($repoPath, $repositoryFile);
            $this->info("File : {$repoPath} created");
        } else {
            $this->info("File : {$repoPath} already exits");
        }


        if (!$this->files->exists($interfacePath)) {
            $this->files->put($interfacePath, $interfaceFile);
            $this->info("File : {$interfacePath} created");
        } else {
            $this->info("File : {$interfacePath} already exits");
        }

    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getRepositoryPath()
    {
        return __DIR__ . '/../../../stubs/repository.stub';
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getInterfacePath()
    {
        return __DIR__ . '/../../../stubs/repository.interface.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubRepositoryVariables()
    {
        return [
            'NAMESPACE' => 'App\\Repositories\\Models',
            'CLASS_NAME' => $this->getSingularClassName($this->argument('name')),
        ];
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubInterfaceVariables()
    {
        return [
            'NAMESPACE' => 'App\\Repositories\\Contracts',
            'CLASS_NAME' => $this->getSingularClassName($this->argument('name')),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getRepositoryFile()
    {
        return $this->getStubContents($this->getRepositoryPath(), $this->getStubRepositoryVariables());
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getInterfaceFile()
    {
        return $this->getStubContents($this->getInterfacePath(), $this->getStubInterfaceVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getRepositoryFilePath()
    {
        return base_path('app\\Repositories\\Models') . '\\'
            . $this->getSingularClassName($this->argument('name'))
            . '\\' . $this->getSingularClassName($this->argument('name')) . 'Repository.php';
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getInterfaceFilePath()
    {
        return base_path('app\\Repositories\\Contracts') . '\\' .
            $this->getSingularClassName($this->argument('name'))
            . '\\' . $this->getSingularClassName($this->argument('name')) . 'RepositoryInterface.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

}
