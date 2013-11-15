<?php
require_once __DIR__ . '/../Mapper/DB/Manipulator/C2_DB_Manipulator.class.php';

/**
 * Handling php session.
 */
class C2_SessionDBHandler
{
    private $_db;
    
    public function __construct(C2_DB_Manipulator $db)
    {
        $this->_db = $db;
    }
    
    public function open($save_path, $session_id)
    {
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function read($session_id)
    {
        $sql = 'SELECT session_data FROM c2_session WHERE session_id = ? AND status = 1 LIMIT 1';
        try {
            $r = $this->_db->readOne($sql, array($session_id));
        } catch (C2_DBException $e) {
            throw new C2_RuntimeException($e->getMessage());
        }
        return $r['session_data'];
    }
    
    public function write($session_id, $session_data)
    {
        $sql = 'INSERT INTO c2_session (session_id, session_data, status, update_time) VALUES (?, ?, 1, NOW()) LIMIT 1';
        try {
            $r = $this->_db->create($sql, array($session_id, $session_data));
        } catch (C2_DBException $e) {
            throw new C2_RuntimeException($e->getMessage());
        }
        return true;
    }
    
    public function destroy($session_id)
    {
        $sql = 'UPDATE c2_session SET status = 0 WHERE session_id = ? LIMIT 1';
        try {
            $r = $this->_db->update($sql, array($session_id));
        } catch (C2_DBException $e) {
            throw new C2_RuntimeException($e->getMessage());
        }
        return true;
    }
    
    public function gc($maxlifetime)
    {
        $sql = 'DELETE FROM c2_session WHERE update_time < DATE_ADD(current_timestamp, INTERVAL -? SECOND)';
        try {
            $r = $this->_db->delete($sql, array($maxlifetime));
        } catch (C2_DBException $e) {
            throw new C2_RuntimeException($e->getMessage());
        }
        return true;
    }
}