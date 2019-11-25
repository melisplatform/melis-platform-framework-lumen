namespace LumenModule\[module_name]\Providers;

use Illuminate\Support\ServiceProvider;

class [module_name]Provider extends ServiceProvider
{
    public function boot()
    {
        // load routes in the lumen application
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        // load views in the lumen application
        $this->loadViewsFrom(__DIR__ . '/../views','[module_name]');
//        // load transations
//        $this->loadTranslationsFrom(__DIR__ . '/../language', 'lumenDemo');
//        // include table config
//        $this->addTableConfig();
//        // include form config
//        $this->addFormConfig();
    }
}