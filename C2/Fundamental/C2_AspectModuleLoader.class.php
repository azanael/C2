<?php

class C2_AspectModuleLoader
{
    private static $_modules;
    
    public static function load($moduleName)
    {
        if (empty($moduleName)) {
            throw new C2_Exception("Aspect Module Name is not specified.");
        }
        if (isset(self::$_modules[$moduleName])) {
            return self::$_modules[$moduleName]; // Singleton
        }
        $className = ucfirst($moduleName) . 'Aspect';
        $classFile = C2_BASE_DIR . "/Module/Aspect/$className.class.php";
        if (!is_file($classFile)) {
            return false;
        }
        include $classFile;
        $module = new $className();
        self::$_modules[$moduleName] = $module;
        return $module;
    }
}