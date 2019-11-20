<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Support\ServiceProvider;

class MelisLumenFrameworkService
{
    /**
     * @var string
     */
    private static $serviceProvidersPath = __DIR__ . "/../../../../../thirdparty/Lumen/bootstrap/service.providers.php";

    /**
     * return service providers file path
     * @return string
     */
    public static function getServiceProvidersPath()
    {
        return self::$serviceProvidersPath;
    }

    /**
     * Can add service provider class in file service.providers.php
     * @param $newClass string
     */
    public static function add($newClass)
    {
        // get service provider file path
        $serviceProviders = require self::getServiceProvidersPath();
        // check if class exists
        if (class_exists($newClass)) {
            // add only when class is not yet listed
            if (!in_array($newClass,$serviceProviders)){
                array_push($serviceProviders,$newClass);
                // prepend and append some string of the array value
                $providers = array_map(function($serviceProviders){
                    return "\t" .$serviceProviders . "::class,";
                },$serviceProviders);
                // make a online string for service providers
                $providers = implode("\t" . PHP_EOL,$providers);
                // comments
                $comments = "/**\n * load here your service provider class for better maintainability\n *  - classes here must be loaded from composer (autoload)\n */";
                // file contents
                $string = "<?php \n" . $comments ."\n"  .
                    "return [\n" . $providers . "\n];";
                // check if file is not writable then make it writable
                if (!is_writable(self::getServiceProvidersPath())) {
                    chmod(self::getServiceProvidersPath(),0777);
                }
                // update file contents
                return self::writeFile(self::getServiceProvidersPath(),$string);
            }
        }
        
        return false;
    }
    private static function writeFile($file,$content)
    {
        if (file_exists($file)) {
            $hanlder = fopen($file,"w");
            fwrite($hanlder,$content);
            fclose($hanlder);
            return true;
        }

        return false;
    }
}