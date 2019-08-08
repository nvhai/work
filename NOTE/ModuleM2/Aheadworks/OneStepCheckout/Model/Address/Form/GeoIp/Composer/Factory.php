<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer;

use Composer\Composer;
use Composer\Factory as ComposerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;

/**
 * Class Factory
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer
 */
class Factory
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ComposerJsonFinder
     */
    private $composerJsonFinder;

    /**
     * @var NullIOFactory
     */
    private $nullIoFactory;

    /**
     * @param DirectoryList $directoryList
     * @param ComposerJsonFinder $composerJsonFinder
     * @param NullIOFactory $nullIoFactory
     */
    public function __construct(
        DirectoryList $directoryList,
        ComposerJsonFinder $composerJsonFinder,
        NullIOFactory $nullIoFactory
    ) {
        $this->directoryList = $directoryList;
        $this->composerJsonFinder = $composerJsonFinder;
        $this->nullIoFactory = $nullIoFactory;
    }

    /**
     * Create composer instance
     *
     * @return Composer
     * @throws \Exception
     */
    public function create()
    {
        putenv('COMPOSER_HOME=' . $this->directoryList->getPath(DirectoryList::COMPOSER_HOME));

        return ComposerFactory::create(
            $this->nullIoFactory->create(),
            $this->composerJsonFinder->findComposerJson()
        );
    }
}
