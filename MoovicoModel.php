<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
abstract class MoovicoModel
{
    /**
     * columns
     *
     * @var array
     */
    private static $columns = array();

    /**
     * db_id
     *
     * @var string
     */
    private $db_id = 'default';

    /**
     * order_by 
     * 
     * @var mixed
     * @access private
     */
    private $order_by;

    /**
     * start 
     * 
     * @var mixed
     * @access private
     */
    private $start;

    /**
     * maxrows 
     * 
     * @var mixed
     * @access private
     */
    private $maxrows;

    /**
     * glue 
     * 
     * @var string
     * @access private
     */
    private $glue = 'AND';

    /**
     * __construct 
     * 
     * @param mixed $pk_value 
     * @access public
     * @return void
     */
    public function __construct($pk_value = null)
    {
        if (!empty($pk_value))
        {
            $this->Load($pk_value);
        }
    }

    /**
     * __set 
     * 
     * @param mixed $what 
     * @param mixed $value 
     * @access public
     * @return void
     */
    public function __set($what, $value)
    {
        if (!in_array($what, $this->doGetColumns()))
        {
            throw new MoovicoException('Cannot set undefined member '.get_class($this).'::'.$what, Moovico::E_DB_UNDEFINED_COLUMN);
        }
        
        $this->$what = $value;
    }

    /**
     * __get 
     * 
     * @param mixed $what 
     * @access public
     * @return void
     */
    public function __get($what)
    {
        return $this->$what; // will not be used if someone iterates this object
    }

    /**
     * Init 
     * 
     * @param Array $data 
     * @access public
     * @return void
     */
    public function Init($data)
    {
        if (is_array($data))
        {
            $data = (object)$data;
        }

        foreach ($this->doGetColumns() as $prop)
        {
            if (isset($data->{$prop}))
            {
                $this->{$prop} = $data->{$prop};
            }
        }

        return $this;
    }

    /**
     * Diff 
     * 
     * @param mixed $data 
     * @access public
     * @return void
     */
    public function Diff($data) 
    {
        $diff = array();

        if (is_array($data))
        {
            $data = (object)$data;
        }

        foreach ($this->doGetColumns() as $prop)
        {
            if (isset($data->{$prop}) && $this->{$prop} != $data->{$prop})
            {
                $diff[$prop] = array($this->{$prop}, $data->{$prop});
            }
        }

        return $diff;
    }

    /**
     * OrderBy 
     * 
     * @param mixed $column 
     * @param mixed $direction 
     * @access public
     * @return void
     */
    public function OrderBy($column, $direction = null)
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

