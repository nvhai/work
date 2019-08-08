<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Plugin\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product\Option\SaveHandler as OptionSaveHandler;

use IWD\BluePaySubs\Setup\UpgradeData;
use IWD\BluePaySubs\Helper\Data as Helper;

class SaveHandler
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $action;

    /**
     * SaveHandler constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper,
        \Magento\Catalog\Model\Product\Action $action
    ) {
        $this->helper = $helper;
        $this->action = $action;
    }

    /**
     * @param OptionSaveHandler $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $result
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(OptionSaveHandler $subject, \Magento\Catalog\Model\Product $result)
    {
        $options = $result->getOptions() ?: [];
        foreach ($options as $option) {
            if ($option->getTitle() == $this->helper->getSubscriptionLabel()) {
                $optionData = [UpgradeData::SUBS_PRODUCT_OPTION_ID => $option->getOptionId()];
                $this->action->updateAttributes([$result->getId()], $optionData, $result->getStoreId());
            }
        }

        return $result;
    }
}