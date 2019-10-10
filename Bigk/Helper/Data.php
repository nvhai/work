<?php

namespace Magenest\Bigk\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_storeManager;
    protected $_config = [];
    protected $_filterProvider;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->currencyCode = $currencyFactory->create();
    }

    public function getConfig($path, $storeId = null) {
        if ($storeId == null || $storeId == '') {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $store = $this->_storeManager->getStore($storeId);
        $config = $this->scopeConfig->getValue(
                $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return $config;
    }

    public function filter($str) {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function getSymbol()
    {
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }

}
