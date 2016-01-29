<?php

namespace Piktalent\Backup\Command;

use Piktalent\Backup\Cloud\CloudStorage;
use Piktalent\Backup\Manager\CompressManager;
use Piktalent\Backup\Manager\DatabaseManager;
use Piktalent\Backup\Manager\DirectoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class BackupCommand extends Command
{

    private $tmpTime;

    protected function configure()
    {
        $this->setName('run:backup')
            ->addArgument('tasks-file', InputArgument::REQUIRED, 'path of tasks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $receipts = Yaml::parse(file_get_contents($input->getArgument('tasks-file')));
        $io       = new SymfonyStyle($input, $output);

        foreach ($receipts['instances'] as $key => $instance) {

            $fs      = new Filesystem();
            $tmpTime = sys_get_temp_dir() . "/backup_" . time();
            if ($fs->exists($tmpTime)) {
                $fs->remove($tmpTime);
            }
            $fs->mkdir($tmpTime);
            $this->tmpTime = $tmpTime;
            $io->note("Save : " . $key);

            /**
             * Save the database if is defined
             */
            $this->backupDatabase($io, $key, $instance);
            /**
             * Save the directories and compress if is defined
             */
            $this->backupDirectories($io, $key, $instance);

            $fileCompressedPath = $this->compress($io,$key,  $instance);

            if (!array_key_exists('storages', $instance['processor'])) {
                throw new \RuntimeException("You storages is not defined");
            }
            $cloudStorage = new CloudStorage(
                $io,
                $instance['processor']['storages'],
                $instance['cloud_storages'],
                $key,
                $fileCompressedPath
            );
            $cloudStorage->execute();
        }

    }

    private function backupDirectories(OutputInterface $io, $key, $instance)
    {

        if (array_key_exists('directories', $instance)) {
            $directories = $instance['directories'];
            $io->caution(sprintf('Directory Backup %s in directory %s', $key, $directories['base_path']));
            $dManager = new DirectoryManager(
                $directories['base_path'],
                $directories['backup_directories'],
                $this->tmpTime);
            $dManager->execute();
            $io->success(sprintf('Directory Backup %s in directory %s', $key, $directories['base_path']));
        }
    }

    private function compress(OutputInterface $io, $key, $instance)
    {

        $io->caution(sprintf('Compress task %s directory %s', $key, $this->tmpTime));
        $processor          = new CompressManager($instance['processor'], $this->tmpTime);
        $fileCompressedPath = $processor->execute();
        $io->success(sprintf('Compress task %s directory %s', $key, $this->tmpTime));
        return $fileCompressedPath;
    }

    private function backupDatabase(OutputInterface $io, $key, $instance)
    {
        if (array_key_exists('database', $instance)) {
            $io->caution(sprintf('Database Backup %s', $key));
            $database  = $instance['database'];
            $dbManager = new DatabaseManager($database['host'],
                $database['port'],
                $database['name'],
                $database['user'],
                $database['password']
            );
            $dbManager->setTemporaryDirectory($this->tmpTime);
            $dbManager->execute();
            $io->success(sprintf('Database Backup %s', $key));
        }

    }
}