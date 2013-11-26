<?php

class NoviewAspect implements C2_Aspect
{
    public function exec($args = null)
    {
        return array('noview' => true);
    }
}