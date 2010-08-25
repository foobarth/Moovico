<?php

/**
 * MoovicoMySQLiConnector 
 * 
 * @uses MoovicoDBConnector
 * @package 
 * @version $id$
 */
class MoovicoMySQLiConnector extends MoovicoDBConnector
{
    /**
     * db 
     * 
     * @var mixed
     * @access protected
     */
    protected $db;

    /**
     * calc_rows 
     * 
     * @var mixed
     * @access protected
     */
    protected $calc_rows = true; // test

    /**
     * Connect 
     * 
     * @param Array $conf 
     * @access public
     * @return void
     */
    public function Connect(Array $conf)
    {
        if (!empty($this->db))
        {
            return;
        }

        $db = new mysqli($conf['host'], $conf['user'], $conf['password'], $conf['name']);
        if ($db->connect_error)
        {
            throw new MoovicoException('Database connection failed: '.$db->connect_error, Moovico::E_DB_CONNECT_FAILED);
        }

        $this->db = $db;

        $this->Query('SET sql_mode = TRADITIONAL');

        Moovico::Debug("Connected to {$conf['host']}/{$conf['name']}");

        return $this;
    }

    /**
     * Query 
     * 
     * @param mixed $sql 
     * @param Array $bindings 
     * @access public
     * @return void
     */
    public function Query($sql)
    {
        Moovico::Debug($sql);

        $stmt = $this->db->prepare($sql);
        if (empty($stmt))
        {
            throw new MoovicoException('Statement preparation failed: '.$this->db->error, Moovico::E_DB_INVALID_SQL);
        }

        if (empty($this->type))
        {
            $this->type = self::DetectType($sql);
        }

        if ($stmt->param_count)
        {
            $vars = func_get_args();
            array_shift($vars); // skip first arg (== $sql)

            if (is_array($vars[0]))
            {
                $vars = $vars[0];
            }

            if (isset($vars[0]) && is_array($vars[0]))
            {
                $vars = $this->getBindingValues($vars);
            }

            $this->cleanVars($vars);
            $this->bindVars($stmt, $vars);
        }

        if (!$stmt->execute())
        {
            throw new MoovicoException('Statement execution failed: '.$stmt->error, Moovico::E_DB_EXECUTE_FAILED);
        }

        switch ($this->type)
        {
            case self::SQL_TYPE_SELECT:
                $result = $this->handleSelect($stmt);
                break;

            case self::SQL_TYPE_INSERT:
                $result = $this->handleInsert($stmt);
                break;

            case self::SQL_TYPE_UPDATE:
                $result = $this->handleUpdate($stmt);
                break;

            case self::SQL_TYPE_DELETE:
                $result = $this->handleDelete($stmt);
                break;

            case self::SQL_TYPE_UNKNOWN:
            default:
                $result = $this->handleUnknown($stmt);
                break;
        }

        $stmt->close();
        $this->Cleanup();

        Moovico::Debug($result);

        return $result;
    }   

    /**
     * TotalRows 
     * 
     * @access public
     * @return void
     */
    public function TotalRows()
    {
        $this->type = self::SQL_TYPE_SELECT;
        $res = $this->Query('SELECT FOUND_ROWS() TOTAL');

        return $res[0]->TOTAL;
    }

    /**
     * BeginTransaction 
     * 
     * @access public
     * @return void
     */
    public function BeginTransaction()
    {
        $this->db->autocommit(false);
    }

    /**
     * Commit 
     * 
     * @access public
     * @return void
     */
    public function Commit()
    {
        $this->db->commit();
        $this->db->autocommit(true);
    }

    /**
     * Rollback 
     * 
     * @access public
     * @return void
     */
    public function Rollback()
    {
        $this->db->rollback();
        $this->db->autocommit(true);
    }

