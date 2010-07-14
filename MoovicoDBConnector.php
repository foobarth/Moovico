<?php

/**
 * MoovicoDBConnector 
 * 
 * @abstract
 * @package 
 * @version $id$
 * @copyright
 * @author Ralf Barth
 * @license © 2010
 */
abstract class MoovicoDBConnector
{
    const SQL_TYPE_UNKNOWN = 0;
    const SQL_TYPE_SELECT = 1;
    const SQL_TYPE_INSERT = 2;
    const SQL_TYPE_UPDATE = 3;
    const SQL_TYPE_DELETE = 4;

    /**
     *  
     */
    protected static $instance;

    /**
     * type 
     * 
     * @var mixed
     * @access protected
     */
    protected $type;

    /**
     * return_obj 
     * 
     * @var mixed
     * @access protected
     */
    protected $return_obj;

    /**
     * table 
     * 
     * @var mixed
     * @access protected
     */
    protected $table;

    /**
     * bindings 
     * 
     * @var mixed
     * @access protected
     */
    protected $bindings;

    /**
     * custom_condition 
     * 
     * @var mixed
     * @access protected
     */
    protected $custom_condition;

    /**
     * data 
     * 
     * @var mixed
     * @access protected
     */
    protected $data;

    /**
     * columns 
     * 
     * @var mixed
     * @access protected
     */
    protected $columns;

    /**
     * order_by 
     * 
     * @var mixed
     * @access protected
     */
    protected $order_by;

    /**
     * start 
     * 
     * @var mixed
     * @access protected
     */
    protected $start;

    /**
     * maxrows 
     * 
     * @var mixed
     * @access protected
     */
    protected $maxrows;

    /**
     * glue 
     * 
     * @var mixed
     * @access protected
     */
    protected $glue;

    /**
     * __construct 
     * 
     * @access protected
     * @return void
     */
    protected final function __construct(Array $conf)
    {
        $this->Cleanup();
        $this->Connect($conf);
    }

    /**
     * Cleanup 
     * 
     * @final
     * @access protected
     * @return void
     */
    protected final function Cleanup()
    {
        $this->type = self::SQL_TYPE_UNKNOWN;
        $this->return_obj = null;
        $this->table = '';
        $this->data = array();
        $this->bindings = array();
        $this->columns = array();
        $this->order_by = array();
        $this->start = 0;
        $this->maxrows = 0;
        $this->glue = 'AND';
    }

    /**
     * GetInstance 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public final static function GetInstance(Array $conf = null)
    {
        if (empty(self::$instance))
        {
            $class = get_called_class();
            self::$instance = new $class($conf);
        }

        return self::$instance;
    }

    /**
     * HasInstance 
     * 
     * @static
     * @final
     * @access public
     * @return void
     */
    public final static function HasInstance()
    {
        return !empty(self::$instance);
    }

    /**
     * TODO: short description.
     * 
     * @param string $sql 
     * 
     * @return TODO
     */
    public final static function DetectType($sql)
    {
        switch (strtoupper(substr($sql, 0, 6)))
        {
            case 'SELECT': return self::SQL_TYPE_SELECT;
            case 'INSERT': return self::SQL_TYPE_INSERT;
            case 'UPDATE': return self::SQL_TYPE_UPDATE;
            case 'DELETE': return self::SQL_TYPE_DELETE;
            default: return self::SQL_TYPE_UNKNOWN;
        }
    }

    /**
     * SetType 
     * 
     * @param mixed $type 
     * @final
     * @access public
     * @return void
     */
    public final function ReturnAs($type)
    {
        if (!is_object($type))
        {
            $this->return_obj = new $type;
        }
        else
        {
            $this->return_obj = clone $type;
        }

        return $this;
    }

    /**
     * Connect 
     * 
     * @param Array $conf 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function Connect(Array $conf);

    /**
     * Query 
     * 
     * @param mixed $sql 
     * @param Array $bindings 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function Query($sql);

    /**
     * buildQuery 
     * 
     * @abstract
     * @access protected
     * @return void
     */
    abstract protected function buildQuery();

