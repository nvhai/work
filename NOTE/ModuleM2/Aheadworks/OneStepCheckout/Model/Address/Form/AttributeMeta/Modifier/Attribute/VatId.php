<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Customer\Helper\Address as AddressHelper;

/**
 * Class VatId
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute
 */
class VatId implements ModifierInterface
{
    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param AddressHelper $addressHelper
     * @param Config $config
     */
    public function __construct(
        AddressHelper $addressHelper,
        Config $config
    ) {
        $this->addressHelper = $addressHelper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata, $addressType)
    {
        $formConfig = $this->config->getAddressFormConfig($addressType);
        if (!isset($formConfig['attributes']['vat_id'])) {
            $metadata['visible'] = $this->addressHelper->isVatAttributeVisible();
        }
        return $metadata;
    }
}
