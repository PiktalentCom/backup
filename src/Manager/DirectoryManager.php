<?php

namespace Piktalent\Backup\Manager;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DirectoryManager
{
    private $temporaryDirectory;
    private $output,
            $directoriesBackup,
            $basePath,
            $outputPath, $filesystem;

    public function __construct($basePath, array $directoriesBackup = [], $temporaryDirectory)
    {
        $this->basePath          = $basePath;
        $this->directoriesBackup = $directoriesBackup;
        $this->outputPath        = $temporaryDirectory . "/directories/";
        $this->filesystem        = new Filesystem();
    }


    public function setTemporaryDirectory($directory)
    {
        $this->temporaryDirectory = $directory;
    }


    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $this->copyFolders();
    }

    public function copyFolders()
    {
        // Copy folder for compression file
        foreach ($this->directoriesBackup as $directory) {
            $originDirectory = $this->basePath . '/' . $directory;
            $mirror          = $this->outputPath . $directory . '/';
            $finder          = new Finder();
            /** @var SplFileInfo $file */
            foreach ($finder->in($originDirectory)->files() as $file) {
                if (false !== @fopen($file->getRealPath(), 'r')) {
                    $this->filesystem->copy($file->getRealPath(), $mirror . $file->getRelativePathname());
                }
            }
        }
    }

}