<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Adapter;

use IWD\BluePay\Gateway\Config\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This factory is preferable to use for BluePay adapter instance creation.
 */
class BluePaySubsAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of BluePay Adapter.
     *
     * @param int $storeId if null is provided as an argument, then current scope will be resolved
     * by \Magento\Framework\App\Config\ScopeCodeResolver (useful for most cases) but for adminhtml area the store
     * should be provided as the argument for correct config settings loading.
     * @return BluePaySubsAdapter
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            BluePaySubsAdapter::class,
            [
                'accountId' => $this->config->getAccountId($storeId),
                'secretKey' => $this->config->getValue(Config::KEY_SECRET_KEY, $storeId),
                'mode' => $this->config->getValue(Config::KEY_TRANS_MODE, $storeId),
            ]
        );
    }
}