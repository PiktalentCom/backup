<?php

namespace Piktalent\Backup\Processor;

class TarProcessor extends BaseProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return '.tar';
    }

    /**
     * {@inheritdoc}
     */
    public function getCompressionCommand($archivePath, $basePath)
    {
        $tarParams = [];
        $zipParams = [];

        if (isset($this->options['compression_ratio']) && $this->options['compression_ratio'] >= 0) {
            $compression_ratio = max(min($this->options['compression_ratio'], 9), 0);
            $zipParams[] = '-' . $compression_ratio;
        }

        return sprintf('tar %s c -C %s . | gzip %s > %s',
            implode(' ', $tarParams),
            $basePath,
            implode(' ', $zipParams),
            $archivePath
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Tar';
    }
}
