<?php

namespace Meetanshi\CurrencySwitcher\Plugin;

use Magento\Directory\Model\Currency;
use Magento\Store\Model\StoreManagerInterface;

class Store
{
    private $dataWrapper;
    private $currenciesModel;

    public function __construct(Datawrapper $dataWrapper, Currency $currencyModel)
    {
        $this->currenciesModel = $currencyModel;
        $this->dataWrapper = $dataWrapper;
    }

    public function afterGetDefaultCurrencyCode()
    {
        $defaultCurrency = $this->currenciesModel->getConfigDefaultCurrencies();
        if ($this->dataWrapper->isActive()) {
            $geoCountry = $this->dataWrapper->getCountryByIp();
            $geoCurrencyCode = $this->dataWrapper->getCurrencyByCountry($geoCountry);
            $allowedCurrencies = $this->currenciesModel->getConfigAllowCurrencies();
            if (!in_array($geoCurrencyCode, $allowedCurrencies)) {
                return $defaultCurrency[0];
            } else {
                return $geoCurrencyCode;
            }
        } else {
            return $defaultCurrency[0];
        }
    }
}
