<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\AdapterInterface;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country as CountrySource;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;

/**
 * Class CountryId
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute
 */
class CountryId implements ModifierInterface
{
    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CountrySource
     */
    private $countrySource;

    /**
     * @var AdapterInterface
     */
    private $geoIpAdapter;

    /**
     * @var RequestInterface|Http
     */
    private $request;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param Config $config
     * @param CountrySource $countrySource
     * @param AdapterInterface $geoIpAdapter
     * @param RequestInterface $request
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        Config $config,
        CountrySource $countrySource,
        AdapterInterface $geoIpAdapter,
        RequestInterface $request
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->config = $config;
        $this->countrySource = $countrySource;
        $this->geoIpAdapter = $geoIpAdapter;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata, $addressType)
    {
        $metadata['default'] = $this->getDefaultCountryId();
        $metadata['options'] = $this->countrySource->getAllOptions();
        $metadata['options'] = $this->orderCountryOptions($metadata['options']);
        return $metadata;
    }

    /**
     * Get default country Id
     *
     * @return string
     */
    private function getDefaultCountryId()
    {
        $defaultCountryId = $this->directoryHelper->getDefaultCountry();
        if ($this->config->getDefaultCountryId()) {
            $defaultCountryId = $this->config->getDefaultCountryId();
        } else if ($this->config->isGeoIpDetectionEnabled() && $this->geoIpAdapter->isAvailable()) {
            $countryIdByIp = $this->getCountryIdByIp();
            if ($countryIdByIp) {
                $defaultCountryId = $countryIdByIp;
            }
        }
        return $defaultCountryId;
    }

    /**
     * Get country Id by client ip address
     *
     * @return null|string
     */
    private function getCountryIdByIp()
    {
        try {
            $countryId = $this->geoIpAdapter->getCountryCode($this->request->getClientIp());
        } catch (\Exception $e) {
            $countryId = null;
        }
        return $countryId;
    }

    /**
     * Reorder country options. Move top countries to the beginning of the list
     *
     * @param array $options
     * @return array
     */
    private function orderCountryOptions($options)
    {
        $topCountryCodes = $this->directoryHelper->getTopCountryCodes();
        if (!empty($topCountryCodes)) {
            $headOptions = [];
            $tailOptions = [[
                'value' => 'delimiter',
                'label' => '──────────',
                'disabled' => true,
            ]];

            foreach ($options as $option) {
                if (empty($option['value']) || in_array($option['value'], $topCountryCodes)) {
                    array_push($headOptions, $option);
                } else {
                    array_push($tailOptions, $option);
                }
            }
            return array_merge($headOptions, $tailOptions);
        }
        return $options;
    }
}
