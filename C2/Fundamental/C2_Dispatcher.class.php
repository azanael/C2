<?php
require_once 'C2_AnnotationReader.class.php';

/*
 * TODO MEMO
 * @noview outputしない
 * @login
 */
class C2_Dispatcher
{
    const CONTROLLER_ERROR = 'ErrorController';
    
    public function dispatch($options = null, $controller = null)
    {
        if ($controller === null) {
            $controller = self::_findController();
        }
        try {
            
        } catch (Exception $e) {
            
        }
    }
    
    private static function _findController()
    {
        $dirs = split('/', $_SERVER['SCRIPT_NAME']);
        array_pop($dirs);
        $controller = '';
        while (!empty($dirs)) {
            $dir = array_pop($dirs);
            if (empty($dir)) {
                $dir = 'Index';
            }
            $controller .= '/' . ucfirst($dir);
        }
        return $controller;
    }
    
    private static function _getControllerInstance($controller)
    {
        if (!is_file($controller)) {
            if ($controller === self::CONTROLLER_ERROR) {
                throw new C2_Exception("CRITICAL: Both Controller $controller and Error Controller are not existed!");
            }
            return self::_getControllerInstance(self::CONTROLLER_ERROR);
        }
        require_once C2_BASE_DIR . '/App/' . $controller;
        $className = substr($controller, strrpos($controller, '/'));
        $reflection = new ReflectionClass($className);
        $annoReader = new C2_AnnotationReader();
        $annos = $annoReader->getAnnotations($reflection->getDocComment());
        if (!empty($annos)) {
            foreach ($annos as $anno) {
            }
        }
        
    }
}