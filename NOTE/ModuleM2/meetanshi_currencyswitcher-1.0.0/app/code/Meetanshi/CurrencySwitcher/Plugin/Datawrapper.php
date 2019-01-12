<?php

namespace Meetanshi\CurrencySwitcher\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\Dir\Reader;

class Datawrapper
{
    const XMLPATH_GEOIPCURRENCY_ENABLE = 'currencyswitcher/general/enable';

    private $flags;
    private $filehandle;
    private $memoryBuffer;
    private $databaseType;
    private $databaseSegments;
    private $recordLength;
    private $shmid;
    private $moduleReader;

    protected $scopeConfig;
    protected $remoteAddress;

    public function __construct(ScopeConfigInterface $scopeConfig, RemoteAddress $remoteAddress, Reader $moduleReader)
    {
        $this->scopeConfig = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
        $this->moduleReader = $moduleReader;
    }

    private $geoipCountryCodes = [
        "", "AP", "EU", "AD", "AE", "AF", "AG", "AI", "AL", "AM", "CW",
        "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AZ", "BA", "BB",
        "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BM", "BN", "BO",
        "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD",
        "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR",
        "CU", "CV", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO",
        "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ",
        "FK", "FM", "FO", "FR", "SX", "GA", "GB", "GD", "GE", "GF",
        "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT",
        "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID",
        "IE", "IL", "IN", "IO", "IQ", "IR", "IS", "IT", "JM", "JO",
        "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW",
        "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT",
        "LU", "LV", "LY", "MA", "MC", "MD", "MG", "MH", "MK", "ML",
        "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV",
        "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI",
        "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF",
        "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW",
        "PY", "QA", "RE", "RO", "RU", "RW", "SA", "SB", "SC", "SD",
        "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO",
        "SR", "ST", "SV", "SY", "SZ", "TC", "TD", "TF", "TG", "TH",
        "TJ", "TK", "TM", "TN", "TO", "TL", "TR", "TT", "TV", "TW",
        "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE",
        "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "RS", "ZA",
        "ZM", "ME", "ZW", "A1", "A2", "O1", "AX", "GG", "IM", "JE",
        "BL", "MF", "BQ"];

    private $geoipCountryBegin = 16776960;
    private $geoipStateBeginRev0 = 16700000;
    private $geoipStateBeginRev1 = 16000000;
    private $geoipMemoryCache = 1;
    private $geoipSharedMemory = 2;
    private $structureInfoMaxSize = 20;
    private $geoipCountryEdition = 106;
    private $geoipProxyEdition = 8;
    private $geoipAsnumEdition = 9;
    private $geoipNetspeedEdition = 10;
    private $geoipRegionEditionRev0 = 112;
    private $geoipRegionEditionRev1 = 3;
    private $geoipCityEditionRev0 = 111;
    private $geoipCityEditionRev1 = 2;
    private $geoipOrgEdition = 110;
    private $geoipIspEdition = 4;
    private $segmentRecordLength = 3;
    private $standardRecordLength = 3;
    private $orgRecordLength = 4;
    private $geoipShmKey = 0x4f415401;
    private $geoipDomainEdition = 11;
    private $geoipCountryEditionV6 = 12;
    private $geoipLocationaEdition = 13;
    private $geoipAccuracyradiusEdition = 14;
    private $geoipCityEditionRev1V6 = 30;
    private $geoipCityEditionRev0V6 = 31;
    private $geoipNetspeedEditionRev1 = 32;
    private $geoipNetspeedEditionRev1V6 = 33;
    private $GEOIP_USERTYPE_EDITION = 28;
    private $geoipUsertypeEditionV6 = 29;
    private $geoipAsnumEditionV6 = 21;
    private $geoipIspEditionV6 = 22;
    private $geoipOrgEditionV6 = 23;
    private $geoipDomainEditionV6 = 24;

