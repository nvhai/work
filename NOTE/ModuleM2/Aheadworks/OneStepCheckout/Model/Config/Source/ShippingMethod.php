<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ShippingMethod
 * @package Aheadworks\OneStepCheckout\Model\Config\Source
 */
class ShippingMethod implements OptionSourceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CarrierFactory $carrierFactory
     * @param RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CarrierFactory $carrierFactory,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->carrierFactory = $carrierFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [['value' => '', 'label' => ' ']];

        foreach ($this->getCarriers() as $carrierCode => $carrier) {
            $carrierMethods = $carrier->getAllowedMethods();
            if ($carrierMethods) {
                $carrierTitle = $this->scopeConfig->getValue(
                    'carriers/' . $carrierCode . '/title',
                    ScopeInterface::SCOPE_STORE
                );
                $result[$carrierCode] = ['label' => $carrierTitle, 'value' => []];
                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    if ($methodTitle) {
                        $result[$carrierCode]['value'][] = [
                            'value' => $carrierCode . '_' . $methodCode,
                            'label' => $carrierTitle . ' - ' . $methodTitle,
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get carriers
     *
     * @return CarrierInterface[]
     */
    private function getCarriers()
    {
        $result = [];

        $scopeType = $this->getScopeType();
        $scopeCode = $this->getScopeCode();
        $carriersConfig = $this->scopeConfig->getValue('carriers', $scopeType, $scopeCode);

        foreach (array_keys($carriersConfig) as $carrierCode) {
            if ($this->scopeConfig->isSetFlag('carriers/' . $carrierCode . '/active', $scopeType, $scopeCode)) {
                $carrierModel = $this->carrierFactory->create($carrierCode);
                if ($carrierModel) {
                    $result[$carrierCode] = $carrierModel;
                }
            }
        }
        return $result;
    }

    /**
     * Get scope type
     *
     * @return string
     */
    private function getScopeType()
    {
        if ($this->request->getParam('website')) {
            return ScopeInterface::SCOPE_WEBSITE;
        } elseif ($this->request->getParam('store')) {
            return ScopeInterface::SCOPE_STORE;
        } else {
            return ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
    }

    /**
     * Get scope code
     *
     * @return string|null
     */
    private function getScopeCode()
    {
        $scopeCode = null;
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($websiteId) {
            $scopeCode = $websiteId;
        } elseif ($storeId) {
            $scopeCode = $storeId;
        }

        return $scopeCode;
    }
}
