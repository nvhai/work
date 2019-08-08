<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Model\Adminhtml\Source;

class PaymentType
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'CCACH',
                'label' => __('Credit Card & eCheck Enabled')
            ],
            [
                'value' => 'CC',
                'label' => __('Credit Card Only')
            ],
            [
                'value' => 'ACH',
                'label' => __('eCheck Only')
            ],
        ];
    }
}
