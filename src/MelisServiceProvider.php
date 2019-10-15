<?php
namespace MelisPlatformFrameworkLumen;

use Symfony\Component\HttpFoundation\Request;
use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;

class MelisServiceProvider
{
    /**
     * @var array
     */
    private $config = [];
    /**
     * zend service manager
     * @var ServiceManager
     */
    protected $zendServiceManager;
    /**
     * zend event manager
     * @var
     */
    protected $zendEventManager;

    public function __construct()
    {
        // run zend mvc
        $this->zendMvc();
    }

    /**
     * @param $serviceName
     * @return mixed
     * @throws \Exception
     */
    public static function getService($serviceName)
    {
        $zendAppConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";

        if (!file_exists($zendAppConfig)) {
            throw new \Exception("Zend application config missing");
        }
        // get the zend application
        $zendApplication = Application::init(require $zendAppConfig);

        return $zendApplication->getServiceManager()->get($serviceName);
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
    protected function zendMvc()
    {
        $zendAppConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";
        if (!file_exists($zendAppConfig)) {
            throw new \Exception("Zend application config missing");
        }
        $zendConfig = require $zendAppConfig;;
        $zendConfig['modules'] = array_merge($zendConfig['modules'],$this->getMelisBoModuleLoad());

        // get the zend application
        $zendApplication = Application::init($zendConfig);
        // set zend service manager
        $this->setZendServiceManager($zendApplication->getServiceManager());
        // set zend event manager
        $this->setZendEventManager($zendApplication->getEventManager());

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getMelisBoModuleLoad()
    {
        $boModuleLoad = $_SERVER['DOCUMENT_ROOT'] . "/../config/melis.module.load.php";
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
        $zendConfig = $this->getZendSerivceManager()->get('config');
        if (! isset($zendConfig['db']) && empty($zendConfig['db'])) {
            throw new \Exception("No melis database was set");
        }

        return $zendConfig['db'];
    }

    /**
     * @param array $dbConfig
     */
    public function constructDbConfig()
    {
        // melis db config
        $dbConfig = $this->getMelisDbConfig();
        // get db dsn
        $dbConnection = explode(';', $dbConfig['dsn']);
        // get database driver
        $driver = explode(':', $dbConnection[0])[0];
        // get host
        $host = explode('=', $dbConnection[1])[1];
        // get database name
        $database = explode('=', $dbConnection[0])[1];
        // db charset
        $charset = explode('=',$dbConnection[2])[1];
        // get database username
        $username = $dbConfig['username'];
        // get database password
        $password = $dbConfig['password'];
        // append melis database connection into lumen db config
        $melisDbConfig['melis'] = [
            'driver' => $driver,
            'port'   => '3306',
            'charset' => $charset,
            'collation' => 'utf8_general_ci',
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];


        return $melisDbConfig;
    }

    /**
     * @param ServiceManager $serviceManager
     */
    public function setZendServiceManager(ServiceManager $serviceManager)
    {
        $this->zendServiceManager = $serviceManager;
    }

    /**
     * @return mixed
     */
    public function getZendSerivceManager()
    {
        return $this->zendServiceManager;
    }

    /**
     * @param EventManager $eventManager
     */
    public function setZendEventManager(EventManager $eventManager)
    {
        $this->zendEventManager = $eventManager;
    }

    /**
     * @return mixed
     */
    public function getZendEventManager()
    {
        return $this->zendEventManager;
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
    public function addConfig(array $config)
    {
        $this->config = array_merge($this->config,$config);
    }
    public function getConfig()
    {
        return $this->config;
    }
    

}