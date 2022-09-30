<?php

namespace __PLUGIN_NS__\Service;

use WonderWp\Plugin\Core\Framework\Doctrine\AbstractDoctrineEMLoaderService;

class __PLUGIN_ENTITY__DoctrineEMLoaderService extends AbstractDoctrineEMLoaderService
{
    /**
     * @return static
     */
    public function register()
    {
        return $this->registerEntityPath(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Entity']));
    }
}
