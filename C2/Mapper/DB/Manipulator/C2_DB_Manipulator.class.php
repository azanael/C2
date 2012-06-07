<?php

interface C2_DB_Manipulator
{
    // Status interface
    public function isInTransaction();
    public function getLastQuery();
    public function getLastBinds();
    
	// CRUD interface
    public function create($sql, array $bind);
    public function createMultiple($sql, array $bind);
    public function readOne($sql, array $bind = array());
    public function read($sql, array $bind = array(), $limit = null, $offset = 0);
    public function update($sql, array $bind);
    public function delete($sql, array $bind);
    
    // Transaction interface
    public function beginTransaction();
    public function commit();
    public function rollback();
}