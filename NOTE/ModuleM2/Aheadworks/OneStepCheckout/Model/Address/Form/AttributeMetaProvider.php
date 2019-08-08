<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Mapper;
use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier;
use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\AvailabilityChecker;
use Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Modifier as CustomizationModifier;
use Magento\Customer\Api\AddressMetadataInterface;

/**
 * Class AttributeMetaProvider
 * @package Aheadworks\OneStepCheckout\Model\Address\Form
 */
class AttributeMetaProvider
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var Modifier
     */
    private $modifier;

    /**
     * @var CustomizationModifier
     */
    private $customizationModifier;

    /**
     * @param AddressMetadataInterface $addressMetadata
     * @param AvailabilityChecker $availabilityChecker
     * @param Mapper $mapper
     * @param Modifier $modifier
     * @param CustomizationModifier $customizationModifier
     */
    public function __construct(
        AddressMetadataInterface $addressMetadata,
        AvailabilityChecker $availabilityChecker,
        Mapper $mapper,
        Modifier $modifier,
        CustomizationModifier $customizationModifier
    ) {
        $this->addressMetadata = $addressMetadata;
        $this->availabilityChecker = $availabilityChecker;
        $this->mapper = $mapper;
        $this->modifier = $modifier;
        $this->customizationModifier = $customizationModifier;
    }

    /**
     * Get address attributes metadata
     *
     * @param string $addressType
     * @return array
     */
    public function getMetadata($addressType)
    {
        $result = [];
        $attributes = $this->addressMetadata->getAttributes('customer_register_address');
        foreach ($attributes as $attributeMeta) {
            if ($this->availabilityChecker->isAvailableOnForm($attributeMeta)) {
                $attributeCode = $attributeMeta->getAttributeCode();
                $attributeMeta = $this->customizationModifier->modify($attributeCode, $attributeMeta, $addressType);
                $metadata = $this->mapper->map($attributeMeta);

                if (isset($metadata['label'])) {
                    $metadata['label'] = __($metadata['label']);
                }

                $result[$attributeCode] = $this->modifier->modify($attributeCode, $metadata, $addressType);
            }
        }
        return $result;
    }
}
