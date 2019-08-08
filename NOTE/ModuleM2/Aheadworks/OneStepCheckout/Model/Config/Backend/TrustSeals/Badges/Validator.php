<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Backend\TrustSeals\Badges;

use Aheadworks\OneStepCheckout\Model\Config\Backend\TrustSeals\Badges;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\OneStepCheckout\Model\Config\Backend\TrustSeals\Badges
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if and only if value meets the validation requirements
     *
     * @param Badges $entity
     * @return bool
     */
    public function isValid($entity)
    {
        $this->_clearMessages();

        $value = $entity->getValue();
        $itemsCount = 0;
        foreach ($value as $badgeData) {
            if (isset($badgeData['script'])) {
                if (!\Zend_Validate::is($badgeData['script'], 'NotEmpty')) {
                    $this->_addMessages(['Badge script is required.']);
                } else {
                    $itemsCount++;
                }
            }
        }
        if (!\Zend_Validate::is($itemsCount, 'LessThan', ['max' => 4])) {
            $this->_addMessages(['Maximum number of badge items 3 exceeded.']);
        }

        return empty($this->getMessages());
    }
}
