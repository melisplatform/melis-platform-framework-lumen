<?php
namespace MelisPlatformFrameworkLumen\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

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
     * @var HelperPluginManager
     */
    protected $viewHelperManager;
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
        $this->app->singleton('ZendTranslator' , function(){
            return $this->zendServiceManager->get('translator');
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
        // set helper manager
        $this->viewHelperManager  = $this->zendServiceManager->get('viewhelpermanager');
        // sync melis database connection into lumen database config
        $this->syncMelisDbConnection($melisServices->constructDbConfig());
    //    $this->syncZendMelisViewHelpers();
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
    public function syncZendMelisViewHelpers()
    {
        // get all registered view helper
        $registerdViewHelpers = $this->viewHelperManager->getRegisteredServices();
        $zendMelisViewHelpers = $registerdViewHelpers['invokableClasses'];
        $zendMelisViewHelpers = array_merge($zendMelisViewHelpers,$registerdViewHelpers['aliases']);
        $zendMelisViewHelpers = array_merge($zendMelisViewHelpers,$registerdViewHelpers['factories']);
        // exclusion in order not to complicate with lumen defined classes
        $excluded = [
            'url',
            "Url"
        ];

        // register all zend view helpers
        foreach ($zendMelisViewHelpers as $idx => $val) {
            if(! in_array($val,$excluded)) {
                $this->app->singleton($val,function() use ($val) {
                    return $this->viewHelperManager->get($val);
                });
            }
        }
    }

}