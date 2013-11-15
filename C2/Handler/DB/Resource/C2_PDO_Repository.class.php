<?php
require_once 'C2_Repository.class.php';
require_once __DIR__ . '/../../../Exception/C2_DBException.class.php';

class C2_PDO_Repository implements C2_Repository
{
    private static $_dsn = null;
    private static $_options = null;
    
    public static function init($iniFilePath = null, $target = 'master')
    {
        $path = ($iniFilePath === null) ? dirname(__FILE__) . '/../Config/db.conf.ini' : $iniFilePath;
        $config = @parse_ini_file($path, true);
        if ($config === false) {
            throw new C2_DBException('Failed to load db configuration file. filepath=' . $path);
        }
        self::$_dsn = array(
            'phptype' => $config['phptype'],
            'username' => $config['user'],
            'password' => $config['pass'],
            'hostspec' => (is_array($config[$target]['host'])) ? $config[$target]['host'][array_rand($config[$target]['host'])] : $config[$target]['host'],
            'database' => $config['db'],
        );
        self::$_options = array(
            PDO::ATTR_AUTOCOMMIT => $config['autocommit'],
            PDO::ATTR_PERSISTENT => $config['persistent'],
        );
    }
    
    public static function getConnection()
    {
        if (self::$_dsn === null) {
            throw new C2_DBException('Not set db configuration. Use init method before getting db connection.');
        }
        try {
            $con = new PDO(self::$_dsn['phptype'] . ':host=' . self::$_dsn['hostspec'] . ';dbname=' . self::$_dsn['database'], self::$_dsn['username'], self::$_dsn['password'], self::$_options);
        } catch (PDOException $e) {
            var_dump(self::$_dsn['phptype'] . ':host=' . self::$_dsn['hostspec'] . ';dbname=' . self::$_dsn['database']);
            throw new C2_DBException($e->getMessage());
        }
        return $con;
    }
    
    public static function destroyConnection($con)
    {
        $con = null;
    }
}