<?php
require_once 'C2_DB_Manipulator.class.php';

class C2_PDO_Manipulator implements C2_DB_Manipulator
{
    private $_con;
    
    private $_lastQuery;
    private $_lastBinds;
    
    public function __construct(PDO $con)
    {
        $this->_con = $con;
    }
    
    // Status interface
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
    
    // CRUD interface
    public function create($sql, array $bind = array())
    {
        $s = $this->_con->prepare($sql);
        $r = $s->execute($bind);
        if ($r === false) {
            throw new C2_DBException($s->errorInfo());
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $this->_con->lastInsertId();
    }
    
    public function createMultiple($sql, array $bind = array())
    {
        $s = $this->_con->prepare($sql);
        foreach ($bind as &$b) {
            $r = $s->execute($b);
            if ($r === false) {
                throw new C2_DBException($s->errorInfo());
            }
            $insertIds[] = $this->_con->lastInsertId();
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $insertIds;
    }
    
    public function readOne($sql, array $bind = array())
    {
        $s = $this->_con->prepare($sql);
        $r = $s->execute($bind);
        if ($r === false) {
            throw new C2_DBException($s->errorInfo());
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $s->fetch(PDO::FETCH_ASSOC);
    }
    
    public function read($sql, array $bind = array(), $limit = null, $offset = 0)
    {
        $s = $this->_con->prepare($sql);
        if ($limit !== null) {
            $sql .= ' LIMIT :offset, :limit';
            $s->bindParam(':limit', $limit, PDO::PARAM_INT);
            $s->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        $r = $s->execute($bind);
        if ($r === false) {
            throw new C2_DBException($s->errorInfo());
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($sql, array $bind = array())
    {
        $s = $this->_con->prepare($sql);
        $r = $s->execute($bind);
        if ($r === false) {
            throw new C2_DBException($s->errorInfo());
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $s->rowCount();
    }
    
    public function delete($sql, array $bind = array())
    {
        $s = $this->_con->prepare($sql);
        $r = $s->execute($bind);
        if ($r === false) {
            throw new C2_DBException($s->errorInfo());
        }
        $this->_lastQuery = $sql;
        $this->_lastBinds = $bind;
        return $s->rowCount();
    }
    
    // Transaction interface
    public function beginTransaction()
    {
        $this->_con->beginTransaction();
    }
    
    public function commit()
    {
        $this->_con->commit();
    }
    
    public function rollback()
    {
        $this->_con->rollBack();
    }
}