<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer;

use Magento\Framework\Composer\MagentoComposerApplicationFactory;

/**
 * Class PackageInstaller
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer
 */
class PackageInstaller
{
    /**
     * @var MagentoComposerApplicationFactory
     */
    private $composerAppFactory;

    /**
     * @param MagentoComposerApplicationFactory $composerAppFactory
     */
    public function __construct(MagentoComposerApplicationFactory $composerAppFactory)
    {
        $this->composerAppFactory = $composerAppFactory;
    }

    /**
     * Install package
     *
     * @param string $packageName
     * @param string $versionString
     * @return bool
     */
    public function install($packageName, $versionString)
    {
        $needRestoreXdebugWarnFlag = false;
        if (extension_loaded('xdebug') && !getenv('COMPOSER_DISABLE_XDEBUG_WARN')) {
            putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');
            $needRestoreXdebugWarnFlag = true;
        }

        $composerApplication = $this->composerAppFactory->create();
        $result = $composerApplication->runComposerCommand(
            [
                'command' => 'require',
                'packages' => [$packageName . ':' . $versionString]
            ]
        );

        if ($needRestoreXdebugWarnFlag) {
            putenv('COMPOSER_DISABLE_XDEBUG_WARN=0');
        }

        return $result;
    }
}
