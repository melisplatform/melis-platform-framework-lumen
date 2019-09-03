# melis-platform-framwork-lumen

This module brings the Lumen microframework inside melis platform and has a ServiceProvider to access all Melis services.

## Getting Started

These instructions will guide you to run the lumen microframework inside melis platform.

### Prerequisites

This module requires melisplatform/melis-core and laravel/lumen-framework in order to have this module running. This will automatically be done when using composer.
 
### Installing

```
composer require melisplatform/melis-platform-framework-lumen
```

### Service Providers

To use the service provider , just add the line below in the \bootstrap\app.php file in "Register Service Providers" area.
```
$app->register(\MelisPlatformFrameworkLumen\Providers\ZendServiceProvider::class)
```

You can also use the class **MelisServiceProvder**  anywhere in the app in getting melis services.

### Usage

Here's an example of direct calling of a **Model** from *melis-core* inside a lumen controller.

```
$melisCoreLangTable = app('ZendServiceManager)->get('MelisCoreTableLang');
$resultArray        = $mesliCoreLangTable->fetchAll()->toArray();
```

Example of using the class **MelisServiceProvider**

```
use MelisPlatformFrameworkLumen\MelisServiceProvider;

$melisServiceProvider = new MelisServiceProvider();
$melisCoreLangTable   = $melisServiceProvider->getService('MelisCoreTableLang');
$resultArray          = $melisCoreLangTable->fetchAll()->toArray();
```

Addtional info :

1. In getting a melis service/table, just look for **module.config.php** in every melisplatform module. Look for **service_manager** key, you can use array keys under **aliases** or under **factories**.

    Example : MelisCoreTableLang

2. You can find the **file location** according to its current value.
    
    Example : MelisCoreTableLang => **'MelisCore\Model\Tables\MelisLangTable'**
    
    File location : *melis-core/src/Model/Tables/MelisLangTable.php* , in here you can see all the available functions.


## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-core/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details


