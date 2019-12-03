
namespace LumenModule\[module_name]\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class [module_name]Provider extends ServiceProvider
{
    const CONFIGS = [
       'table_config' =>  __DIR__ . "/../config/table.config.php",
       'form_config' => __DIR__ . "/../config/form.config.php"
    ];

    public function boot()
    {
        // load routes in the lumen application
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        // load views in the lumen application
        $this->loadViewsFrom(__DIR__ . '/../views','[module_name]');
        // load transations
        $this->loadTranslationsFrom(__DIR__ . '/../language', '[module_name]');
//        // include table config
        $this->addConfigs();
//        // include form config
//        $this->addFormConfig();
    }
    private function addConfigs()
    {
        $configs = [];
        foreach (self::CONFIGS as $key => $val) {
            $configs[$key] = include $val;
        }

        Config::set('[module_name]', $configs);
    }
}