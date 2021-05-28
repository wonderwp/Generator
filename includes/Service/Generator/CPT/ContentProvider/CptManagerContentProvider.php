<?php

namespace WonderWp\Plugin\Generator\Service\Generator\CPT\ContentProvider;

use WonderWp\Plugin\Generator\Service\Generator\Base\ContentProvider\BaseManagerContentProvider;

class CptManagerContentProvider extends BaseManagerContentProvider
{
    public function getUsesContent()
    {
        return <<<'EOD'
        use WonderWp\Component\CPT\CustomPostTypeService;
        use __PLUGIN_NS__\CPT\__PLUGIN_CLASSNAME__CPT;
        use __PLUGIN_NS__\Controller\__PLUGIN_CLASSNAME__PublicController;
        use __PLUGIN_NS__\Repository\__PLUGIN_CLASSNAME__Repository;
        EOD;
    }

    public function getConfigContent()
    {
        return <<<'EOD'
$this->setConfig('plugin.capability', $this->getConfig('plugin.capability', 'edit_posts'));
        $this->setConfig('cpt', $this->getConfig('cpt', new __PLUGIN_CLASSNAME__CPT(WWP_PLUGIN___PLUGIN_CONST___NAME,[
            'capabilities'        => [
                'delete_posts'        => $this->getConfig('plugin.capability'),
                'delete_others_posts' => $this->getConfig('plugin.capability'),
                'edit_post'           => $this->getConfig('plugin.capability'),
                'read_post'           => $this->getConfig('plugin.capability'),
                'delete_post'         => $this->getConfig('plugin.capability'),
                'edit_posts'          => $this->getConfig('plugin.capability'),
                'edit_others_posts'   => $this->getConfig('plugin.capability'),
                'publish_posts'       => $this->getConfig('plugin.capability'),
                'read_private_posts'  => $this->getConfig('plugin.capability'),
                'create_posts'        => $this->getConfig('plugin.capability'),
            ],
        ])));
EOD;
    }

    public function getControllersContent()
    {
        return <<<'EOD'
$this->addController(AbstractManager::PUBLIC_CONTROLLER_TYPE, function () {
            $pubCtrl = new __PLUGIN_ENTITY__PublicController($this);
            $pubCtrl->setCustomPostType($this->getConfig('cpt'));

            return $pubCtrl;
        });
EOD;
    }

    public function getServicesContent()
    {
        return <<<'EOD'
$this->addService(ServiceInterface::CUSTOM_POST_TYPE_SERVICE_NAME, function() {
            //Custom Post Type
            return new CustomPostTypeService($this->getConfig('cpt'), $this);
        });
        $this->addService(ServiceInterface::REPOSITORY_SERVICE_NAME, function() {
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
