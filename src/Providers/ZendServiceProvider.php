<?php
namespace MelisPlatformFrameworkLumen\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;

class ZendServiceProvider extends ServiceProvider
{
    /**
     * zend service manager
     * @var
     */
    protected $zendServiceManager;
    /**
     * zend event manager
     * @var
     */
    protected $zendEventManager;

    /**
     * register zend services
     */
    public function register()
    {
        // register zend service manager
        $this->app->singleton('ZendServiceManager' , function(){
            return $this->getZendSerivceManager();
        });
        // register zend event manager
        $this->app->singleton('ZendEventManager', function(){
            return $this->getZendEventManager();
        });
    }

    /**
     * run zendMvc init
     */
    public function boot()
    {
        // run zendMvc
        $this->zendMvc();
        // sync melis database connection into lumen database config
        $this->syncMelisDbConnection($this->getMelisDbConnection());
    }
    /**
     * Register zend services
     */
    protected function zendMvc()
    {
        // Avoid accessing from aritsan command
        $zendAppConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";
        if (!file_exists($zendAppConfig)) {
            throw new \Exception("Zend application config missing");
        }
        // get the zend application
        $zendApplication = Application::init(require $zendAppConfig);
        // set zend service manager
        $this->setZendServiceManager($zendApplication->getServiceManager());
        // set zend event manager
        $this->setZendEventManager($zendApplication->getEventManager());
    }
    protected function getMelisDbConnection()
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
    protected function syncMelisDbConnection( array $dbConfig)
    {
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
        // get all lumen database config
        $lumenDbConfig = Config::get('database.connections');
        // append melis database connection into lumen db config
        $lumenDbConfig['melis'] = [
            'driver' => $driver,
            'port'   => '3306',
            'charset' => $charset,
            'collation' => 'utf8_general_ci',
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];
        // set lumen database connection with melis db config
        Config::set('database.connections',$lumenDbConfig);

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
}