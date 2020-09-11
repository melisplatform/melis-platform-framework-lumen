<?php
namespace MelisPlatformFrameworkLumen\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
use MelisPlatformFrameworkLumen\Service\MelisPlatformToolLumenService;
use Laminas\EventManager\EventManager;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container;
use Laminas\View\HelperPluginManager;

class LaminasServiceProvider extends ServiceProvider
{
    /**
     * laminas service manager
     * @var
     */
    protected $laminasServiceManager;

    /**
     * laminas event manager
     * @var
     */
    protected $laminasEventManager;

    /**
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     * register laminas services
     */
    public function register()
    {
        // register laminas service manager
        $this->app->singleton('LaminasServiceManager' , function(){
            return $this->laminasServiceManager;
        });
        // event manager
        $this->app->singleton('LaminasEventManager' , function(){
            return $this->laminasEventManager;
        });
        $this->app->singleton('LaminasTranslator' , function(){
            return $this->laminasServiceManager->get('translator');
        });


    }

    /**
     * set laminas services and sync melis database connection config
     */
    public function boot()
    { 
        /**
         * no running of laminas when in CLI mode
         */
        if (!$this->app->has('LaminasServiceManager')) 
            return;

        // run laminasMvc
        $melisServices = new MelisServiceProvider();
        // set service manager
        $this->laminasServiceManager = $melisServices->getLaminasSerivceManager();
        // set event manager
        $this->laminasEventManager   = $melisServices->getLaminasEventManager();
        // set helper manager
        $this->viewHelperManager  = $this->laminasServiceManager->get('ViewHelperManager');
        // sync melis database connection into lumen database config
        $this->syncMelisDbConnection($melisServices->constructDbConfig());
        // set application locale
        $this->setLocale();


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

    public function syncLaminasMelisViewHelpers()
    {
        // get all registered view helper
        $registerdViewHelpers = $this->viewHelperManager->getRegisteredServices();
        $laminasMelisViewHelpers = $registerdViewHelpers['invokableClasses'];
        $laminasMelisViewHelpers = array_merge($laminasMelisViewHelpers,$registerdViewHelpers['aliases']);
        $laminasMelisViewHelpers = array_merge($laminasMelisViewHelpers,$registerdViewHelpers['factories']);

        // selective view helper classes in order not to complicate with lumen pre-defined  classes
        $allowedHelpers = [
            'meliscoreicon',
            "meliscmsicon",
            "melismarketingicon",
            "meliscommerceicon",
            "melisothersicon",
            "meliscustomicon",
            "melisgenerictable",
            "melistag",
            "melislink",
            "melishomepagelink",
            "melispagelanglink",
            "sitetranslate",
            "siteconfig",
            "melisdatatable",
        ];
        // register view helpers
        foreach ($laminasMelisViewHelpers as $idx => $val) {
            if(in_array($val,$allowedHelpers)) {
                $this->app->singleton($val,function() use ($val) {
                    return $this->viewHelperManager->get($val);
                });
            }
        }
    }

    private function setLocale()
    {
        // get melis back office locale
        $melisBoLocale = new Container('meliscore');
        // loclae
        $locale = explode('_',$melisBoLocale['melis-lang-locale'])[0];
        // set locale
        app('translator')->setLocale($locale);
    }



}
