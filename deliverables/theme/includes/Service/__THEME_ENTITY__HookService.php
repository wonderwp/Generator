<?php

namespace __THEME_NS__\Service;

use WonderWp\Component\Hook\AbstractHookService;//__THEME_HOOKS_EXTRA_USES__//

/**
 * Defines the different hooks that are going to be used by your theme
 */
class __THEME_ENTITY__HookService extends AbstractHookService
{
    //__THEME_HOOKS_CLASS_ATTRIBUTES__//
    /**
     * Registering theme hooks
     * @return $this
     */
    public function register()
    {
        /** @var __THEME_ENTITY__AssetManipulatorService $assetsManipulator */
        $assetsManipulator = $this->manager->getService('assetsManipulator');
        $this->addAction('wp_enqueue_scripts', [$assetsManipulator, 'enqueueFrontAssets']);

        return $this;
    }

    //__THEME_HOOKS_EXTRA_CALLABLES__//

}
