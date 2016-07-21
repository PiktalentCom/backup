<?php


namespace Piktalent\Backup\Cloud;


use Symfony\Component\Console\Output\OutputInterface;

class CloudStorage
{

    private $io;
    private $storages;
    private $cloudStorages;
    private $taskName;
    private $pathToFiles;

    public function __construct(OutputInterface $io, $storages, $cloudStorages, $taskName, array $pathToFiles = [])
    {
        $this->storages      = $storages;
        $this->io            = $io;
        $this->cloudStorages = $cloudStorages;
        $this->taskName      = $taskName;
        $this->pathToFiles   = $pathToFiles;
    }


    public function execute()
    {
        foreach ($this->storages as $storage) {
            if (!method_exists($this, $storage)) {
                throw new \RuntimeException(sprintf('This %s doesn\'t exists'));
            }
            $this->{$storage}();
        }
    }


    private function Dropbox()
    {
        if (array_key_exists('dropbox_sdk', $this->cloudStorages)) {
            $dropboxSDK = $this->cloudStorages['dropbox_sdk'];
            $this->io->caution(sprintf('Upload %s to Dropbox ', $this->taskName));
            if (!array_key_exists('access_token', $dropboxSDK) || !array_key_exists('remote_path', $dropboxSDK)) {
                throw  new \RuntimeException('You Dropbox settings is not defined');
            }
            $dropbox = new \Piktalent\Backup\Cloud\DropboxClient(
                [
                    'dropbox_sdk' => [
                        'access_token' => $dropboxSDK['access_token'],
                        'remote_path'  => $dropboxSDK['remote_path']
                    ]
                ]
            );

            foreach ($this->pathToFiles as $pathToFile) {
                $dropbox->upload($pathToFile);
            }

            $this->io->success(sprintf('Upload %s to Dropbox ', $this->taskName));
        }
    }
}