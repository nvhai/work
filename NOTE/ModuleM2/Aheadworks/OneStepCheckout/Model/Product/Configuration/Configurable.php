<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Product\Configuration;

use Aheadworks\OneStepCheckout\Model\Product\ConfigurationInterface;
use Aheadworks\OneStepCheckout\Model\Serialize\Factory as SerializeFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;

/**
 * Class Configurable
 * @package Aheadworks\OneStepCheckout\Model\Product\Configuration
 */
class Configurable implements ConfigurationInterface
{
    /**
     * @var SerializeFactory
     */
    private $serializerFactory;

    /**
     * @param SerializeFactory $serializerFactory
     */
    public function __construct(SerializeFactory $serializerFactory)
    {
        $this->serializerFactory = $serializerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(ItemInterface $item)
    {
        $product = $item->getProduct();
        $configurableOptions = $this->getConfigurableProductOptions($product);
        $attributes =  $item->getProduct()->getCustomOption('attributes');

        $serializer = $this->serializerFactory->create();
        return [
            'attributes' => $this->getAttributesData($product, $configurableOptions),
            'defaultValues' => $serializer->unserialize($attributes->getValue())
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(ItemInterface $item, $optionsData = [])
    {
        if ($item->getProductOption()) {
            $extensionAttributes = $item->getProductOption()->getExtensionAttributes();
            if ($extensionAttributes) {
                /** @var ConfigurableItemOptionValueInterface $options */
                $options = $extensionAttributes->getConfigurableItemOptions();
                $product = $item->getProduct();
                $optionsDataKeyedById = $this->getKeyedByIdOptionsData(
                    $product,
                    $this->getConfigurableProductOptions($product),
                    $optionsData
                );
                if (is_array($options)) {
                    foreach ($options as $option) {
                        $optionId = $option->getOptionId();
                        if (isset($optionsDataKeyedById[$optionId])) {
                            $option->setOptionValue($optionsDataKeyedById[$optionId]);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get configurable product options
     *
     * @param Product $product
     * @return array
     */
    private function getConfigurableProductOptions($product)
    {
        $result = [];
        /** @var TypeConfigurable $productType */
        $productType = $product->getTypeInstance();
        $allowedProducts = $productType->getSalableUsedProducts($product, null);
        $allowedAttributes = $productType->getConfigurableAttributes($product);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowedAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $result[$productAttributeId][$attributeValue][] = $productId;
                $result['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $result;
    }

    /**
     * Get attributes data
     *
     * @param Product $product
     * @param array $configurableOptions
     * @return array
     */
    private function getAttributesData($product, $configurableOptions)
    {
        $attributesData = [];
        /** @var TypeConfigurable $productType */
        $productType = $product->getTypeInstance();
        $allowedAttributes = $productType->getConfigurableAttributes($product);

        foreach ($allowedAttributes as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $configurableOptions);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $attributesData[$attributeId] = [
                    'id' => $attributeId,
                    'code' => $productAttribute->getAttributeCode(),
                    'label' => $productAttribute->getStoreLabel($product->getStoreId()),
                    'options' => $attributeOptionsData,
                    'position' => $attribute->getPosition(),
                ];
            }
        }
        return $attributesData;
    }

    /**
     * Get attribute options data
     *
     * @param ConfigurableAttribute $attribute
     * @param array $configurableOptions
     * @return array
     */
    private function getAttributeOptionsData($attribute, $configurableOptions)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $attributeOptionsData[] = [
                'value' => $optionId,
                'label' => $attributeOption['label'],
                'products' => isset($configurableOptions[$attribute->getAttributeId()][$optionId])
                    ? $configurableOptions[$attribute->getAttributeId()][$optionId]
                    : [],
            ];
        }
        return $attributeOptionsData;
    }

    /**
     * Get options data keyed by id
     *
     * @param Product $product
     * @param array $configurableOptions
     * @param array $optionsData
     * @return array
     */
    private function getKeyedByIdOptionsData($product, $configurableOptions, $optionsData)
    {
        $keyedOptionsData = [];
        /** @var TypeConfigurable $productType */
        $productType = $product->getTypeInstance();
        $allowedAttributes = $productType->getConfigurableAttributes($product);

        foreach ($allowedAttributes as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $configurableOptions);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeCode = $productAttribute->getAttributeCode();
                if (isset($optionsData[$attributeCode])) {
                    $keyedOptionsData[$productAttribute->getId()] = $optionsData[$attributeCode];
                }
            }
        }
        return $keyedOptionsData;
    }
}
