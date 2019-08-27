<?php
namespace MelisPlatformFrameworkLumen\Providers;

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