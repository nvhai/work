<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Layout;

use Aheadworks\OneStepCheckout\Model\Layout\SelectiveMerger;
use Aheadworks\OneStepCheckout\Model\Layout\RecursiveMerger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Layout\SelectiveMerger
 */
class SelectiveMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectiveMerger
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
            SelectiveMerger::class,
            ['recursiveMerger' => $this->recursiveMergerMock]
        );
    }

    public function testMergeExisting()
    {
        $code = 'code';
        $config = ['configField' => 'configValue'];
        $sourceConfig = ['sourceConfigField' => 'sourceConfigValue'];
        $mergedConfig = ['mergedConfigField' => 'mergedConfigValue'];

        $this->recursiveMergerMock->expects($this->once())
            ->method('merge')
            ->with($config, $sourceConfig)
            ->willReturn($mergedConfig);

        $this->assertEquals(
            [$code => $mergedConfig],
            $this->merger->merge([$code => $config], [$code => $sourceConfig], [$code])
        );
    }

    public function testMergePortConfig()
    {
        $code = 'code';
        $sourceConfig = ['sourceConfigField' => 'sourceConfigValue'];
        $mergedConfig = $sourceConfig;

        $this->recursiveMergerMock->expects($this->once())
            ->method('merge')
            ->with([], $sourceConfig)
            ->willReturn($mergedConfig);

        $this->assertEquals(
            [$code => $mergedConfig],
            $this->merger->merge([], [$code => $sourceConfig], [$code])
        );
    }

    public function testNoMergeNotExists()
    {
        $code = 'code';
        $config = ['configField' => 'configValue'];
        $this->recursiveMergerMock->expects($this->never())
            ->method('merge');

        $this->assertEquals(
            [$code => $config],
            $this->merger->merge([$code => $config], [], [$code])
        );
    }

    public function testNoMergeNotSpecified()
    {
        $code = 'code';
        $config = ['configField' => 'configValue'];
        $sourceConfig = ['sourceConfigField' => 'sourceConfigValue'];

        $this->recursiveMergerMock->expects($this->never())
            ->method('merge');

        $this->assertEquals(
            [$code => $config],
            $this->merger->merge([$code => $config], [$code => $sourceConfig], [])
        );
    }
}
