<?php

interface C2_Repository
{
    public static function getConnection($target = null);
    public static function destroyConnection($connection);
}