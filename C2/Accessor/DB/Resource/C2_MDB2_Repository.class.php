<?php
require_once 'MDB2.php';
require_once 'C2_Repository.class.php';
require_once dirname(__FILE__) . '/../../../Exception/C2_DBException.class.php';

final class C2_MDB2_Repository implements C2_Repository
{
    private static $_dsn = null;
    private static $_options = null;
    
    public static function init($iniFilePath = null, $target = 'master')
    {
        $path = ($iniFilePath === null) ? '/../Config/db.conf.ini' : $iniFilePath;
        $config = @parse_ini_file($path, true);
        if ($config === false) {
            throw new C2_DBException('Failed to load db configuration file. filepath=' . $path);
        }
        self::$_dsn[$target] = array(
            'phptype' => $config['phptype'],
            'username' => $config['user'],
            'password' => $config['pass'],
            'hostspec' => (is_array($config[$target]['host'])) ? $config[$target]['host'][array_rand($config[$target]['host'])] : $config[$target]['host'],
            'database' => $config['db'],
        );
        self::$_options = array(
            'charset' => $config['charset'],
            'portability' => MDB2_PORTABILITY_NONE,
        );
    }
    
    public static function getConnection($target = 'master')
    {
        if (self::$_dsn === null) {
            throw new C2_DBException('Not set db configuration. Use setConfig method before getting db connection.');
        }
        $con = MDB2::singleton(self::$_dsn[$target], self::$_options);
        if (MDB2_Driver_Common::isError($con)) {
            throw new RuntimeException('C2: Failed to establish MDB2 connection. code=' . $con->getCode() . ' error=' . $con->getMessage() . ' userinfo=' . $con->getUserInfo());
        }
        return $con;
    }
    
    public static function destroyConnection($connection)
    {
        if (!$connection instanceof MDB2_Driver_Common) {
            return false;
        }
        $result = $connection->disconnect();
        if (MDB2_Driver_Common::isError($result)) {
            throw new RuntimeException('C2: Failed to disconnect MDB2 connection. code=' . $result->getCode() . ' error=' . $result->getMessage());
        }
        return true;
    }
}