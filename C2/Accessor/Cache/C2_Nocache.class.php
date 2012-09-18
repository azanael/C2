<?php

class C2_Nocache implements C2_Cache
{
    public function set($ns, $key, $var, $timeout = 0)
    {
        return true;
    }
    
    public function get($ns, $key)
    {
        return false;
    }
    
    public function delete($ns, $key = null)
    {
        return true;
    }
}