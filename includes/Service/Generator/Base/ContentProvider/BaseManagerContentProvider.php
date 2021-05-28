<?php

namespace WonderWp\Plugin\Generator\Service\Generator\Base\ContentProvider;

class BaseManagerContentProvider
{

    /**
     * @return string
     */
    public function getUsesContent()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getConfigContent()
    {
        return <<<'EOD'
        $this->setConfig('plugin.capability', $this->getConfig('plugin.capability', 'edit_posts'));
        EOD;
    }

    /**
     * @return string
     */
    public function getControllersContent()
    {
        return <<<'EOD'
/* Uncomment this if your plugin has a public controller
        $this->addController(AbstractManager::PUBLIC_CONTROLLER_TYPE, function () {
            return $plugin_public = new __PLUGIN_ENTITY__PublicController($this);
        }); */
EOD;
    }

    /**
     * @return string
     */
    public function getServicesContent()
    {
        return <<<'EOD'

        $this->addService(ServiceInterface::ACTIVATOR_NAME, function () {
            //  Activator service
            return new __PLUGIN_ENTITY__Activator($this->getVersion());
        });

EOD;
    }

}
