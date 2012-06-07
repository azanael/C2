<?php
require_once 'C2_DB_Manipulator.class.php';
require_once dirname(__FILE__) . '/../../Exception/C2/C2_DBException.class.php';

class C2_MDB2_Manipulator implements C2_DB_Manipulator
{
    private $_con = null;
    
    private $_lastQuery = '';
    private $_lastBinds = array();
    
    public function __construct(MDB2_Driver_Common $con)
    {
        $this->_con = $con;
    }
    
    public function isInTransaction()
    {
        return $this->_con->inTransaction();
    }
    
    public function getLastQuery()
    {
        return $this->_lastQuery;
    }
    
    public function getLastBinds()
    {
        return $this->_lastBinds;
    }
    
    public function create($sql, array $bind = array())
    {
        $sth = $this->_con->prepare($sql, null, MDB2_PREPARE_MANIP);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ':' . $sth->getUserInfo(), $sth->getCode());
        }
        $r = $sth->execute($bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getUserInfo(), $r->getCode());
        }
        if ($r != 1) {
            throw new C2_DBException('expected 1 insert, but affected ' . $r);
        }
        return $this->_con->lastInsertID();
    }
    
    public function createMultiple($sql, array $bind = array())
    {
        $sth = $this->_con->prepare($sql, null, MDB2_PREPARE_MANIP);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ':' . $sth->getUserInfo(), $sth->getCode());
        }
        $r = $this->_con->extended->executeMultiple($sth, $bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getUserInfo(), $r->getCode());
        }
        if (count($bind) != $r) {
            throw new C2_DBException('expected ' . count($bind) . ' inserts, but affected ' . $r);
        }
        return $r;
    }
    
    public function isInTransaction()
    {
    	if ($this->_con->inTransaction()) {
    		return true;
    	}
    	return false;
    }
    
    public function readOne($sql, array $bind = array())
    {
        $this->_con->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $this->_con->setLimit(1);
        $sth = $this->_con->prepare($sql);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ":" . $sth->getMessage(), $sth->getCode());
        }
        $r = $sth->execute($bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getMessage(), $r->getCode());
        }
        $row = $r->fetchRow();
        $r->free();
        return $row;
    }
    
    public function read($sql, array $bind = array(), $limit = null, $offset = 0)
    {
        $this->_con->setFetchMode(MDB2_FETCHMODE_ASSOC);
        if ($limit !== null) {
            $this->_con->setLimit($limit, $offset);
        }
        $sth = $this->_con->prepare($sql);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ':' . $sth->getUserInfo(), $sth->getCode());
        }
        $r = $sth->execute($bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getUserInfo(), $r->getCode());
        }
        $return = array();
        while ($row = $r->fetchRow()) {
            array_push($return, $row);
        }
        $r->free();
        return $return;
    }
    
    public function update($sql, array $bind)
    {
        $sth = $this->_con->prepare($sql, null, MDB2_PREPARE_MANIP);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ':' . $sth->getUserInfo(), $sth->getCode());
        }
        $r = $sth->execute($bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getUserInfo(), $r->getCode());
        }
        return $r;
    }
    
    public function delete($sql, array $bind)
    {
        $sth = $this->_con->prepare($sql, null, MDB2_PREPARE_MANIP);
        if (MDB2::isError($sth)) {
            throw new C2_DBException($sql . ':' . $sth->getUserInfo(), $sth->getCode());
        }
        $r = $sth->execute($bind);
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        if (MDB2::isError($r)) {
            throw new C2_DBException($r->getUserInfo(), $r->getCode());
        }
        return $r;
    }
    
   public function beginTransaction()
   {
       if (!$this->_con->supports('transactions') || $this->_con->inTransaction()) {
           return false;
       }
       $this->_con->beginTransaction();
       return true;
   }
   
   public function commit()
   {
       if (!$this->_con->supports('transactions') || !$this->_con->inTransaction()) {
           return false;
       }
       $this->_con->commit();
       return true;
   }
   
   public function rollback()
   {
       if (!$this->_con->supports('transactions') || !$this->_con->inTransaction()) {
           return false;
       }
       $this->_con->rollback();
       return true;
   }
}