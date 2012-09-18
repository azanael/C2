<?php
require_once 'C2_Cache.class.php';

class C2_Memcache extends Memcache implements C2_Cache
{
    private $_port = 11211;
    private $_timeout = 60;
    private $_compressed = 0;
    
    public function __construct($params = array())
    {
        if (isset($params['hosts'])) {
            foreach ($params['hosts'] as $host) {
                parent::addServer($host, $this->_port);
            }
        }
        if (isset($params['compressed'])) {
            $this->_compressed = $params['compressed'];
        }
        if (isset($params['port'])) {
            $this->_port = $params['port'];
        }
    }
    
    public function set($ns, $key, $var, $timeout = 0)
    {
        return parent::set(self::_key($ns, $key), $var, $this->_compressed, $this->_timeout($timeout));
    }
    
    public function get($ns, $key)
    {
        return parent::get(self::_key($ns, $key));
    }
    
    public function delete($ns, $key = null)
    {
        if ($key === null) {
            return parent::increment($ns, 1);
        }
        return parent::delete(self::_key($ns, $key));
    }
    
    private function _key($ns, $key)
    {
        $nsKey = parent::get($ns);
        if ($nsKey === false) {
            $nsKey = 0;
            parent::set($ns, $nsKey, $this->_compressed, $this->_timeout($timeout));
        }
        $k = is_object($key) ? sha1(serialize($key)) : sha1($key);
        return "$ns:$nsKey:$k";
    }
    
    private function _timeout($time)
    {
        if (is_numeric($time) && $time > 0) {
            return $time;
        }
        return $this->_timeout;
    }
}