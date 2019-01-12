<?php

namespace Meetanshi\CurrencySwitcher\Cron;

use Magento\Framework\Module\Dir\Reader;

class Cron
{
    private $moduleReader;

    public function __construct(Reader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    public function dataDownload()
    {
        $path = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Meetanshi_CurrencySwitcher'
        );
        $zipUrl = "http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz";
        file_put_contents($path . "/Data/GeoIP.zip", fopen($zipUrl, 'r'));
        $archiveFile = $path . "/Data/GeoIP.zip";
        $destinationFile = $path . "/Data/GeoIP.dat";
        $bufferSize = 4096;
        $archiveFile = gzopen($archiveFile, 'rb');
        $data = fopen($destinationFile, 'wb');
        while (!gzeof($archiveFile)) {
            fwrite($data, gzread($archiveFile, $bufferSize));
        }
        fclose($data);
        gzclose($archiveFile);
    }
}
