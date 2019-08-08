<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Convert\DataObject as DataObjectConvert;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CustomerGroup
 * @package Aheadworks\OneStepCheckout\Model\Report\Source
 */
class CustomerGroup implements OptionSourceInterface
{
    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var DataObjectConvert
     */
    private $converter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param DataObjectConvert $converter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        DataObjectConvert $converter
    ) {
        $this->groupManagement = $groupManagement;
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $allGroup = $this->groupManagement->getAllCustomersGroup();
            $allGroup->setCode('All Groups');
            $notLoggedInGroup = $this->groupManagement->getNotLoggedInGroup();
            $loggedInGroups = $this->groupManagement->getLoggedInGroups();
            $this->options = $this->converter->toOptionArray(
                array_merge([$allGroup, $notLoggedInGroup], $loggedInGroups),
                'id',
                'code'
            );
        }
        return $this->options;
    }
}
