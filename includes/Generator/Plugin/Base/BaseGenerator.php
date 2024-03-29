<?php

namespace WonderWp\Plugin\Generator\Generator\Plugin\Base;

use Exception;
use WonderWp\Component\PluginSkeleton\AbstractManager;
use WonderWp\Plugin\Generator\Generator\Definition\AbstractGenerator;
use WonderWp\Plugin\Generator\Result\DataCheckResult;
use WonderWp\Plugin\Generator\Result\GenerationResult;
use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseActivatorContentProvider;
use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseAdminControllerContentProvider;
use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseHookServiceContentProvider;
use WonderWp\Plugin\Generator\Generator\Plugin\Base\ContentProvider\BaseManagerContentProvider;
use WP_Error;
use WP_Filesystem_Direct;
use function WonderWp\Functions\array_merge_recursive_distinct;

class BaseGenerator extends AbstractGenerator
{
    //Content Providers
    /** @var BaseManagerContentProvider */
    protected $managerContentProvider;
    /** @var BaseActivatorContentProvider */
    protected $activatorContentProvider;
    /** @var BaseHookServiceContentProvider */
    protected $hookServiceContentProvider;
    /** @var BaseAdminControllerContentProvider */
    protected $adminControllerContentProvider;

    /** Constructor */
    public function __construct(AbstractManager $manager)
    {
        parent::__construct($manager);
        $this->managerContentProvider         = new BaseManagerContentProvider();
        $this->activatorContentProvider       = new BaseActivatorContentProvider();
        $this->hookServiceContentProvider     = new BaseHookServiceContentProvider();
        $this->adminControllerContentProvider = new BaseAdminControllerContentProvider();
    }

    /** @inheritDoc */
    public function generate(): GenerationResult
    {
        $check = $this->checkDatas();

        if ($check->getCode() === 200) {
            try {
                $this
                    ->prepareDatas()
                    ->createBaseFolders()
                    ->generateIndexFile()
                    ->generateBootstrapFile()
                    ->generateManager()
                    ->generateActivator()
                    ->generateDeActivator()
                    ->generateHookService()
                    ->generateAdminController();
            } catch (Exception $e) {
                return new GenerationResult(500, ['msg' => $e->getMessage(), 'exception' => $e]);
            }

            return new GenerationResult(200, [
                'msg' => "
Plugin generated in your plugins folder.
Don't forget to add an autoload entry in your composer.json file, then to launch a composer dump-autoload command, if you'd like to use it right away.
More information in the documentation : http://wonderwp.net/Creating_a_plugin/Getting_Started
",
            ]);
        } else {
            $errors = $check->getData('errors');

            return new GenerationResult(500, ['msg' => implode("\n", $errors), 'errors' => $errors]);
        }
    }

    protected function checkDatas()
    {
        $requiredDatas = ['name', 'namespace'];
        $errors        = [];
        foreach ($requiredDatas as $req) {
            if (empty($this->data[$req])) {
                $errors[$req] = 'Attribute ' . $req . ' is missing';
            }
        }
        $code = empty($errors) ? 200 : 403;

        return new DataCheckResult($code, ['datas' => $this->data, 'errors' => $errors]);
    }

