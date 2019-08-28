<?php
namespace MelisPlatformFrameworkLumen\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
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
            return $this->zendServiceManager;
        });
        // event manager
        $this->app->singleton('ZendEventManager' , function(){
            return $this->zendEventManager;
        });
    }

    /**
     * set zend services and sync melis database connection config
     */
    public function boot()
    {
        // run zendMvc
        $melisServices = new MelisServiceProvider();
        // set service manager
        $this->zendServiceManager = $melisServices->getZendSerivceManager();
        // set event manager
        $this->zendEventManager   = $melisServices->getZendEventManager();
        // sync melis database connection into lumen database config
        $this->syncMelisDbConnection($melisServices->constructDbConfig());
    }

    /**
     * include melis db config into lumen db config
     *
     * @param $dbConfig
     */
    public function syncMelisDbConnection($dbConfig)
    {
        // get all lumen db config
        $lumenDbConfig = Config::get('database.connections');
        // pushed melis db config into lumen db config
        $lumenDbConfig = array_merge($lumenDbConfig,$dbConfig);
        // update lumen db config
        Config::set('database.connections',$lumenDbConfig);
    }
}