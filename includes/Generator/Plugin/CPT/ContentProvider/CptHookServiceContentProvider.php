<?php

namespace WonderWp\Plugin\Generator\Generator\Plugin\CPT\ContentProvider;

use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseHookServiceContentProvider;

class CptHookServiceContentProvider extends BaseHookServiceContentProvider
{

    /**
     * @return string
     */
    public function getUsesContent()
    {
        return <<<'EOD'

use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Component\CPT\CustomPostTypeService;
use __PLUGIN_NS__\CPT\__PLUGIN_CLASSNAME__CPT;
EOD;
    }

    /**
     * @return string
     */
    public function getHooksDeclarationsContent()
    {
        return <<<'EOD'


        //Custom Post type registration
        add_action('init', [$this, 'createCustomPostType']);
        /** @var __PLUGIN_CLASSNAME__CPT $cpt */
        $cpt = $this->manager->getConfig('cpt');
        /** @var CustomPostTypeService $cptService */
        $cptService = $this->manager->getService(ServiceInterface::CUSTOM_POST_TYPE_SERVICE_NAME);
        if(!empty($cpt->getMetaDefinitions())){
            add_action('admin_init', [$cptService, 'createMetasForm']);
            add_action('save_post', [$cptService, 'saveMetasForm'], 10, 2);
        }
EOD;
    }

    public function getHooksCallablesContent()
    {
        return <<<'EOD'

    public function createCustomPostType()
    {
        /** @var CustomPostTypeService $cptService */
        $cptService = $this->manager->getService(ServiceInterface::CUSTOM_POST_TYPE_SERVICE_NAME);
        $cptService->register();
    }
EOD;
    }
}
