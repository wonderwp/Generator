<?php

namespace WonderWp\Plugin\Generator\Service\Generator;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\VoidLogger;
use WonderWp\Component\Logging\WpCliLogger;
use WonderWp\Plugin\Generator\GeneratorManager;
use WonderWp\Plugin\Generator\Result\DataCheckResult;
use WonderWp\Plugin\Generator\Result\GenerationResult;

class BaseGenerator implements GeneratorInterface
{

    /** @var string[] */
    protected $data;
    /** @var Container */
    protected $container;
    /** @var GeneratorManager */
    protected $manager;
    /** @var  \WP_Filesystem_Direct */
    protected $fileSystem;
    /** @var array */
    protected $folders;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }

    /** @inheritDoc */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /** @inheritDoc */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /** Constructor */
    public function __construct()
    {
        $this->container  = Container::getInstance();
        $this->fileSystem = $this->container['wwp.fileSystem'];
        $this->manager    = $this->container['wonderwp_generator.Manager'];
        $this->logger     = new VoidLogger();
    }

    /** @inheritDoc */
    public function generate()
    {
        $check = $this->checkDatas();

        if ($check->getCode() === 200) {
            try {
                $this
                    ->prepareDatas()
                    ->createBaseFolders()
                    ->generateIndexFile()
                    ->generateBootstrapFile()//✓
                    ->generateManager()//✓
                    ->generateActivator()//✓
                    ->generateDeActivator()//
                    ->generateHookService()//✓
                    ->generateAdminController()//✓
                ;
            } catch (\Exception $e) {
                return new GenerationResult(500, ['msg' => $e->getMessage(), 'exception' => $e]);
            }

            return new GenerationResult(200, ['msg' => 'Plugin generation completed']);
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

    protected function prepareDatas()
    {
        if (empty($this->data['classprefix'])) {
            $frags                     = explode('\\', $this->data['namespace']);
            $this->data['classprefix'] = end($frags);
        }
        $this->data['className'] = $this->data['classprefix'];

        return $this;
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
                    $errors[] = new \WP_Error(500, 'Required folder creation failed: ' . $folder);
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Plugin generation error : ' . implode("\n", $errors));
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

        return $this;
    }

    /**
     * @return self
     */
    protected function generateManager()
    {
        $replacements = [
            '//__PLUGIN_ENTITY_CONFIG__//'        => '',
            '//__PLUGIN_ENTITY_SERVICES__//'      => '',
            '__PLUGIN_PARENT_MANAGER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\AbstractPluginManager',
            '__PLUGIN_PARENT_MANAGER__'           => 'AbstractPluginManager',
            '//__PLUGIN_EXTRA_USES//'             => '',
            '//__PLUGIN_ENTITY_CONTROLLERS__//'   => <<<'EOD'
        /* Uncomment this if your plugin has a public controller
        $this->addController(AbstractManager::PUBLIC_CONTROLLER_TYPE, function () {
            return $plugin_public = new __PLUGIN_ENTITY__PublicController($this);
        });
        */
EOD
            ,
        ];

        $replacements['//__PLUGIN_ENTITY_SERVICES__//'] = $this->replacePlaceholders(<<<'EOD'

        $this->addService(ServiceInterface::ACTIVATOR_NAME, function () {
            //  Activator service
            return new __PLUGIN_ENTITY__Activator($this->getVersion());
        });

EOD
        );

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Manager.php', $replacements);

        return $this;
    }

    /**
     * @return self
     */
    protected function generateActivator()
    {
        $replacements = [
            '//__PLUGIN_ACTIVATION_TASKS__//' => <<<'EOD'
        $this->copyLanguageFiles(dirname(dirname(__DIR__)) . '/languages');
EOD
            ,
        ];

        $replacements['__PLUGIN_PARENT_ACTIVATOR_NAMESPACE__'] = 'WonderWp\Component\PluginSkeleton\Service\AbstractPluginActivator';
        $replacements['__PLUGIN_PARENT_ACTIVATOR__']           = 'AbstractPluginActivator';

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__Activator.php', $replacements);

        return $this;
    }

    /**
     * @return self
     */
    protected function generateDeActivator()
    {
        // TODO
        return $this;
    }

    /**
     * @return self
     */
    protected function generateHookService()
    {
        $replacements = [
            '//__PLUGIN_HOOKS_EXTRA_USES__//'         => $this->replacePlaceholders(<<<'EOD'
use WonderWp\Component\PluginSkeleton\AbstractManager;
EOD
            ),
            '//__PLUGIN_HOOKS_EXTRA_DECLARATIONS__//' => $this->replacePlaceholders(<<<'EOD'
        //Menus
        //add_action('admin_menu', [$this, 'customizeMenus']); //If you want to add a link to your plugin in the admin menu, uncomment this then modify the customizeMenus method
EOD)
            ,
            '//__PLUGIN_HOOKS_EXTRA_CALLABLES__//'    => $this->replacePlaceholders(<<<'EOD'
/**
     * Add plugin menu entry in admin menu
     */
    public function customizeMenus()
    {
        //Get admin controller
        $adminController = $this->manager->getController(AbstractManager::ADMIN_CONTROLLER_TYPE);
        $callable        = [$adminController, 'route'];

        //Add entry in admin menu
        add_menu_page('__PLUGIN_NAME__', '__PLUGIN_NAME__', 'read', WWP_PLUGIN___PLUGIN_CONST___NAME, $callable);
    }
EOD)
            ,

        ];
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__HookService.php', $replacements);

        return $this;
    }

    /**
     * @return self
     */
    protected function generateAdminController()
    {
        $replacements = [
            '//__PLUGIN_DEFAULT_ACTION__//'          => '',
            '__PLUGIN_PARENT_CONTROLLER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\Controller\AbstractPluginBackendController',
            '__PLUGIN_PARENT_CONTROLLER__'           => 'AbstractPluginBackendController',
        ];

        if (!empty($this->data['table'])) {
            $replacements['//__PLUGIN_DEFAULT_ACTION__//'] = <<<'EOD'

        public function defaultAction()
        {
        }
EOD;
        }

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . '__PLUGIN_ENTITY__AdminController.php', $replacements);

        return $this;
    }

    /**
     * @param string $deliverable
     * @param array  $replacements
     *
     * @return bool
     */
    protected function importDeliverable($deliverable, array $replacements = [])
    {
        $src = implode(DIRECTORY_SEPARATOR, [$this->manager->getConfig('path.root'), 'deliverables', $deliverable]);

        if (!$this->fileSystem->exists($src) || !$this->fileSystem->is_readable($src)) {
            throw new \RuntimeException(sprintf('The deliverable file "%s" does not exist or is not readable', $src));
        }

        $content = $this->replacePlaceholders($this->fileSystem->get_contents($src), $replacements);
        $dst     = implode(DIRECTORY_SEPARATOR, [$this->folders['base'], $this->replacePlaceholders($deliverable)]);

        if (!$this->fileSystem->exists(dirname($dst))) {
            $this->fileSystem->mkdir(dirname($dst));
        }

        if ($this->fileSystem->exists($dst)) {
            $this->fileSystem->delete($dst);
        }

        return $this->fileSystem->put_contents($dst, $content, FS_CHMOD_FILE);
    }

    /**
     * @param string   $string
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
