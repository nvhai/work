<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\CountryId;
use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\AdapterInterface;
use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Exception\CouldNotDetectGeoDataException;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\CountryId
 */
class CountryIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CountryId
     */
    private $modifier;

    /**
     * @var DirectoryHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryHelperMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $geoIpAdapterMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->directoryHelperMock = $this->createMock(DirectoryHelper::class);
        $this->configMock = $this->createMock(Config::class);
        $this->geoIpAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->modifier = $objectManager->getObject(
            CountryId::class,
            [
                'directoryHelper' => $this->directoryHelperMock,
                'config' => $this->configMock,
                'geoIpAdapter' => $this->geoIpAdapterMock,
                'request' => $this->requestMock
            ]
        );
    }

    public function testGetDefaultCountryIdSystemConfigValue()
    {
        $countryId = 'US';

        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($countryId);
        $this->configMock->expects($this->once())
            ->method('getDefaultCountryId')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isGeoIpDetectionEnabled')
            ->willReturn(false);

        $class = new \ReflectionClass($this->modifier);
        $method = $class->getMethod('getDefaultCountryId');
        $method->setAccessible(true);

        $this->assertEquals($countryId, $method->invoke($this->modifier));
    }

    public function testGetDefaultCountryIdModuleConfigValue()
    {
        $countryIdSysConfigValue = 'US';
        $countryIdModuleConfigValue = 'UK';

        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($countryIdSysConfigValue);
        $this->configMock->expects($this->exactly(2))
            ->method('getDefaultCountryId')
            ->willReturn($countryIdModuleConfigValue);

        $class = new \ReflectionClass($this->modifier);
        $method = $class->getMethod('getDefaultCountryId');
        $method->setAccessible(true);

        $this->assertEquals($countryIdModuleConfigValue, $method->invoke($this->modifier));
    }

    public function testGetDefaultCountryIdGeoIpNotAvailable()
    {
        $countryId = 'US';

        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($countryId);
        $this->configMock->expects($this->once())
            ->method('getDefaultCountryId')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isGeoIpDetectionEnabled')
            ->willReturn(true);
        $this->geoIpAdapterMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $class = new \ReflectionClass($this->modifier);
        $method = $class->getMethod('getDefaultCountryId');
        $method->setAccessible(true);

        $this->assertEquals($countryId, $method->invoke($this->modifier));
    }

    public function testGetDefaultCountryIdGeoIpAvailable()
    {
        $countryIdSysConfigValue = 'US';
        $countryIdDetectedByIp = 'UK';
        $ip = '127.0.0.1';

        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($countryIdSysConfigValue);
        $this->configMock->expects($this->once())
            ->method('getDefaultCountryId')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isGeoIpDetectionEnabled')
            ->willReturn(true);
        $this->geoIpAdapterMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getClientIp')
            ->willReturn($ip);
        $this->geoIpAdapterMock->expects($this->once())
            ->method('getCountryCode')
            ->with($ip)
            ->willReturn($countryIdDetectedByIp);

        $class = new \ReflectionClass($this->modifier);
        $method = $class->getMethod('getDefaultCountryId');
        $method->setAccessible(true);

        $this->assertEquals($countryIdDetectedByIp, $method->invoke($this->modifier));
    }

    public function testGetDefaultCountryIdGeoIpException()
    {
        $countryId = 'US';
        $ip = '127.0.0.1';

        $exception = new CouldNotDetectGeoDataException(__('Unexpected data is found in database.'));

        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($countryId);
        $this->configMock->expects($this->once())
            ->method('getDefaultCountryId')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isGeoIpDetectionEnabled')
            ->willReturn(true);
        $this->geoIpAdapterMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getClientIp')
            ->willReturn($ip);
        $this->geoIpAdapterMock->expects($this->once())
            ->method('getCountryCode')
            ->with($ip)
            ->willThrowException($exception);

        $class = new \ReflectionClass($this->modifier);
        $method = $class->getMethod('getDefaultCountryId');
        $method->setAccessible(true);

        $this->assertEquals($countryId, $method->invoke($this->modifier));
    }
}