    protected function createBaseFolders(array $folders = [])
    {
        $this->folders                = $folders;
        $this->folders['base']        = WP_PLUGIN_DIR . '/' . sanitize_title($this->data['name']);
        $this->folders['includes']    = $this->folders['base'] . '/includes';
        $this->folders['services']    = $this->folders['includes'] . '/Service';
        $this->folders['controllers'] = $this->folders['includes'] . '/Controller';
        $this->folders['languages']   = $this->folders['base'] . '/languages';
        $errors                       = [];

        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                if (!$this->fileSystem->mkdir($folder, FS_CHMOD_DIR)) {
                    $errors[] = new WP_Error(500, 'Required folder creation failed: ' . $folder);
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception('Plugin base folders generation error : ' . implode("\n", $errors));
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function generateIndexFile()
    {
        $this->importDeliverable('index.php');

        return $this;
    }

    /**
     * @return self
     */
    protected function generateBootstrapFile()
    {
        $pluginMetas = [];

        $pluginMetas['Plugin Name'] = $this->data['name'];
        if (!empty($this->data['uri'])) {
            $pluginMetas['Plugin URI'] = $this->data['uri'];
        }
        if (!empty($this->data['desc'])) {
            $pluginMetas['Description'] = $this->data['desc'];
        }
        $pluginMetas['Version'] = !empty($this->data['plugin_version']) ? $this->data['plugin_version'] : '0.0.1';
        if (!empty($this->data['author'])) {
            $pluginMetas['Author'] = $this->data['author'];
        }
        if (!empty($this->data['author_uri'])) {
            $pluginMetas['Author URI'] = $this->data['author_uri'];
        }
        if (!empty($this->data['licence'])) {
            $pluginMetas['License'] = $this->data['licence'];
        }
        if (!empty($this->data['licence_uri'])) {
            $pluginMetas['License URI'] = $this->data['licence_uri'];
        }

        $pluginMetas['Text Domain'] = !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className']);
        $pluginMetas['Domain Path'] = !empty($this->data['domain_path']) ? $this->data['domain_path'] : '/languages';

        $pluginMetasString = '';
        foreach ($pluginMetas as $key => $val) {
            $pluginMetasString .= "\n" . ' * ' . $key . ': ' . $val;
        }

        $this->importDeliverable('__PLUGIN_SLUG__.php', [
            '__PLUGIN_METAS__' => $pluginMetasString,
        ]);

        $this->importDeliverable('__PLUGIN_SLUG__-constants.php');

        return $this;
    }

    /**
     * @return self
     */
    protected function generateManager($givenReplacements = [])
    {
        $baseReplacements = [
            '//__MANAGER_EXTRA_USES//'            => $this->replacePlaceholders($this->managerContentProvider->getUsesContent()),
            '//__MANAGER_EXTRA_CONFIG__//'        => $this->replacePlaceholders($this->managerContentProvider->getConfigContent()),
            '//__MANAGER_EXTRA_CONTROLLERS__//'   => $this->replacePlaceholders($this->managerContentProvider->getControllersContent()),
            '//__MANAGER_EXTRA_SERVICES__//'      => $this->replacePlaceholders($this->managerContentProvider->getServicesContent()),
            '__PLUGIN_PARENT_MANAGER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\AbstractPluginManager',
            '__PLUGIN_PARENT_MANAGER__'           => 'AbstractPluginManager',
        ];

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Manager.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    /**
     * @return self
     */
    protected function generateActivator($givenReplacements = [])
    {
        $baseReplacements = [
            '//__ACTIVATOR_EXTRA_USES//'            => $this->replacePlaceholders($this->activatorContentProvider->getUsesContent()),
            '//__PLUGIN_ACTIVATION_TASKS__//'       => $this->replacePlaceholders($this->activatorContentProvider->getActivationTasksContent()),
            '__PLUGIN_PARENT_ACTIVATOR_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\Service\AbstractPluginActivator',
            '__PLUGIN_PARENT_ACTIVATOR__'           => 'AbstractPluginActivator',
        ];

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Activator.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    /**
     * @return self
     */
    protected function generateDeActivator(array $givenReplacements = [])
    {
        // TODO
        return $this;
    }

    /**
     * @return self
     */
    protected function generateHookService(array $givenReplacements = [])
    {
        $baseReplacements = [
            '//__PLUGIN_HOOKS_EXTRA_USES__//'         => $this->replacePlaceholders($this->hookServiceContentProvider->getUsesContent()),
            '//__PLUGIN_HOOKS_EXTRA_DECLARATIONS__//' => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksDeclarationsContent()),
            '//__PLUGIN_HOOKS_EXTRA_CALLABLES__//'    => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksCallablesContent()),
            '//__PLUGIN_HOOKS_CLASS_ATTRIBUTES__//'    => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksClassAttributes()),
        ];
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__HookService.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    /**
     * @return self
     */
    protected function generateAdminController(array $givenReplacements = [])
    {
        $baseReplacements = [
            '//__PLUGIN_DEFAULT_ACTION__//'          => $this->replacePlaceholders($this->adminControllerContentProvider->getDefaultActionContent()),
            '__PLUGIN_PARENT_CONTROLLER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\Controller\AbstractPluginBackendController',
            '__PLUGIN_PARENT_CONTROLLER__'           => 'AbstractPluginBackendController',
        ];

        /*if (!empty($this->data['table'])) {
            $replacements['//__PLUGIN_DEFAULT_ACTION__//'] = <<<'EOD'

        public function defaultAction()
        {
        }
EOD;
        }*/

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__AdminController.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements));

        return $this;
    }

    /**
     * @param string $string
     * @param string[] $replacements
     *
     * @return string
     */
    protected function replacePlaceholders($string, array $replacements = [])
    {
        $replacements = array_merge([
            '__PLUGIN_NAME__'       => $this->data['name'],
            '__PLUGIN_SLUG__'       => sanitize_title($this->data['name']),
            '__PLUGIN_DESC__'       => !empty($this->data['desc']) ? $this->data['desc'] : '',
            '__PLUGIN_CONST__'      => strtoupper($this->data['classprefix']),
            '__PLUGIN_CONST_LOW__'  => strtolower($this->data['classprefix']),
            '__PLUGIN_ENTITY__'     => $this->data['classprefix'],
            '__PLUGIN_NS__'         => $this->data['namespace'],
            '__PLUGIN_CLASSNAME__'  => $this->data['className'],
            '__ESCAPED_PLUGIN_NS__' => str_replace('\\', '\\\\', $this->data['namespace']),
        ], $replacements);

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

}
