<?php

namespace WonderWp\Plugin\Generator\Generator\Plugin\CPT\ContentProvider;

use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseManagerContentProvider;

class CptManagerContentProvider extends BaseManagerContentProvider
{
    public function getUsesContent()
    {
        return <<<'EOD'
        use __PLUGIN_NS__\Repository\__PLUGIN_CLASSNAME__Repository;
        EOD;
    }

    public function getServicesContent()
    {
        return <<<'EOD'
$this->addService(ServiceInterface::REPOSITORY_SERVICE_NAME, function () {
            //Repository
            return new __PLUGIN_CLASSNAME__Repository();
        });
        $this->addService(ServiceInterface::ACTIVATOR_NAME, function () {
            //Activator
            return new __PLUGIN_ENTITY__Activator($this->getVersion());
        });
EOD;
    }
}
