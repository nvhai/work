<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Layout;

use Aheadworks\OneStepCheckout\Model\Layout\CrossMerger;
use Aheadworks\OneStepCheckout\Model\Layout\RecursiveMerger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Layout\CrossMerger
 */
class CrossMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CrossMerger
     */
    private $merger;

    /**
     * @var RecursiveMerger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $recursiveMergerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->recursiveMergerMock = $this->createMock(RecursiveMerger::class);
        $this->merger = $objectManager->getObject(
            CrossMerger::class,
            ['recursiveMerger' => $this->recursiveMergerMock]
        );
    }

    public function testMergeWithSource()
    {
        $code = 'code';
        $customConfig = ['customConfigField' => 'customConfigValue'];
        $sourceConfig = ['sourceConfigField' => 'sourceConfigValue'];
        $mergedConfig = ['mergedConfigField' => 'mergedConfigValue'];

        $this->recursiveMergerMock->expects($this->once())
            ->method('merge')
            ->with($sourceConfig, $customConfig)
            ->willReturn($mergedConfig);

        $this->assertEquals(
            [$code => $mergedConfig],
            $this->merger->merge([$code => $customConfig], [$code => $sourceConfig])
        );
    }

    public function testNoMerge()
    {
        $this->recursiveMergerMock->expects($this->never())
            ->method('merge');
        $this->assertEquals(
            [],
            $this->merger->merge(['code' => ['customConfigField' => 'customConfigValue']], [])
        );
    }

    public function testMergePortConfig()
    {
        $code = 'code';
        $customConfig = [];
        $sourceConfig = ['sourceConfigField' => 'sourceConfigValue'];
        $mergedConfig = $sourceConfig;

        $this->recursiveMergerMock->expects($this->once())
            ->method('merge')
            ->with($sourceConfig, $customConfig)
            ->willReturn($mergedConfig);

        $this->assertEquals(
            [$code => $mergedConfig],
            $this->merger->merge([$code => $customConfig], [$code => $sourceConfig])
        );
    }

    public function testMergeNoPortConfig()
    {
        $this->recursiveMergerMock->expects($this->never())
            ->method('merge');
        $this->assertEquals([], $this->merger->merge(['code' => []], []));
    }
}
