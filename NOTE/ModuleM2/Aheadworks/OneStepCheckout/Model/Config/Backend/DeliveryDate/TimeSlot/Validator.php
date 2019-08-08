<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\TimeSlot;

use Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\TimeSlot;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\TimeSlot
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if entity value meets the validation requirements
     *
     * @param TimeSlot $entity
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($entity)
    {
        $this->_clearMessages();

        // No validation

        return empty($this->getMessages());
    }
}
