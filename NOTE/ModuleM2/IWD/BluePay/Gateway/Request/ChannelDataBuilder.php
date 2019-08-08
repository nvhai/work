<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class ChannelDataBuilder
 */
class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $channel = 'channel';


    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::$channel => 'Magento-IWD'
        ];
    }
}
