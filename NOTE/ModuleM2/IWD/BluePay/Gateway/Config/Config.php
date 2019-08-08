<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'iwd_bluepay';
    const KEY_ACTIVE = 'active';
    const KEY_ACCOUNT_ID = 'account_id';
    const KEY_SECRET_KEY = 'secret_key';
    const KEY_TRANS_MODE = 'trans_mode';
    const FRAUD_PROTECTION = 'fraudprotection';
    const KEY_TITLE = 'title';
    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';
    const KEY_CC_TYPES_MAPPER = 'cc_types_mapper';

    private $scopeConfig;

    private $storeManager;

    private $request;

    private $assetRepo;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        RequestInterface $request,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * @return string
     */
    public function getBluePayIcon()
    {
        $params = ['_secure' => $this->request->isSecure()];
        return $this->assetRepo->createAsset('IWD_BluePay::images/blue_pay.png', $params)->getUrl();
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE) ?
            $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE) : $this->storeManager->getStore()->getName();
    }

    /**
     * Gets merchant ID.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getAccountId($storeId = null)
    {
        return $this->getValue(Config::KEY_ACCOUNT_ID, $storeId);
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }
}