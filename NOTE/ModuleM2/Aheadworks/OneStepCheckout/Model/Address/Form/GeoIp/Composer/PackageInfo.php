<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer;

use Composer\Package\PackageInterface;

/**
 * Class PackageInfo
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer
 */
class PackageInfo
{
    /**
     * @var Factory
     */
    private $composerFactory;

    /**
     * @var array
     */
    private $isInstalledCache = [];

    /**
     * @param Factory $composerFactory
     */
    public function __construct(Factory $composerFactory)
    {
        $this->composerFactory = $composerFactory;
    }

    /**
     * Check if package installed
     *
     * @param string $packageName
     * @return bool
     */
    public function isInstalled($packageName)
    {
        // todo: save in cache, not in class property
        if (!isset($this->isInstalledCache[$packageName])) {
            $package = $this->findPackageByName($packageName);
            $this->isInstalledCache[$packageName] = $package !== null;
        }
        return $this->isInstalledCache[$packageName];
    }

    /**
     * Get installed package version
     *
     * @param string $packageName
     * @return null|string
     */
    public function getInstalledVersion($packageName)
    {
        $package = $this->findPackageByName($packageName);
        return $package ? $package->getVersion() : null;
    }

    /**
     * Find package by name
     *
     * @param string $packageName
     * @return PackageInterface|null
     */
    private function findPackageByName($packageName)
    {
        $composer = $this->composerFactory->create();
        foreach ($composer->getLocker()->getLockedRepository()->getPackages() as $package) {
            if ($packageName == $package->getName()) {
                return $package;
            }
        }
        return null;
    }
}
