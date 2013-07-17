<?php
require_once 'C2_Cache.class.php';

class C2_APC implements C2_Cache
{
    public function get($ns, $key)
    {
        $r = apc_fetch(self::_key($ns, $key));
        return $r;
    }
    
    public function set($ns, $key, $var, $timeout = 0)
    {
        return apc_store(self::_key($ns, $key), $var, $timeout);
    }
    
    public function delete($ns, $key = null)
    {
        if ($key === null) {
            $it = new APCIterator('user', '/^' . preg_quote($ns) . '\:/', APC_ITER_VALUE);
            return apc_delete($it);
        }
        return apc_delete(self::_key($ns, $key));
    }
    
    private static function _key($ns, $key)
    {
        $k = is_object($key) ? sha1(serialize($key)) : sha1($key);
        return "$ns:$key";
    }
}