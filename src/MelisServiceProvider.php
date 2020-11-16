<?php
namespace MelisPlatformFrameworkLumen;

use Symfony\Component\HttpFoundation\Request;
use Laminas\EventManager\EventManager;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container;

class MelisServiceProvider
{
    /**
     * @var array
     */
    private $config = [];
    /**
     * laminas service manager
     * @var ServiceManager
     */
    protected $laminasServiceManager;
    /**
     * laminas event manager
     * @var
     */
    protected $laminasEventManager;

    public function __construct()
    {
        // run laminas mvc
        $this->laminasMvc();
    }

    /**
     * @param $serviceName
     * @return mixed
     * @throws \Exception
     */
    public static function getService($serviceName)
    {
        $laminasAppConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";

        if (!file_exists($laminasAppConfig)) {
            throw new \Exception("Laminas application config missing");
        }
        // get the laminas application
        $laminasApplication = Application::init(require $laminasAppConfig);

        return $laminasApplication->getServiceManager()->get($serviceName);
    }
    /**
     * @return mixed
     */
    public static function getMelisLocale()
    {
        # get melis back office lang locale
        $container = new Container('meliscore');

        return $container['melis-lang-locale'];
    }

    /**
     * @throws \Exception
     */
    protected function laminasMvc()
    {
        $laminasAppConfig = __DIR__ . "/../../../../config/application.config.php";
        if (!file_exists($laminasAppConfig)) {
            throw new \Exception("Laminas application config missing");
        }
        $laminasConfig = require $laminasAppConfig;;
        $laminasConfig['modules'] = array_merge($laminasConfig['modules'],$this->getMelisBoModuleLoad());

        // get the laminas application
        $laminasApplication = Application::init($laminasConfig);
        // set laminas service manager
        $this->setLaminasServiceManager($laminasApplication->getServiceManager());
        // set laminas event manager
        $this->setLaminasEventManager($laminasApplication->getEventManager());

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getMelisBoModuleLoad()
    {
        $boModuleLoad = __DIR__ . "/../../../../config/melis.module.load.php";
        if (!file_exists($boModuleLoad)) {
            throw new \Exception("Melis back office melis.module.load.php not found");
        }

        return require $boModuleLoad;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getMelisDbConfig()
    {
        $laminasConfig = $this->getLaminasSerivceManager()->get('config');
        if (! isset($laminasConfig['db']) && empty($laminasConfig['db'])) {
            throw new \Exception("No melis database was set");
        }

        return $laminasConfig['db'];
    }

    /**
     * @param array $dbConfig
     */
    public function constructDbConfig()
    {
        // get melis db config
        $dbConfig = $this->getMelisDbConfig();

        return [
            'melis' => [
                'driver' => strtolower($dbConfig['driver']) == 'mysqli' ? 'mysql' : $dbConfig['driver'],
                'port'   => $dbConfig['port'],
                'charset' => $dbConfig['charset'],
                'collation' => 'utf8_general_ci',
                'host' => $dbConfig['hostname'],
                'database' => $dbConfig['database'],
                'username' => $dbConfig['username'],
                'password' => $dbConfig['password']
            ]
        ];
    }

    /**
     * @param ServiceManager $serviceManager
     */
    public function setLaminasServiceManager(ServiceManager $serviceManager)
    {
        $this->laminasServiceManager = $serviceManager;
    }

    /**
     * @return mixed
     */
    public function getLaminasSerivceManager()
    {
        return $this->laminasServiceManager;
    }

    /**
     * @param EventManager $eventManager
     */
    public function setLaminasEventManager(EventManager $eventManager)
    {
        $this->laminasEventManager = $eventManager;
    }

    /**
     * @return mixed
     */
    public function getLaminasEventManager()
    {
        return $this->laminasEventManager;
    }

    /**
     * @return \Laravel\Lumen\Application
     */
    protected function getLumenApp()
    {
        // get the lumen application
        $lumenApp = include_once $_SERVER['DOCUMENT_ROOT'] . "/../thirdparty/Lumen/bootstrap/app.php";

        return $lumenApp;
    }

    /**
     * @param Request|null $request
     * @return false|string
     */
    public function getLumenContent(Request $request = null)
    {
        return $this->getLumenApp()->dispatch($request)->getContent();
    }

    /**
     * add config
     */
    public function addConfig(array $config)
    {
        $this->config = array_merge($this->config,$config);
    }

    /**
     * get config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