        return $this;
    }

    /**
     * Limit 
     * 
     * @param mixed $start 
     * @param mixed $maxrows 
     * @access public
     * @return void
     */
    public function Limit($start, $maxrows)
    {
        $this->start = (int)$start;
        $this->maxrows = (int)$maxrows;

        return $this;
    }

    /**
     * Glue 
     * 
     * @param mixed $glue 
     * @access public
     * @return void
     */
    public function Glue($glue)
    {
        $this->glue = $glue;

        return $this;
    }

    /**
     * Load 
     * 
     * @param mixed $pk_value 
     * @access public
     * @return void
     */
    public function Load($pk_value)
    {
        $where = is_array($pk_value) ? $pk_value : array(static::PK => $pk_value);
        $result = $this->Read($where);
        $this->Init($result[0]);
    }

    /**
     * Reload 
     * 
     * @access public
     * @return void
     */
    public function Reload()
    {
        $this->Load($this->{static::PK});
    }

    /**
     * Create 
     * 
     * @param Array $what 
     * @access public
     * @return void
     */
    public function Create(Array $what = array(), $onDuplicateKeyUpdate = false)
    {
        $table = static::TABLE;
        $use_this = empty($what);
        if ($use_this)
        {
            foreach ($this->doGetColumns() as $prop)
            {
                if (!is_null($this->{$prop}))
                {
                    $what[$prop] = $this->{$prop};
                }
            }
        }

        $db = $this->getDB();
        $result = $db->Insert($what)
                     ->Into($table)
                     ->OnDuplicateKeyUpdate($onDuplicateKeyUpdate)
                     ->Execute()
                     ;

        if ($result && $use_this)
        {
            $this->{static::PK} = $result;
        }

        return $result;
    }

    /**
     * Save 
     *
     * Convenient wrapper to store THIS object in the db,
     * automatically performing an update if a duplicate key is found
     * 
     * @access public
     * @return void
     */
    public function Save() {
        return $this->Create(array(), true);
    }

    /**
     * Read 
     * 
     * @param Array $where 
     * @access public
     * @return void
     */
    public function Read($bindings_or_condition = array(), $real_bindings = null)
    {
        $columns = $this->doGetColumns();
        $table = static::TABLE;

        $db = $this->getDB();
        $result = $db->Select($columns)
                     ->From($table)
                     ->Glue($this->glue)
                     ->Where($bindings_or_condition, $real_bindings)
                     ->OrderBy($this->order_by)
                     ->Limit($this->start, $this->maxrows)
                     ->ReturnAs(get_class($this))
                     ->Execute()
                     ;

        if (empty($result))
        {
            throw new MoovicoException('Requested data not available', Moovico::E_DB_DATA_NOT_AVAILABLE);
        }

        $this->order_by = array();
        $this->start = null;
        $this->maxrows = null;
        $this->glue = 'AND';

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
        $db = $this->getDB();
        return $db->TotalRows();
    }

    /**
     * Update 
     * 
     * @param Array $what 
     * @param mixed $pk_value 
     * @access public
     * @return void
     */
    public function Update(Array $what = array(), $pk_value = null)
    {
        $table = static::TABLE;
        $where = array(static::PK => (!empty($pk_value) ? $pk_value : $this->{static::PK}));
        $use_this = empty($what);
        if ($use_this)
        {
            foreach ($this->doGetColumns() as $prop)
            {
                if (!is_null($this->{$prop}) && $prop != static::PK)
                {
                    $what[$prop] = $this->{$prop};
                }
            }
        }

        $db = $this->getDB();
        $result = $db->Update($what)
                     ->Table($table)
                     ->Glue($this->glue)
                     ->Where($where)
                     ->Execute()
                     ;

        return $result;
    }

    /**
     * Delete 
     * 
     * @param Array $where 
     * @access public
     * @return void
     */
    public function Delete(Array $where = array())
    {
        $table = static::TABLE;
        $use_this = empty($where);
        if ($use_this)
        {
            $where[static::PK] = $this->{static::PK};
        }

        $db = $this->getDB();
        $result = $db->Delete()
                     ->Table($table)
                     ->Glue($this->glue)
                     ->Where($where)
                     ->Execute()
                     ;

        return $result;
    }

    /**
     * UseDB
     *
     * @param mixed $db_id
     */
    public function UseDB($db_id) {
        $this->db_id = $db_id;

        return $this;
    }

    /**
     * GetColumns 
     * 
     * @access public
     * @return void
     */
    public function GetColumns($asString = false)
    {
        $cols = $this->doGetColumns(ReflectionProperty::IS_PUBLIC);
        if ($asString) 
        {
            $cols = implode(', ', $cols);
        }

        return $cols;
    }

    /**
     * getDB
     *
     */
    protected final function getDB() {
        $db = Moovico::GetDB($this->db_id);

        return $db;
    }

    /**
     * doGetColumns 
     * 
     * @access protected
     * @return void
     */
    protected function doGetColumns($type = 0)
    {
        $type = empty($type) ? ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED : $type;
        $key = get_class($this).$type;
        if (empty(self::$columns[$key]))
        {
            $reflect = new ReflectionClass($this);
            $props = $reflect->getProperties($type);
            self::$columns[$key] = array();
            foreach ($props as $prop)
            {
                if (!$prop->isStatic())
                {
                    self::$columns[$key][] = $prop->getName();
                }
            }
        }

        return self::$columns[$key];
    }

    /**
     * toCSV 
     * 
     * @param mixed $include_headers 
     * @access public
     * @return void
     */
    public function toCSV($include_headers = false, $e = '"', $s = ';', $t = "\n", $columns = array())
    {
        $cols = !empty($columns) ? $columns : $this->GetColumns();
        $str = MoovicoCSVGenerator::rowFromObject($this, $include_headers, $e, $s, $t, $cols);

        return $str;
    }
}