    /**
     * buildQuery 
     * 
     * @access protected
     * @return void
     */
    protected function buildQuery()
    {
        $use_where = false;
        $use_order = false;
        $use_limit = false;
        switch ($this->type)
        {
            case self::SQL_TYPE_SELECT:
                $use_where = $use_order = $use_limit = true;
                $extra = !empty($this->maxrows) && $this->calc_rows === true ? 'SQL_CALC_FOUND_ROWS ' : '';
                $sql = 'SELECT '.$extra.implode(', ', $this->columns).' FROM '.$this->table;
                break;

            case self::SQL_TYPE_INSERT:
                $columns = $this->getBindingColumns($this->data);
                $sql = 'INSERT INTO '.$this->table.' ('.implode(', ', $columns).') VALUES (';
                $tmp = array();
                for ($i = 0; $i < count($columns); $i++)
                {
                    $tmp[] = '?';
                }
                $sql.= implode(', ', $tmp);
                $sql.= ')';
                break;

            case self::SQL_TYPE_UPDATE:
                $use_where = true;
                $columns = $this->getBindingColumns($this->data);
                $sql = 'UPDATE '.$this->table.' SET ';
                $tmp = array();
                foreach ($columns as $col)
                {
                    $tmp[] = $col.' = ?';
                }
                $sql.= implode(', ', $tmp);
                break;

            case self::SQL_TYPE_DELETE:
                $use_where = true;
                $sql = 'DELETE FROM '.$this->table;
                break;
        }

        if ($use_where && !empty($this->bindings))
        {
            if (empty($this->custom_condition))
            {
                $tmp = array();
                foreach ($this->bindings as $idx => $binding)
                {
                    list($col, $val) = $binding;

                    $operator = '=';
                    if (($pos = strpos($col, ' ')) !== false) 
                    {
                        list($real_col, $operator) = explode(' ', $col);
                        $this->bindings[$idx] = array($real_col, $val);
                        $col = $real_col;
                    }

                    if (!is_array($val)) 
                    {
                        $val = array($val);
                    }
                    foreach ((array)$val as $val2)
                    {
                        if (is_null($val2))
                        {
                            $str = $col.' IS '.($operator == 'NOT' ? 'NOT ' : '').'NULL';
                            unset($this->bindings[$idx]); // remove the null value from bindings
                        }
                        else
                        {
                            $str = $col.' '.$operator.' ?';
                        }

                        $tmp[] = $str;
                    }
                }

                $condition = implode(' '.$this->glue.' ', $tmp);
            }
            else
            {
                $condition = $this->custom_condition;
            }

            $sql.= ' WHERE '.$condition;
        }

        if ($use_order && !empty($this->order_by))
        {
            $tmp = array();
            foreach ($this->order_by as $col => $dir)
            {
                $tmp[] = $col.' '.($dir == 'DESC' ? 'DESC' : 'ASC');
            }

            $sql.= ' ORDER BY '.implode(', ', $tmp);
        }

        if ($use_limit && !empty($this->maxrows))
        {
            $sql.= ' LIMIT '.(int)$this->start.', '.(int)$this->maxrows;
        }

        return $sql;
    }

    /**
     * TODO: short description.
     * 
     * @param string $stmt 
     * @param mixed  $vars 
     * 
     * @return TODO
     */
    protected function bindVars(&$stmt, Array $vars)
    {
        $args = array('');
        foreach ($vars as &$v)
        {
            if (is_array($v))
            {
                foreach ($v as &$v2)
                {
                    if (!is_null($v2))
                    {
                        $args[0].= $this->getDataType($v2);
                        $args[] = &$v2;
                    }
                }
            } 
            else 
            {
                $args[0].= $this->getDataType($v);
                $args[] = &$v;
            } 
        }

        Moovico::Debug($args);

        if (!call_user_func_array(array($stmt, 'bind_param'), $args))
        {
            throw new MoovicoException('Parameter binding failed: '.$stmt->error, Moovico::E_DB_BINDING_FAILED);
        }
    }

    /**
     * getDataType 
     * 
     * @param mixed $v 
     * @access protected
     * @return void
     */
    protected function getDataType($v) 
    {
        if (!is_numeric($v)) 
        {
            return 's';
        }

        return preg_match('/^\d+$/', $v) ? 'i' : 'd';
    }

    /**
     * cleanVars 
     * 
     * @param mixed $vars 
     * @access protected
     * @return void
     */
    protected function cleanVars(&$vars)
    {
        switch ($this->type)
        {
            case self::SQL_TYPE_INSERT:
            case self::SQL_TYPE_UPDATE:
                break;

            default:
                return;
        }

        foreach ($vars as &$v)
        {
            if ($v === '') // we don't want empty strings, make'em NULL
            {
                $v = null;
            }
        }
    }

    /**
     * TODO: short description.
     * 
     * @param mysqli_stmt $stmt 
     * 
     * @return TODO
     */
    protected function handleSelect(mysqli_stmt $stmt)
    {
        if ($metadata = $stmt->result_metadata())
        {
            $fields = $metadata->fetch_fields();
            $metadata->free_result();

            $args = array();
            $data = array();
            foreach ($fields as $col) 
            {   
                $data[$col->name] = null;
                $args[] = &$data[$col->name];
            }

            call_user_func_array(array($stmt, 'bind_result'), $args);
        }

        if (empty($this->return_obj))
        {
            $this->ReturnAs('stdClass');
        }

        $result = array();
        while ($stmt->fetch())
        {
            $obj = clone $this->return_obj;
            foreach ($data as $k => $v)
            {
                $obj->$k = $v;
            }

            $result[] = $obj;
        }

        $this->ReturnAs('stdClass');

        return $result;
    }

    /**
     * TODO: short description.
     * 
     * @param mysqli_stmt $stmt 
     * 
     * @return TODO
     */
    protected function handleInsert(mysqli_stmt $stmt)
    {
        return $stmt->insert_id;
    }

    /**
     * TODO: short description.
     * 
     * @param mysqli_stmt $stmt 
     * 
     * @return TODO
     */
    protected function handleUpdate(mysqli_stmt $stmt)
    {
        return $stmt->affected_rows;
    }

    /**
     * TODO: short description.
     * 
     * @param mysqli_stmt $stmt 
     * 
     * @return TODO
     */
    protected function handleDelete(mysqli_stmt $stmt)
    {
        return $stmt->affected_rows;
    }

    /**
     * handleUnknown 
     * 
     * @param mysqli_stmt $stmt 
     * @access protected
     * @return void
     */
    protected function handleUnknown(mysqli_stmt $stmt)
    {
        // left blank
    }
}
