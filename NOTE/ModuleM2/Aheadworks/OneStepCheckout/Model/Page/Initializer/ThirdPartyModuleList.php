<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Page\Initializer;

use Magento\Framework\Module\ModuleList;

/**
 * Class ThirdPartyModuleList
 * @package Aheadworks\OneStepCheckout\Model\Page\Initializer
 */
class ThirdPartyModuleList
{
    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var string[]
     */
    private $modules = [];

    /**
     * @var string[]
     */
    private $presentedModules;

    /**
     * @param ModuleList $moduleList
     * @param array $modules
     */
    public function __construct(
        ModuleList $moduleList,
        array $modules = []
    ) {
        $this->moduleList = $moduleList;
        $this->modules = $modules;
    }

    /**
     * Get presented third party modules list
     *
     * @return string[]
     */
    public function getPresentedModules()
    {
        if (!$this->presentedModules) {
            $this->presentedModules = [];
            foreach ($this->modules as $moduleName) {
                if ($this->moduleList->has($moduleName)) {
                    $this->presentedModules[] = $moduleName;
                }
            }
        }
        return $this->presentedModules;
    }
}
