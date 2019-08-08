<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Product\Option;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use IWD\BluePaySubs\Helper\Data as Helper;
use IWD\BluePaySubs\Setup\InstallData;
use Magento\Framework\Exception\LocalizedException;

class SaveHandler
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $customOptionFactory;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $customOptionValueFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    private $productOptions;

    /**
     * GenerateSubscriptionsObserver constructor.
     *
     * @param Helper $helper
     * @param ProductCustomOptionInterfaceFactory $customOptionFactory
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptions
     * @param ProductCustomOptionValuesInterfaceFactory $customOptionValueFactory
     */
    public function __construct(
        Helper $helper,
        ProductCustomOptionInterfaceFactory $customOptionFactory,
        \Magento\Catalog\Model\Product\OptionFactory $productOptions,
        ProductCustomOptionValuesInterfaceFactory $customOptionValueFactory
    )
    {
        $this->helper = $helper;
        $this->customOptionFactory = $customOptionFactory;
        $this->customOptionValueFactory = $customOptionValueFactory;
        $this->productOptions = $productOptions;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Catalog\Model\Product $product)
    {
        try {
            $customOptions = $product->getOptions() ?: [];
            if (!$this->helper->moduleIsActive() || !$product->getData(InstallData::SUBS_ACTIVE)) {
                $product->setData('product_options', $customOptions);
                return $this;
            }
            $this->removeProductSubscriptionOption($product);
            $product->setCanSaveCustomOptions(true);
            $product->setData('product_options', $customOptions);

            $subsOption = $this->createSubscriptionOption($product);
            if (!empty($subsOption)) {
                $product->setCanSaveCustomOptions(true);
                // Add custom product option
                $customOptions = $product->getOptions() ?: [];
                $customOptions[] = $subsOption;
                $product->setData('product_options', $customOptions);
                // Add product option
                $this->addProductSubscriptionOption($product, $subsOption);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error saving Subscription option: %1', $e->getMessage()));
        }

        return $this;
    }

    /**
     * Save product custom option related to subscription attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function removeProductCustomOptions(\Magento\Catalog\Model\Product $product)
    {
        $customOptions = $product->getProductOptions() ?: [];
        /**
         * Remove any subscription option that might exist
         */
        foreach ($customOptions as $key => $option) {
            if (!empty($option['option_id']) &&
                $option['option_id'] == $this->helper->getSubscriptionOptionId($product)) {
                $customOptions[$key]['is_delete'] = 1;
                $product->getOptionInstance()->addOption($customOptions[$key]);
            }
        }

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @return $this
     * @throws \Exception
     */
    protected function removeProductSubscriptionOption(ProductInterface $product)
    {
        /**
         * Remove the option from the product's options array. This prevents "No such entity." on 2.1.10+.
         */
        /** @var \Magento\Catalog\Model\Product\Option $option */
        $options = $product->getOptions();
        if (!empty($options)) {
            foreach ($options as $k => $option) {
                if ($option->getTitle() == $this->helper->getSubscriptionLabel() ||
                    $option->getId() == $this->helper->getSubscriptionOptionId($product)) {
                    $option->setData('is_delete', 1);
                    $option->delete();
                    unset($options[$k]);
                }
            }
        }
        $product->setOptions($options);

        return $this;
    }

    /**
     * Generate custom option for the given product subscription settings.
     *
     * @param ProductInterface $product
     * @return array
     */
    protected function createSubscriptionOption(ProductInterface $product)
    {
        $subsOptions = $this->helper->unserialize($product->getData(InstallData::SUBS_OPTIONS));
        $subsOptions = empty($subsOptions) ? [] : $subsOptions;

        $optionValues = [];
        if ($product->getData(InstallData::SUBS_ONETIME) == 1) {
            $optionValues[0] = [
                'title' => Helper::SUBS_ONETIME_TITLE,
                'sort_order' => 0,
                'price' => 0,
                'price_type' => 'fixed',
            ];
        }

        foreach ($subsOptions as $option) {
            if ($title = $this->helper->generateOptionTitle($option)) {

                $optionValue = [
                    'title' => $title,
                    'sort_order' => count($optionValues),
                    'price' => 0,
                    'price_type' => 'fixed',
                ];

                $optionValues[] = $optionValue;
            }
        }

        if (!empty($optionValues)) {
            return [
                'title' => $this->helper->getSubscriptionLabel(),
                'type' => 'drop_down',
                'is_require' => 1,
                'is_delete' => 0,
                'sort_order' => 1000,
                'values' => $optionValues,
                'price' => 0,
                'price_type' => 'fixed',
            ];
        }

        return [];
    }

    /**
     * @param ProductInterface $product
     * @param array $subsOption
     * @return $this
     */
    protected function addProductSubscriptionOption(ProductInterface $product, array $subsOption)
    {
        $options = $product->getOptions() ?: [];
        $subValues = [];
        foreach ($subsOption['values'] as $value) {
            $subValue = $this->customOptionValueFactory->create();
            $subValue->addData($value);
            $subValues[] = $subValue;
        }
        $subsOption['values'] = $subValues;

        /** @var \Magento\Catalog\Model\Product\Option $option */
        $option = $this->customOptionFactory->create();
        $option->addData($subsOption);
        $option->setProduct($product);
        $option->setProductSku($product->getSku());

        array_unshift($options, $option);

        $product->setOptions($options);

        return $this;
    }
}