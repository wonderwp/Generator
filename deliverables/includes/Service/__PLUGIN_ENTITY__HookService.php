<?php

namespace __PLUGIN_NS__\Service;

use WonderWp\Component\Hook\AbstractHookService;//__PLUGIN_HOOKS_EXTRA_USES__//

/**
 * Defines the different hooks that are going to be used by your plugin
 */
class __PLUGIN_ENTITY__HookService extends AbstractHookService
{
    /**
     * Run
     * @return $this
     */
    public function register()
    {

        /**
         * Admin Hooks
         */

        //Translate
        add_action('plugins_loaded', [$this, 'loadTextdomain']);//__PLUGIN_HOOKS_EXTRA_DECLARATIONS__//

        return $this;
    }

    //__PLUGIN_HOOKS_EXTRA_CALLABLES__//

}
