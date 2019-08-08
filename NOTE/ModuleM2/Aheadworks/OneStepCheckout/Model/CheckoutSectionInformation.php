<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionInformationInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class CheckoutSectionInformation
 * @package Aheadworks\OneStepCheckout\Model
 */
class CheckoutSectionInformation extends AbstractSimpleObject implements CheckoutSectionInformationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }
}
