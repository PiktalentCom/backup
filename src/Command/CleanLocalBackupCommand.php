<?php


namespace Piktalent\Backup\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CleanLocalBackupCommand extends Command
{
    protected function configure()
    {
        $this->setName('clean:local:backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io  = new SymfonyStyle($input, $output);
        $finder = new Finder();
        $finder->in(sys_get_temp_dir())->ignoreUnreadableDirs()->sortByType();

        $fs = new Filesystem();

        $files = $finder->name("backup_*");
        if(count($files) == 0){
            $io->success('No olders backups removed');
            return;
        }
        $io->progressStart(count($files));
        foreach ($files as $file) {
            if($fs->exists($file->getRealpath())){
                $fs->remove($file->getRealpath());
            }
            sleep(1);
            $io->progressAdvance();
        }
        $io->progressFinish();
    }

}