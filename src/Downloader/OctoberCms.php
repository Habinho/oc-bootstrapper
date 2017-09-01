<?php

namespace OFFLINE\Bootstrapper\October\Downloader;


use GuzzleHttp\Client;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class OctoberCms
{
    protected $zipFile;

    /**
     * Downloads and extracts October CMS.
     *
     */
    public function __construct()
    {
        $this->zipFile = $this->makeFilename();
    }

    /**
     * Download latest October CMS.
     *
     * @param bool $force
     *
     * @return $this
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    public function download($force = false)
    {
        if($this->alreadyInstalled($force)) {
            throw new \LogicException('-> October is already installed. Use --force to reinstall.');
        }
        
        $this->fetchZip()
             ->extract()
             ->fetchHtaccess()
             ->cleanUp();

        return $this;
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @throws LogicException
     * @throws RuntimeException
     * @return $this
     */
    protected function fetchZip()
    {
        $response = (new Client)->get('https://github.com/octobercms/october/archive/v1.0.419.zip');
        file_put_contents($this->zipFile, $response->getBody());

        return $this;
    }

    /**
     * Extract the zip file into the given directory.
     *
     * @return $this
     */
    protected function extract()
    {
        $archive = new ZipArchive;
        $archive->open($this->zipFile);
        $archive->extractTo(getcwd());
        $archive->close();

        return $this;
    }

    /**
     * Download the latest .htaccess file from GitHub separately
     * since ZipArchive does not support extracting hidden files.
     *
     * @return $this
     */
    protected function fetchHtaccess()
    {
        $contents = file_get_contents('https://raw.githubusercontent.com/octobercms/october/master/.htaccess');
        file_put_contents(getcwd() . DS . '.htaccess', $contents);

        return $this;
    }

    /**
     * Remove the Zip file, move folder contents one level up.
     *
     * @throws LogicException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return $this
     */
    protected function cleanUp()
    {
        @chmod($this->zipFile, 0777);
        @unlink($this->zipFile);

        $directory = getcwd();
        $source    = $directory . DS . 'october-master';

        (new Process(sprintf('mv %s %s', $source . '/*', $directory)))->run();
        (new Process(sprintf('rm -rf %s', $source)))->run();

        if (is_dir($source)) {
            echo "<comment>Install directory could not be removed. Delete ${source} manually</comment>";
        }

        return $this;
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd() . DS . 'october_' . md5(time() . uniqid('oc-', true)) . '.zip';
    }

    /**
     * @param $force
     *
     * @return bool
     */
    protected function alreadyInstalled($force)
    {
        return ! $force && is_dir(getcwd() . DS . 'bootstrap') && is_dir(getcwd() . DS . 'modules');
    }

}