    public function isActive()
    {
        return (bool)$this->scopeConfig->getValue(self::XMLPATH_GEOIPCURRENCY_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCountryByIp()
    {
        $ipAddress = $this->remoteAddress->getRemoteAddress();
        if ($this->isActive()) {
            $path = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                'Meetanshi_CurrencySwitcher'
            );
            if ($this->geoipOpen($path . '/Data/GeoIP.dat', 0)) {
                $country = $this->geoipCountryCodeByAddr($ipAddress);
                $this->geoipClose();
                return $country;
            } else {
                return null;
            }
        }
    }

    public function geoipOpen($filename, $flags)
    {
        $this->flags = $flags;
        if ($this->flags & $this->geoipSharedMemory) {
            $this->shmid = @shmop_open($this->geoipShmKey, "a", 0, 0);
        } else {
            if (file_exists($filename) && $this->filehandle = fopen($filename, "rb")) {
                if ($this->flags & $this->geoipMemoryCache) {
                    $s_array = fstat($this->filehandle);
                    $this->memoryBuffer = fread($this->filehandle, $s_array['size']);
                }
            } else {
                return false;
            }
        }

        $this->setupSegments();
        return true;
    }

    public function geoipClose()
    {
        if ($this->flags & $this->geoipSharedMemory) {
            return true;
        }

        return fclose($this->filehandle);
    }

    public function geoipCountryCodeByAddr($addr)
    {
        $country_id = $this->geoipCountryIdByAddr($addr);
        return $country_id !== false ? $this->geoipCountryCodes[$country_id] : false;
    }

    public function geoipCountryIdByAddr($addr)
    {
        $ipnum = ip2long($addr);
        return $this->geoipSeekCountry($ipnum) - $this->geoipCountryBegin;
    }

    private function geoipSeekCountry($ipnum)
    {
        $offset = 0;
        for ($depth = 31; $depth >= 0; --$depth) {
            if ($this->flags & $this->geoipMemoryCache) {
                $enc = mb_internal_encoding();
                mb_internal_encoding('ISO-8859-1');

                $buf = substr(
                    $this->memoryBuffer,
                    2 * $this->recordLength * $offset,
                    2 * $this->recordLength
                );

                mb_internal_encoding($enc);
            } elseif ($this->flags & $this->geoipSharedMemory) {
                $buf = @shmop_read(
                    $this->shmid,
                    2 * $this->recordLength * $offset,
                    2 * $this->recordLength
                );
            } else {
                fseek($this->filehandle, 2 * $this->recordLength * $offset, SEEK_SET) == 0;
                $buf = fread($this->filehandle, 2 * $this->recordLength);
            }

            $x = [0, 0];
            for ($i = 0; $i < 2; ++$i) {
                for ($j = 0; $j < $this->recordLength; ++$j) {
                    $x[$i] += ord($buf[$this->recordLength * $i + $j]) << ($j * 8);
                }
            }

            if ($ipnum & (1 << $depth)) {
                if ($x[1] >= $this->databaseSegments) {
                    return $x[1];
                }

                $offset = $x[1];
            } else {
                if ($x[0] >= $this->databaseSegments) {
                    return $x[0];
                }

                $offset = $x[0];
            }
        }

        trigger_error("error traversing database - perhaps it is corrupt?", E_USER_ERROR);
        return false;
    }

    private function setupSegments()
    {
        $this->databaseType = $this->geoipCountryEdition;
        $this->recordLength = $this->standardRecordLength;
        if ($this->flags & $this->geoipSharedMemory) {
            $offset = @shmop_size($this->shmid) - 3;
            for ($i = 0; $i < $this->structureInfoMaxSize; $i++) {
                $delim = @shmop_read($this->shmid, $offset, 3);
                $offset += 3;
                if ($delim == (chr(255) . chr(255) . chr(255))) {
                    $this->databaseType = ord(@shmop_read($this->shmid, $offset, 1));
                    $offset++;

                    if ($this->databaseType == $this->geoipRegionEditionRev0) {
                        $this->databaseSegments = $this->geoipStateBeginRev0;
                    } elseif ($this->databaseType == $this->geoipRegionEditionRev1) {
                        $this->databaseSegments = $this->geoipStateBeginRev1;
                    } elseif (($this->databaseType == $this->geoipCityEditionRev0) ||
                        ($this->databaseType == $this->geoipCityEditionRev1)
                        || ($this->databaseType == $this->geoipOrgEdition)
                        || ($this->databaseType == $this->geoipOrgEditionV6)
                        || ($this->databaseType == $this->geoipDomainEdition)
                        || ($this->databaseType == $this->geoipDomainEditionV6)
                        || ($this->databaseType == $this->geoipIspEdition)
                        || ($this->databaseType == $this->geoipIspEditionV6)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION)
                        || ($this->databaseType == $this->geoipUsertypeEditionV6)
                        || ($this->databaseType == $this->geoipLocationaEdition)
                        || ($this->databaseType == $this->geoipAccuracyradiusEdition)
                        || ($this->databaseType == $this->geoipCityEditionRev0V6)
                        || ($this->databaseType == $this->geoipCityEditionRev1V6)
                        || ($this->databaseType == $this->geoipNetspeedEditionRev1)
                        || ($this->databaseType == $this->geoipNetspeedEditionRev1V6)
                        || ($this->databaseType == $this->geoipAsnumEdition)
                        || ($this->databaseType == $this->geoipAsnumEditionV6)) {
                        $this->databaseSegments = 0;
                        $buf = @shmop_read($this->shmid, $offset, $this->segmentRecordLength);
                        for ($j = 0; $j < $this->segmentRecordLength; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }

                        if (($this->databaseType == $this->geoipOrgEdition)
                            || ($this->databaseType == $this->geoipOrgEditionV6)
                            || ($this->databaseType == $this->geoipDomainEdition)
                            || ($this->databaseType == $this->geoipDomainEditionV6)
                            || ($this->databaseType == $this->geoipIspEdition)
                            || ($this->databaseType == $this->geoipIspEditionV6)) {
                            $this->recordLength = $this->orgRecordLength;
                        }
                    }

                    break;
                } else {
                    $offset -= 4;
                }
            }

            if (($this->databaseType == $this->geoipCountryEdition) ||
                ($this->databaseType == $this->geoipCountryEditionV6) ||
                ($this->databaseType == $this->geoipProxyEdition) ||
                ($this->databaseType == $this->geoipNetspeedEdition)) {
                $this->databaseSegments = $this->geoipCountryBegin;
            }
        } else {
            $filepos = ftell($this->filehandle);
            fseek($this->filehandle, -3, SEEK_END);
            for ($i = 0; $i < $this->structureInfoMaxSize; $i++) {
                $delim = fread($this->filehandle, 3);
                if ($delim == (chr(255) . chr(255) . chr(255))) {
                    $this->databaseType = ord(fread($this->filehandle, 1));
                    if ($this->databaseType == $this->geoipRegionEditionRev0) {
                        $this->databaseSegments = $this->geoipStateBeginRev0;
                    } elseif ($this->databaseType == $this->geoipRegionEditionRev1) {
                        $this->databaseSegments = $this->geoipStateBeginRev1;
                    } elseif (($this->databaseType == $this->geoipCityEditionRev0)
                        || ($this->databaseType == $this->geoipCityEditionRev1)
                        || ($this->databaseType == $this->geoipCityEditionRev0V6)
                        || ($this->databaseType == $this->geoipCityEditionRev1V6)
                        || ($this->databaseType == $this->geoipOrgEdition)
                        || ($this->databaseType == $this->geoipDomainEdition)
                        || ($this->databaseType == $this->geoipIspEdition)
                        || ($this->databaseType == $this->geoipOrgEditionV6)
                        || ($this->databaseType == $this->geoipDomainEditionV6)
                        || ($this->databaseType == $this->geoipIspEditionV6)
                        || ($this->databaseType == $this->geoipLocationaEdition)
                        || ($this->databaseType == $this->geoipAccuracyradiusEdition)
                        || ($this->databaseType == $this->geoipCityEditionRev0V6)
                        || ($this->databaseType == $this->geoipCityEditionRev1V6)
                        || ($this->databaseType == $this->geoipNetspeedEditionRev1)
                        || ($this->databaseType == $this->geoipNetspeedEditionRev1V6)
                        || ($this->databaseType == $this->GEOIP_USERTYPE_EDITION)
                        || ($this->databaseType == $this->geoipUsertypeEditionV6)
                        || ($this->databaseType == $this->geoipAsnumEdition)
                        || ($this->databaseType == $this->geoipAsnumEditionV6)) {
                        $this->databaseSegments = 0;
                        $buf = fread($this->filehandle, $this->segmentRecordLength);
                        for ($j = 0; $j < $this->segmentRecordLength; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }

                        if (($this->databaseType == $this->geoipOrgEdition)
                            || ($this->databaseType == $this->geoipDomainEdition)
                            || ($this->databaseType == $this->geoipIspEdition)
                            || ($this->databaseType == $this->geoipOrgEditionV6)
                            || ($this->databaseType == $this->geoipDomainEditionV6)
                            || ($this->databaseType == $this->geoipIspEditionV6)) {
                            $this->recordLength = $this->orgRecordLength;
                        }
                    }

                    break;
                } else {
                    fseek($this->filehandle, -4, SEEK_CUR);
                }
            }

            if (($this->databaseType == $this->geoipCountryEdition) ||
                ($this->databaseType == $this->geoipCountryEditionV6) ||
                ($this->databaseType == $this->geoipProxyEdition) ||
                ($this->databaseType == $this->geoipNetspeedEdition)) {
                $this->databaseSegments = $this->geoipCountryBegin;
            }

            fseek($this->filehandle, $filepos, SEEK_SET);
        }
    }

