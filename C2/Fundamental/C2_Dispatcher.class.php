<?php

class C2_Dispatcher
{
    const CONTROLLER_ERROR = 'ErrorController';
    
    public function dispatch($options = null, $controllerName = 'RootController')
    {
        if ($controllerName === null) {
            $controllerName = self::_findController();
        }
        $viewer = new C2_SmartyViewer();
        try {
            $controller = self::_getControllerInstance($controllerName);
            $assigns = $controller->assign();
            $template = self::_findTemplate();
            if (!empty($assigns)) {
                foreach ($assigns as $key => $value) {
                    $viewer->assign($key, $value);
                }
            }
            $viewer->view($template);
        } catch (Exception $e) {
            $viewer->view('503.tpl');
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
    
    private static function _getControllerInstance($controllerName, $options = null)
    {
        if (!is_file($controllerName)) {
            if ($controllerName === self::CONTROLLER_ERROR) {
                throw new C2_Exception("CRITICAL: Both Controller $controllerName and Error Controller are not existed!");
            }
            return self::_getControllerInstance(self::CONTROLLER_ERROR);
        }
        require_once C2_BASE_DIR . '/App/' . $controllerName;
        $className = substr($controllerName, strrpos($controllerName, '/'));
        return new $className();
    }
    
    private static function _findTemplate()
    {
        
    }
}