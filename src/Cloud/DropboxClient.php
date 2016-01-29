<?php
namespace Piktalent\Backup\Cloud;

use Dropbox as Dropbox;
use Piktalent\Backup\Exception\UploadException;


/**
 * Class DropboxClient.
 * @author  Jonathan Dizdarevic <dizda@dizda.fr>
 */
class DropboxClient implements ClientInterface
{
    /**
     * @var string
     */
    private $access_token;

    /**
     * @param array $params user
     */
    public function __construct($params)
    {
        $params             = $params['dropbox_sdk'];
        $this->access_token = $params['access_token'];
        $this->remotePath   = $params['remote_path'];
        Dropbox\RootCertificates::useExternalPaths();
    }

    /**
     * {@inheritdoc}
     */
    public function upload($archive)
    {
        $fileName  = explode('/', $archive);
        $pathError = Dropbox\Path::findErrorNonRoot($this->remotePath);

        if ($pathError !== null) {
            throw new UploadException(sprintf('Invalid path "%s".', $archive));
        }

        $client = new Dropbox\Client($this->access_token, 'CloudBackupBundle');

        $size = filesize($archive);

        $fp = fopen($archive, 'rb');
        $client->uploadFile($this->remotePath . '/' . end($fileName), Dropbox\WriteMode::add(), $fp, $size);
        fclose($fp);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'DropboxSdk';
    }
}
