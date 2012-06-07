<?php

class C2_ClassLoader
{
    public static function load($filePath, $className)
    {
        if (!is_readable($filePath)) {
            throw new C2_Exception("Unreadable $filePath include_path=" . get_include_path());
        }
        require_once $filePath;
        if (!class_exists($className)) {
            throw new C2_Exception("Unloadable $className. class is not exists.");
        }
        return self::applyAnnotations(new $className);
    }
    
    private static function applyAnnotations($instance, $className)
    {
        $clazz = new ReflectionClass($className);
        $docComment = $clazz->getDocComment();
        
        // do something with annotations.
        
        return $instance;
    }
    
}