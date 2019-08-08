<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Model\Adminhtml\Source;

class TransactionMode
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'TEST',
                'label' => __('Test')
            ],
            [
                'value' => 'LIVE',
                'label' => __('Live')
            ],
        ];
    }
}
