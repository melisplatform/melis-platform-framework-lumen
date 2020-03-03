<?php
namespace MelisPlatformFrameworkLumen\Middlewares;

use Closure;
use Illuminate\Http\Testing\MimeType;

class AssetsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $modulesPath = __DIR__ . "/../../../../../thirdparty/Lumen/module";
        // available registered modules
        $lumenModules = scandir($modulesPath);
        $uri = $request->server('REQUEST_URI');
        $url = explode("/" ,$uri);
        if (count($url) > 2) {
            $moduleName = $url[2];
            if (in_array($moduleName,$lumenModules)) {
                $path = DIRECTORY_SEPARATOR . $moduleName ;
                $filePath = null;
                for ($i = 3; $i < count($url); $i++) {
                    $filePath .= DIRECTORY_SEPARATOR . $url[$i];
                }
                $fullPathFile = $modulesPath. DIRECTORY_SEPARATOR . $moduleName. DIRECTORY_SEPARATOR . "assets" . $filePath;
                if (file_exists($fullPathFile)) {
                    $extension = pathinfo($fullPathFile, PATHINFO_EXTENSION);
                    $mimeType = MimeType::get($extension);
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: " . $mimeType);
                    echo file_get_contents($fullPathFile);
                    exit;
                }
            }
        }

        return $next($request);
    }
}