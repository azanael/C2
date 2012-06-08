<?php
require_once __DIR__ . '/../Mapper/DB/Resource/C2_Repository.class.php';

/**
 * Handling php session.
 *
 * @version >= PHP 5.4.0
 */
class C2_SessionDBHandler implements SessionHandlerInterface
{
    private $_con;
    
    public function __construct(C2_Repository $con)
    {
        $this->_con = $con;
    }
    
    public function close()
    {
        
    }
    
    public function destroy($session_id)
    {
        
    }
    
    public function gc($maxlifetime)
    {
        
    }
    
    public function open($save_path, $session_id)
    {
        
    }
    
    public function read($session_id)
    {
        
    }
    
    public function write($session_id, $session_data)
    {
        
    }
}