<?php

namespace WonderWp\Plugin\Generator\Service\Generator;

use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Plugin\Generator\Result\GenerationResult;

class CPTGenerator extends BaseGenerator
{
    public function generate()
    {
        try {
            $result = parent::generate();
            $this
                ->generateCPT()
                ->generateRepository()
                ->generatePublicController()
                ->generateLanguages()
            ;
        } catch (\Exception $e) {
            return new GenerationResult(500, ['msg' => $e->getMessage(), 'data' => $e->getTrace()]);
        }

        return $result;
    }

    protected function generateCpt()
    {
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'CPT' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__CPT.php');

        return $this;
    }

    protected function getManagerUsesContent()
    {
        return <<<'EOD'
        use WonderWp\Component\CPT\CustomPostTypeService;
        use __PLUGIN_NS__\CPT\__PLUGIN_CLASSNAME__CPT;
        use __PLUGIN_NS__\Controller\__PLUGIN_CLASSNAME__PublicController;
        use __PLUGIN_NS__\Repository\__PLUGIN_CLASSNAME__Repository;
        EOD;
    }

    protected function getManagerConfigContent()
    {
        return <<<'EOD'
        
        $this->setConfig('plugin.capability', $this->getConfig('plugin.capability', 'edit_posts'));
        $this->setConfig('cpt', $this->getConfig('cpt', new __PLUGIN_CLASSNAME__CPT('',[
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

    protected function generateManager()
    {
        $replacements = [
            '__PLUGIN_PARENT_MANAGER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\AbstractPluginManager',
            '__PLUGIN_PARENT_MANAGER__'           => 'AbstractPluginManager',
            '//__PLUGIN_EXTRA_USES//'             => $this->replacePlaceholders($this->getManagerUsesContent()),
        ];

        $replacements['//__PLUGIN_ENTITY_CONFIG__//'] = $this->replacePlaceholders($this->getManagerConfigContent());

        $replacements['//__PLUGIN_ENTITY_CONTROLLERS__//'] = $this->replacePlaceholders(<<<'EOD'
        $this->addController(AbstractManager::PUBLIC_CONTROLLER_TYPE, function () {
            $pubCtrl = new __PLUGIN_ENTITY__PublicController($this);
            $pubCtrl->setCustomPostType($this->getConfig('cpt'));

            return $pubCtrl;
        });
EOD
        );

        $replacements['//__PLUGIN_ENTITY_SERVICES__//'] = $this->replacePlaceholders(<<<'EOD'

        $this->addService(ServiceInterface::CUSTOM_POST_TYPE_SERVICE_NAME, function() {
            return new CustomPostTypeService($this->getConfig('cpt'), $this);
        });
        $this->addService(ServiceInterface::REPOSITORY_SERVICE_NAME, function() {
            return new __PLUGIN_CLASSNAME__Repository();
        });
        $this->addService(ServiceInterface::ACTIVATOR_NAME, function () {
            //  Activator service
            return new __PLUGIN_ENTITY__Activator($this->getVersion());
        });
EOD
        );

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Manager.php', $replacements);

        return $this;
    }

    protected function getHookServiceExtraDeclarationsContent()
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

    protected function generateHookService()
    {
        $replacements['//__PLUGIN_HOOKS_EXTRA_USES__//']         = $this->replacePlaceholders(<<<'EOD'

use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Component\CPT\CustomPostTypeService;
use __PLUGIN_NS__\CPT\__PLUGIN_CLASSNAME__CPT;
EOD
        );
        $replacements['//__PLUGIN_HOOKS_EXTRA_DECLARATIONS__//'] = $this->replacePlaceholders($this->getHookServiceExtraDeclarationsContent());
        $replacements['//__PLUGIN_HOOKS_EXTRA_CALLABLES__//']    = $this->replacePlaceholders(<<<'EOD'


    public function createCustomPostType()
    {
        /** @var CustomPostTypeService $cptService */
        $cptService = $this->manager->getService(ServiceInterface::CUSTOM_POST_TYPE_SERVICE_NAME);
        $cptService->register();
    }
EOD
        );

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__HookService.php', $replacements);

        return $this;
    }

    protected function getPublicControllerDefaultActionContent()
    {
        return '';
    }

    protected function generatePublicController()
    {
        $replacements = [
            '__PLUGIN_PARENT_CONTROLLER_NAMESPACE__' => 'WonderWp\Component\CPT\CustomPostTypePublicController',
            '__PLUGIN_PARENT_CONTROLLER__'           => 'CustomPostTypePublicController',
        ];

        $replacements['//__PLUGIN_EXTRA_USES//'] = $this->replacePlaceholders(<<<'EOD'

use WonderWp\Component\Repository\PostRepository;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;

EOD
        );

        $replacements['//__PLUGIN_DEFAULT_ACTION__//'] = $this->replacePlaceholders($this->getPublicControllerDefaultActionContent());

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__PublicController.php', $replacements);

        return $this;
    }

    protected function createBaseFolders(array $folders = [])
    {
        $folders['base']   = WP_PLUGIN_DIR . '/' . sanitize_title($this->data['name']);
        $folders['public'] = $folders['base'] . '/public';
        $folders['views']  = $folders['public'] . '/views';

        return parent::createBaseFolders($folders);
    }

    protected function generateRepository()
    {
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Repository' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Repository.php');

        return $this;
    }

    protected function generateLanguages()
    {
        $pot = <<<'EOD'
msgid ""
msgstr ""

EOD;

        $potFile = $this->folders['languages'] . DIRECTORY_SEPARATOR . sanitize_title($this->data['name']) . '.pot';
        $this->fileSystem->put_contents($potFile, $pot, FS_CHMOD_FILE);

        return $this;
    }

}
