<?php

namespace Piktalent\Backup\Manager;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DatabaseManager
{

    private $host,
        $port,
        $dbname,
        $user,
        $password,
        $temporaryDirectory,
        $output,
        $authPrefix,
        $auth, $timeout;

    /**
     * @param $host
     * @param $port
     * @param $dbname
     * @param $user
     * @param $password
     */
    public function __construct($host, $port, $dbname, $user, $password)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->dbname   = $dbname;
        $this->user     = $user;
        $this->password = $password;
        $this->timeout  = 300;

        $this->authPrefix = sprintf('export PGPASSWORD="%s" && ', $password);
        $this->auth       = sprintf('--username "%s" ', $user);
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

        $process = new Process($this->getCommand(), null, null, null, $this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

    }

    protected function getCommand()
    {
        //TODO: pg_dumpall support
        return sprintf('%spg_dump %s "%s" > "%s"',
            $this->authPrefix,
            $this->auth,
            $this->dbname,
            $this->temporaryDirectory . "/" . time() . ".sql");
    }

}