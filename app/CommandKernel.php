<?php


class CommandKernel
{
    public function registerCommands()
    {
        return [
            new \Piktalent\Backup\Command\BackupCommand(),
            new \Piktalent\Backup\Command\CleanLocalBackupCommand()
        ];
    }
}