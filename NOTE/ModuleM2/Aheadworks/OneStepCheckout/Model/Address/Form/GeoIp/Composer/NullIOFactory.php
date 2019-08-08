<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer;

use Composer\IO\NullIO;

/**
 * Class NullIOFactory
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer
 */
class NullIOFactory
{
    /**
     * Creates NullIO instance
     *
     * @return NullIO
     */
    public function create()
    {
        return new NullIO();
    }
}
