<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Plugin\Catalog\Model;

use IWD\BluePaySubs\Helper\Data as Helper;
use IWD\BluePaySubs\Setup\InstallData;
use IWD\BluePaySubs\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions;
use IWD\BluePaySubs\Observer\ProductDeleteAfter as SubsDeleteHelper;
use IWD\BluePaySubs\Model\Product\Option\SaveHandler;
use Magento\Framework\Exception\LocalizedException;

/**
 * \Magento\Catalog\Model\Product plugin
 */
class Product
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var SubsDeleteHelper
     */
    private $subsDeleteHelper;

    /**
     * GenerateSubscriptionsObserver constructor.
     *
     * @param Helper $helper
     * @param SaveHandler $saveHandler
     */
    public function __construct(
        Helper $helper,
        SaveHandler $saveHandler,
        SubsDeleteHelper $subsDeleteHelper
    )
    {
        $this->helper = $helper;
        $this->saveHandler = $saveHandler;
        $this->subsDeleteHelper = $subsDeleteHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @throws LocalizedException
     */
    public function beforeBeforeDelete(\Magento\Catalog\Model\Product $product)
    {
        if (!empty($subsIds = $this->subsDeleteHelper->getProductSubscriptionIds($product))) {
            throw new LocalizedException(
                __('Sorry, some subscription still active (IDs %1). Please, stop them before delete product',
                    implode(', ', $subsIds)
                )
            );
        }
    }

    /**
     * Before product save, process subscriptions custom options logic.
     *
     * We're using an around_ plugin here because beforeBeforeSave doesn't seem to work.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @throws LocalizedException
     */
    public function beforeBeforeSave(
        \Magento\Catalog\Model\Product $product
    )
    {
        try {
            $gridOptions = $product->getData(SubscriptionOptions::SUBS_OPTIONS_GRID) ?: [];
            foreach ($gridOptions as $key => $gridOption) {
                if (isset($gridOption["is_delete"]) && $gridOption["is_delete"] == 1) {
                    unset($gridOptions[$key]);
                }
            }
            $product->setData(
                InstallData::SUBS_OPTIONS,
                $this->helper->serialize($gridOptions)
            );
            $this->saveHandler->execute($product);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
