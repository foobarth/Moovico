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
     *  
     */
    private static $columns = array();

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
    public final function __construct($pk_value = null)
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
     * @final
     * @access public
     * @return void
     */
    public final function __set($what, $value)
    {
        if (!in_array($what, $this->getColumns()))
        {
            throw new MoovicoException('Cannot set undefined member '.get_class($this).'::'.$what, Moovico::E_DB_UNDEFINED_COLUMN);
        }
        
        $this->$what = $value;
    }

    /**
     * __get 
     * 
     * @param mixed $what 
     * @final
     * @access public
     * @return void
     */
    public final function __get($what)
    {
        return $this->$what; // will not be used if someone iterates this object
    }

    /**
     * Init 
     * 
     * @param Array $data 
     * @final
     * @access public
     * @return void
     */
    public final function Init($data)
    {
        if (!is_array($data))
        {
            $data = (array)$data;
        }

        foreach ($this->getColumns() as $prop)
        {
            if (isset($data[$prop]))
            {
                $this->{$prop} = $data[$prop];
            }
        }
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
     * Load 
     * 
     * @param mixed $pk_value 
     * @access public
     * @return void
     */
    public final function Load($pk_value)
    {
        $where = is_array($pk_value) ? $pk_value : array(static::PK => $pk_value);
        $result = $this->Read($where);
        $this->Init($result[0]);
    }

    /**
     * Reload 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function Reload()
    {
        $this->Load($this->{static::PK});
    }

    /**
     * Create 
     * 
     * @param Array $what 
     * @final
     * @access public
     * @return void
     */
    public final function Create(Array $what = array())
    {
        $table = static::TABLE;
        $use_this = empty($what);
        if ($use_this)
        {
            foreach ($this->getColumns() as $prop)
            {
                if (!is_null($this->{$prop}))
                {
                    $what[$prop] = $this->{$prop};
                }
            }
        }

        $db = Moovico::GetDB();
        $result = $db->Insert($what)
                     ->Into($table)
                     ->Execute()
                     ;

        if ($result && $use_this)
        {
            $this->{static::PK} = $result;
        }

        return $result;
    }

    /**
     * Read 
     * 
     * @param Array $where 
     * @final
     * @access public
     * @return void
     */
    public final function Read(Array $where = array())
    {
        $columns = $this->getColumns();
        $table = static::TABLE;

        $db = Moovico::GetDB();
        $result = $db->Select($columns)
                     ->From($table)
                     ->Glue($this->glue)
                     ->Where($where)
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
     * @final
     * @access public
     * @return void
     */
    public final function TotalRows()
    {
        $db = Moovico::GetDB();
        return $db->TotalRows();
    }

    /**
     * Update 
     * 
     * @param Array $what 
     * @param mixed $pk_value 
     * @final
     * @access public
     * @return void
     */
    public final function Update(Array $what = array(), $pk_value = null)
    {
        $table = static::TABLE;
        $where = array(static::PK => (!empty($pk_value) ? $pk_value : $this->{static::PK}));
        $use_this = empty($what);
        if ($use_this)
        {
            foreach ($this->getColumns() as $prop)
            {
                if (!is_null($this->{$prop}) && $prop != static::PK)
                {
                    $what[$prop] = $this->{$prop};
                }
            }
        }

        $db = Moovico::GetDB();
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
     * @final
     * @access public
     * @return void
     */
    public final function Delete(Array $where = array())
    {
        $table = static::TABLE;
        $use_this = empty($where);
        if ($use_this)
        {
            $where[static::PK] = $this->{static::PK};
        }

        $db = Moovico::GetDB();
        $result = $db->Delete()
                     ->Table($table)
                     ->Glue($this->glue)
                     ->Where($where)
                     ->Execute()
                     ;

        return $result;
    }

    /**
     * getColumns 
     * 
     * @final
     * @access protected
     * @return void
     */
    protected final function getColumns($type = 0)
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
     * @final
     * @access public
     * @return void
     */
    public final function toCSV($include_headers = false)
    {
        $e = '"';
        $s = ';';
        $t = "\n";
        $str = '';

        $cols = $this->getColumns(ReflectionProperty::IS_PUBLIC);
        if ($include_headers == true)
        {
            $vals = array();
            foreach ($cols as $prop)
            {
                $vals[] = strtoupper(addcslashes($prop, $e));
            }

            $str.= $e.implode($e.$s.$e, $vals).$e.$t;
        }

        $vals = array();
        foreach ($cols as $prop)
        {
            $vals[] = addcslashes($this->{$prop}, $e);
        }

        $str.= $e.implode($e.$s.$e, $vals).$e.$t;

        return $str;
    }
}