    public function getCurrencyByCountry($countryCode)
    {
        $mapCountry = ['' => '',
            "EU" => "EUR", "AD" => "EUR", "AE" => "AED", "AF" => "AFN", "AG" => "XCD", "AI" => "XCD",
            "AL" => "ALL", "AM" => "AMD", "CW" => "ANG", "AO" => "AOA", "AQ" => "AQD", "AR" => "ARS", "AS" => "EUR",
            "AT" => "EUR", "AU" => "AUD", "AW" => "AWG", "AZ" => "AZN", "BA" => "BAM", "BB" => "BBD",
            "BD" => "BDT", "BE" => "EUR", "BF" => "XOF", "BG" => "BGL", "BH" => "BHD", "BI" => "BIF",
            "BJ" => "XOF", "BM" => "BMD", "BN" => "BND", "BO" => "BOB", "BR" => "BRL", "BS" => "BSD",
            "BT" => "BTN", "BV" => "NOK", "BW" => "BWP", "BY" => "BYR", "BZ" => "BZD", "CA" => "CAD",
            "CC" => "AUD", "CD" => "CDF", "CF" => "XAF", "CG" => "XAF", "CH" => "CHF", "CI" => "XOF",
            "CK" => "NZD", "CL" => "CLP", "CM" => "XAF", "CN" => "CNY", "CO" => "COP", "CR" => "CRC",
            "CU" => "CUP", "CV" => "CVE", "CX" => "AUD", "CY" => "EUR", "CZ" => "CZK", "DE" => "EUR",
            "DJ" => "DJF", "DK" => "DKK", "DM" => "XCD", "DO" => "DOP", "DZ" => "DZD", "EC" => "ECS",
            "EE" => "EEK", "EG" => "EGP", "EH" => "MAD", "ER" => "ETB", "ES" => "EUR", "ET" => "ETB",
            "FI" => "EUR", "FJ" => "FJD", "FK" => "FKP", "FM" => "USD", "FO" => "DKK", "FR" => "EUR", "SX" => "ANG",
            "GA" => "XAF", "GB" => "GBP", "GD" => "XCD", "GE" => "GEL", "GF" => "EUR", "GH" => "GHS",
            "GI" => "GIP", "GL" => "DKK", "GM" => "GMD", "GN" => "GNF", "GP" => "EUR", "GQ" => "XAF",
            "GR" => "EUR", "GS" => "GBP", "GT" => "GTQ", "GU" => "USD", "GW" => "XOF", "GY" => "GYD",
            "HK" => "HKD", "HM" => "AUD", "HN" => "HNL", "HR" => "HRK", "HT" => "HTG", "HU" => "HUF",
            "ID" => "IDR", "IE" => "EUR", "IL" => "ILS", "IN" => "INR", "IO" => "USD", "IQ" => "IQD",
            "IR" => "IRR", "IS" => "ISK", "IT" => "EUR", "JM" => "JMD", "JO" => "JOD", "JP" => "JPY",
            "KE" => "KES", "KG" => "KGS", "KH" => "KHR", "KI" => "AUD", "KM" => "KMF", "KN" => "XCD",
            "KP" => "KPW", "KR" => "KRW", "KW" => "KWD", "KY" => "KYD", "KZ" => "KZT", "LA" => "LAK",
            "LB" => "LBP", "LC" => "XCD", "LI" => "CHF", "LK" => "LKR", "LR" => "LRD", "LS" => "LSL",
            "LT" => "LTL", "LU" => "EUR", "LV" => "LVL", "LY" => "LYD", "MA" => "MAD", "MC" => "EUR",
            "MD" => "MDL", "MG" => "MGF", "MH" => "USD", "MK" => "MKD", "ML" => "XOF", "MM" => "MMK",
            "MN" => "MNT", "MO" => "MOP", "MP" => "USD", "MQ" => "EUR", "MR" => "MRO", "MS" => "XCD",
            "MT" => "EUR", "MU" => "MUR", "MV" => "MVR", "MW" => "MWK", "MX" => "MXN", "MY" => "MYR",
            "MZ" => "MZN", "NA" => "NAD", "NC" => "XPF", "NE" => "XOF", "NF" => "AUD", "NG" => "NGN",
            "NI" => "NIO", "NL" => "EUR", "NO" => "NOK", "NP" => "NPR", "NR" => "AUD", "NU" => "NZD",
            "NZ" => "NZD", "OM" => "OMR", "PA" => "PAB", "PE" => "PEN", "PF" => "XPF", "PG" => "PGK",
            "PH" => "PHP", "PK" => "PKR", "PL" => "PLN", "PM" => "EUR", "PN" => "NZD", "PR" => "USD", "PS" => "ILS", "PT" => "EUR",
            "PW" => "USD", "PY" => "PYG", "QA" => "QAR", "RE" => "EUR", "RO" => "RON", "RU" => "RUB",
            "RW" => "RWF", "SA" => "SAR", "SB" => "SBD", "SC" => "SCR", "SD" => "SDD", "SE" => "SEK",
            "SG" => "SGD", "SH" => "SHP", "SI" => "EUR", "SJ" => "NOK", "SK" => "SKK", "SL" => "SLL",
            "SM" => "EUR", "SN" => "XOF", "SO" => "SOS", "SR" => "SRG", "ST" => "STD", "SV" => "SVC",
            "SY" => "SYP", "SZ" => "SZL", "TC" => "USD", "TD" => "XAF", "TF" => "EUR", "TG" => "XOF",
            "TH" => "THB", "TJ" => "TJS", "TK" => "NZD", "TM" => "TMM", "TN" => "TND", "TO" => "TOP", "TL" => "USD",
            "TR" => "TRY", "TT" => "TTD", "TV" => "AUD", "TW" => "TWD", "TZ" => "TZS", "UA" => "UAH",
            "UG" => "UGX", "UM" => "USD", "US" => "USD", "UY" => "UYU", "UZ" => "UZS", "VA" => "EUR",
            "VC" => "XCD", "VE" => "VEF", "VG" => "USD", "VI" => "USD", "VN" => "VND", "VU" => "VUV",
            "WF" => "XPF", "WS" => "EUR", "YE" => "YER", "YT" => "EUR", "RS" => "RSD",
            "ZA" => "ZAR", "ZM" => "ZMK", "ME" => "EUR", "ZW" => "ZWD",
            "AX" => "EUR", "GG" => "GBP", "IM" => "GBP",
            "JE" => "GBP", "BL" => "EUR", "MF" => "EUR", "BQ" => "USD", "SS" => "SSP"
        ];

        return $mapCountry[$countryCode];
    }
}
