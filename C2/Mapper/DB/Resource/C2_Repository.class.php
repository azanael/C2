<?php

interface C2_Repository
{
    public static function getConnection();
    public static function destroyConnection($connection);
}