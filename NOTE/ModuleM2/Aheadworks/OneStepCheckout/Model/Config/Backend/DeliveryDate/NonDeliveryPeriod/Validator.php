<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\NonDeliveryPeriod;

use Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\NonDeliveryPeriod;
use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\PeriodType;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\OneStepCheckout\Model\Config\Backend\DeliveryDate\NonDeliveryPeriod
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if value meets the validation requirements
     *
     * @param NonDeliveryPeriod $entity
     * @return bool
     */
    public function isValid($entity)
    {
        $this->_clearMessages();

        /** @var array $value */
        $value = $entity->getValue();
        foreach ($value as $periodData) {
            if (isset($periodData['period_type'])) {
                $periodType = $periodData['period_type'];
                if ($periodType == PeriodType::SINGLE_DAY) {
                    if (!\Zend_Validate::is($periodData['period']['from_date'], 'NotEmpty')) {
                        $this->_addMessages(['Date is required.']);
                    }
                } elseif ($periodType == PeriodType::FROM_TO) {
                    if (!\Zend_Validate::is($periodData['period']['from_date'], 'NotEmpty')) {
                        $this->_addMessages(['Start date is required.']);
                    }
                    if (!\Zend_Validate::is($periodData['period']['to_date'], 'NotEmpty')) {
                        $this->_addMessages(['End date is required.']);
                    }
                }
            }
        }

        return empty($this->getMessages());
    }
}
