<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\Customization;

use Aheadworks\OneStepCheckout\Model\Config as ModuleConfig;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Modifier
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\Customization
 */
class Modifier
{
    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param ModuleConfig $config
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ModuleConfig $config,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->config = $config;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Modify attribute metadata
     *
     * @param string $attributeCode
     * @param AttributeMetadataInterface $metadata
     * @param string $addressType
     * @return AttributeMetadataInterface
     */
    public function modify($attributeCode, $metadata, $addressType)
    {
        $formConfig = $this->config->getAddressFormConfig($addressType);
        if (isset($formConfig['attributes'][$attributeCode])
            && $metadata->getFrontendInput() != 'multiline'
        ) {
            $metadataUpdate = $formConfig['attributes'][$attributeCode];
            $this->dataObjectHelper->populateWithArray(
                $metadata,
                [
                    AttributeMetadataInterface::STORE_LABEL => $metadataUpdate['label'],
                    AttributeMetadataInterface::VISIBLE => $metadataUpdate['visible'],
                    AttributeMetadataInterface::REQUIRED => $metadataUpdate['required']
                ],
                AttributeMetadataInterface::class
            );
        }
        return $metadata;
    }
}
