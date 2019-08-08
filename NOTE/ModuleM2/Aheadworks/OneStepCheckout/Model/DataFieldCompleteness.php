<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\Data\DataFieldCompletenessInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class DataFieldCompleteness
 * @package Aheadworks\OneStepCheckout\Model
 */
class DataFieldCompleteness extends AbstractSimpleObject implements DataFieldCompletenessInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->_get(self::FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldName($fieldName)
    {
        return $this->setData(self::FIELD_NAME, $fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsCompleted()
    {
        return $this->_get(self::IS_COMPLETED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCompleted($isCompleted)
    {
        return $this->setData(self::IS_COMPLETED, $isCompleted);
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->_get(self::SCOPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        return $this->setData(self::SCOPE, $scope);
    }
}