    /**
     * TotalRows 
     * 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function TotalRows();

    /**
     * Execute 
     * 
     * @access public
     * @return void
     */
    public final function Execute()
    {
        $sql = $this->buildQuery();
        return $this->Query($sql, $this->data + $this->bindings);
    }

    /**
     * Select 
     * 
     * @param Array $columns 
     * @access public
     * @return void
     */
    public final function Select(Array $columns)
    {
        $this->type = self::SQL_TYPE_SELECT;
        $this->columns = $columns;

        return $this;
    }

    /**
     * Insert 
     * 
     * @param Array $what 
     * @final
     * @access public
     * @return void
     */
    public final function Insert(Array $data)
    {
        $this->type = self::SQL_TYPE_INSERT;
        $this->data = $this->expandParams($data);

        return $this;
    }

    /**
     * Update 
     * 
     * @param Array $data 
     * @final
     * @access public
     * @return void
     */
    public final function Update(Array $data)
    {
        $this->type = self::SQL_TYPE_UPDATE;
        $this->data = $this->expandParams($data);

        return $this;
    }

    /**
     * Delete 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function Delete()
    {
        $this->type = self::SQL_TYPE_DELETE;

        return $this;
    }

    /**
     * From 
     * 
     * @param mixed $table 
     * @access public
     * @return void
     */
    public final function From($table)
    {
        return $this->Table($table);
    }

    /**
     * Into 
     * 
     * @param mixed $table 
     * @final
     * @access public
     * @return void
     */
    public final function Into($table)
    {
        return $this->Table($table);
    }

    /**
     * Table 
     * 
     * @param mixed $table 
     * @final
     * @access public
     * @return void
     */
    public final function Table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Where 
     * 
     * @param Array $bindings 
     * @access public
     * @return void
     */
    public final function Where($bindings_or_condition, $real_bindings = null)
    {
        if (is_array($bindings_or_condition))
        {
            $real_bindings = $bindings_or_condition;
            $condition = '';
        }
        else
        {
            $condition = $bindings_or_condition;
        }

        $this->bindings = $this->expandParams($real_bindings);
        $this->custom_condition = $condition;

        return $this;
    }

    /**
     * Glue 
     * 
     * @param mixed $glue 
     * @final
     * @access public
     * @return void
     */
    public final function Glue($glue)
    {
        $this->glue = $glue;

        return $this;
    }

    /**
     * OrderBy 
     * 
     * @param mixed $column 
     * @param mixed $direction 
     * @final
     * @access public
     * @return void
     */
    public final function OrderBy($column, $direction = null)
    {
        if (!empty($column)) // in case the base model provides an empty array
        {
            if (is_array($column))
            {
                foreach ($column as $k => $v)
                {
                    $this->order_by[$k] = $v == 'DESC' ? 'DESC' : 'ASC';
                }
            }
            else
            {
                $this->order_by[$column] = $direction == 'DESC' ? 'DESC' : 'ASC';
            }
        }

        return $this;
    }

    /**
     * Limit 
     * 
     * @param mixed $start 
     * @param mixed $maxrows 
     * @final
     * @access public
     * @return void
     */
    public final function Limit($start, $maxrows)
    {
        $this->start = (int)$start;
        $this->maxrows = (int)$maxrows;

        return $this;
    }

    /**
     * expandParams
     * 
     * @param mixed $p 
     * @access protected
     * @return void
     */
    protected function expandParams(Array $p)
    {
        $expanded = array();
        foreach ($p as $k => $v)
        {
            $expanded[] = array($k, $v);
        }

        return $expanded;
    }

    /**
     * getBindingColumns 
     * 
     * @param Array $data 
     * @access protected
     * @return void
     */
    protected function getBindingColumns(Array $data, $idx = 0)
    {
        $tmp = array();
        foreach ($data as $v)
            $tmp[] = $v[$idx];

        return $tmp;
    }

    /**
     * getBindingValues 
     * 
     * @param Array $data 
     * @access protected
     * @return void
     */
    protected function getBindingValues(Array $data)
    {
        return $this->getBindingColumns($data, 1);
    }
}
