<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp;

use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Exception\CouldNotDetectGeoDataException;

/**
 * Class AdapterInterface
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp
 */
interface AdapterInterface
{
    /**
     * Get country code by IP address
     *
     * @param string $ip
     * @return string|null
     * @throws CouldNotDetectGeoDataException
     */
    public function getCountryCode($ip);

    /**
     * Check if service is available
     *
     * @return bool
     */
    public function isAvailable();
}
