<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Address\Form;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMetaProvider;
use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Mapper;
use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier;
use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\AvailabilityChecker;
use Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Modifier as CustomizationModifier;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMetaProvider
 */
class AttributeMetaProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeMetaProvider
     */
    private $metaProvider;

    /**
     * @var AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataMock;

    /**
     * @var AvailabilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $availabilityCheckerMock;

    /**
     * @var Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapperMock;

    /**
     * @var Modifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifierMock;

    /**
     * @var CustomizationModifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customizationModifierMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->addressMetadataMock = $this->getMockForAbstractClass(AddressMetadataInterface::class);
        $this->availabilityCheckerMock = $this->getMockBuilder(AvailabilityChecker::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailableOnForm'])
            ->getMock();
        $this->mapperMock = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['map'])
            ->getMock();
        $this->modifierMock = $this->getMockBuilder(Modifier::class)
            ->disableOriginalConstructor()
            ->setMethods(['modify'])
            ->getMock();
        $this->customizationModifierMock = $this->getMockBuilder(CustomizationModifier::class)
            ->disableOriginalConstructor()
            ->setMethods(['modify'])
            ->getMock();
        $this->metaProvider = $objectManager->getObject(
            AttributeMetaProvider::class,
            [
                'addressMetadata' => $this->addressMetadataMock,
                'availabilityChecker' => $this->availabilityCheckerMock,
                'mapper' => $this->mapperMock,
                'modifier' => $this->modifierMock,
                'customizationModifier' => $this->customizationModifierMock
            ]
        );
    }

    public function testGetMetadata()
    {
        $addressType = 'shipping';
        $attributeCode = 'firtstname';
        $metadata = ['label' => 'First Name', 'visible' => '1'];
        $modifiedMetadata = ['label' => 'First Name', 'visible' => '0'];

        $attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->addressMetadataMock->expects($this->once())
            ->method('getAttributes')
            ->with('customer_register_address')
            ->willReturn([$attributeMetadataMock]);
        $this->availabilityCheckerMock->expects($this->once())
            ->method('isAvailableOnForm')
            ->with($attributeMetadataMock)
            ->willReturn(true);
        $attributeMetadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->customizationModifierMock->expects($this->once())
            ->method('modify')
            ->with($attributeCode, $attributeMetadataMock, $addressType)
            ->willReturn($attributeMetadataMock);
        $this->mapperMock->expects($this->once())
            ->method('map')
            ->with($attributeMetadataMock)
            ->willReturn($metadata);
        $this->modifierMock->expects($this->once())
            ->method('modify')
            ->with($attributeCode, $metadata, $addressType)
            ->willReturn($modifiedMetadata);

        $this->assertEquals([$attributeCode => $modifiedMetadata], $this->metaProvider->getMetadata($addressType));
    }

    public function testGetMetadataNotAvailableOnForm()
    {
        $addressType = 'shipping';

        $attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->addressMetadataMock->expects($this->once())
            ->method('getAttributes')
            ->with('customer_register_address')
            ->willReturn([$attributeMetadataMock]);
        $this->availabilityCheckerMock->expects($this->once())
            ->method('isAvailableOnForm')
            ->with($attributeMetadataMock)
            ->willReturn(false);

        $this->assertEquals([], $this->metaProvider->getMetadata($addressType));
    }
}
