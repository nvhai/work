<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';

    const KEY_TITLE = 'title';

    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';

    private $scopeConfig;

    private $storeManager;

    private $request;

    private $assetRepo;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        RequestInterface $request,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    )
    {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
    }

    public function isActive()
    {
        return (bool)$this->getValue(self::KEY_ACTIVE);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function getBluePayIcon()
    {
        $params = ['_secure' => $this->request->isSecure()];
        return $this->assetRepo->createAsset('IWD_BluePay::images/blue_pay.png', $params)->getUrl();
    }

    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE) ?
            $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE) : $this->storeManager->getStore()->getName();
    }
}
