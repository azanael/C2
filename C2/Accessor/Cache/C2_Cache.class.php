<?php

interface C2_Cache
{
    public function get($ns, $key);
    public function set($ns, $key, $var, $timeout = 0);
    public function delete($ns, $key = null);
}