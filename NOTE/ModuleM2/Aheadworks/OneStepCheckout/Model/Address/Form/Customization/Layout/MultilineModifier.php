<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Layout;

use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class MultilineModifier
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Layout
 */
class MultilineModifier
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param Config $config
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        Config $config,
        ArrayManager $arrayManager
    ) {
        $this->config = $config;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Modify multiline address field config
     *
     * @param array $rowLayout
     * @param string $addressType
     * @return array
     */
    public function modify($rowLayout, $addressType)
    {
        // todo: revise, should be more abstract
        $streetLinesPath = 'address-field-row/children/street/children';

        $formConfig = $this->config->getAddressFormConfig($addressType);
        if (isset($formConfig['attributes']['street'])) {
            $streetLinesLayout = $this->arrayManager->get($streetLinesPath, $rowLayout);
            if ($streetLinesLayout) {
                $streetLinesLayoutUpdate = [];
                foreach ($streetLinesLayout as $line => $inputConfig) {
                    if (isset($formConfig['attributes']['street'][$line])) {
                        $streetConfigUpdate = $formConfig['attributes']['street'][$line];
                        if ((bool)$streetConfigUpdate['visible']) {
                            $inputConfig = array_merge($inputConfig, $streetConfigUpdate);
                            if ((bool)$streetConfigUpdate['required']) {
                                $inputConfig['validation']['required-entry'] = true;
                            } elseif (isset($inputConfig['validation']['required-entry'])) {
                                unset($inputConfig['validation']['required-entry']);
                            }
                            $streetLinesLayoutUpdate[$line] = $inputConfig;
                        }
                    } else {
                        $streetLinesLayoutUpdate[$line] = $inputConfig;
                    }
                }
                $rowLayout = $this->arrayManager->set($streetLinesPath, $rowLayout, $streetLinesLayoutUpdate);
            }
        }

        return $rowLayout;
    }
}
