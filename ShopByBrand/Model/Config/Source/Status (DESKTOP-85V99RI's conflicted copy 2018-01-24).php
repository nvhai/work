<?php
/**
 * Test for \Magento\Paypal\Block\Payment\Form\Billing\Agreement
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payment\Form\Billing;

class AgreementTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Paypal\Block\Payment\Form\Billing\Agreement */
    protected $_block;

    protected function setUp()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\ResourceModel\Quote\Collection::class
        )->getFirstItem();
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects