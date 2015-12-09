<?php


namespace Piktalent\Backup\Manager;


use Symfony\Component\Process\Process;

class CompressManager
{

    private $settings,
        $processor,
        $directory, $password;

    function __construct($settings, $tempDirectory)
    {
        $this->settings = $settings;
        $this->directory = $tempDirectory;
        $processNamespace = "\\Piktalent\\Backup\\Processor\\" . ucfirst($settings['compress']) . 'Processor';
        $this->processor = new $processNamespace;
    }

    public function execute()
    {
        $process = new Process($this->compress());
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    public function compress()
    {
        $archivePath = $this->buildArchiveFilename();
        $this->processor->addOptions(['compression_ratio' => $this->settings['ratio']] + $this->getPassword());
        $archive = $this->processor->getCompressionCommand($archivePath, $this->directory);
        return $archive;
    }

    /**
     * Return the archive file name.
     *
     * @return string
     */
    public function buildArchiveFilename()
    {
        return $this->directory . $this->processor->getExtension();
    }

    public function getPassword()
    {
        if ($this->password != null) {
            return ['password' => $this->password];
        }
        return [];
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

}