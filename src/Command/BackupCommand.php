<?php

namespace Piktalent\Backup\Command;

use Piktalent\Backup\Manager\CompressManager;
use Piktalent\Backup\Manager\DatabaseManager;
use Piktalent\Backup\Manager\DirectoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class BackupCommand extends Command
{

    protected function configure()
    {
        $this->setName('run:backup')
            ->addArgument('tasks-file', InputArgument::REQUIRED, 'path of tasks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $receipts = Yaml::parse(file_get_contents($input->getArgument('tasks-file')));
        $fs = new Filesystem();
        foreach ($receipts['instances'] as $key => $instance) {

            $tmpTime = sys_get_temp_dir() . "/backup_" . time();
            if ($fs->exists($tmpTime)) {
                $fs->remove($tmpTime);
            }
            $fs->mkdir($tmpTime);

            $output->writeln("Save : " . $key);


            if (array_key_exists('database', $instance)) {
                $output->writeln('Database Backup');
                $database = $instance['database'];
                $dbManager = new DatabaseManager($database['host'],
                    $database['port'],
                    $database['name'],
                    $database['user'],
                    $database['password']
                );
                $dbManager->setTemporaryDirectory($tmpTime);
                $dbManager->execute();

            }


            if (array_key_exists('directories', $instance)) {
                $output->writeln('Directory Backup');
                $directories = $instance['directories'];

                $dManager = new DirectoryManager(
                    $directories['base_path'],
                    $directories['backup_directories'],
                    $tmpTime);
                $dManager->execute();
            }

            $output->writeln('Compress');
            $processor = new CompressManager($receipts['settings'], $tmpTime);
            if (array_key_exists('password', $receipts['settings'])) {
                $password = $receipts['settings']['password'];
                $processor->setPassword($password);
            }

            $processor->execute();

            if (array_key_exists('dropbox_sdk', $receipts)) {
                $output->writeln('Upload to Dropbox');
                $dropbox = new \Piktalent\Backup\Cloud\DropboxClient(
                    [
                        'dropbox_sdk' => [
                            'access_token' => $receipts['dropbox_sdk']['access_token'],
                            'remote_path' => "/" . $key
                        ]
                    ]
                );
                $dropbox->upload($tmpTime . "." . $receipts['settings']['compress']);
            }
        }


    }


}