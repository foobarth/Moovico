<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoRoute
{
    const DEFAULT_CONTROLLER = 'index';
    const DEFAULT_ACTION = 'index';
    
    /**
     * name 
     * 
     * @var mixed
     * @access protected
     */
    protected $name;

    /**
     * TODO: description.
     * 
     * @var resource
     */
    protected $regex;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected $controller;

    /**
     * TODO: description.
     * 
     * @var array
     */
    protected $action;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected $args;

    /**
     * format 
     * 
     * @var mixed
     * @access protected
     */
    protected $format;

    /**
     * default_format 
     * 
     * @var mixed
     * @access protected
     */
    protected $default_format;

    /**
     * __construct 
     * 
     * @param mixed $regex 
     * @access public
     * @return void
     */
    public function __construct($name, $regex, $default_format = 'html')
    {
        $this->name = $name;
        $this->regex = $regex;
        $this->default_format = $default_format;
    }

    /**
     * TODO: short description.
     * 
     * @return boolean
     */
    public function IsMatching($url)
    {
        if (!preg_match($this->regex, $url, $m))
        {
            return false;
        }

        $this->controller = self::DEFAULT_CONTROLLER;
        $this->action = self::DEFAULT_ACTION;

        unset($m[0]);
        $i = 0;
        $special = array('controller', 'action', 'format');
        foreach ($m as $k => $v)
        {
            if (is_numeric($k)) continue;

            if (in_array($k, $special))
            {
                $this->{$k} = $v;
                unset($m[$k]);
            }

            if (isset($m[$i+1]) && $m[$i+1] == $v)
            {
                unset($m[$i+1]);
            }

            $i++;
        }

        $this->args = !empty($m) ? $m : array();

        return true;
    }

    /**
     * GetName 
     * 
     * @access public
     * @return void
     */
    public function GetName()
    {
        return $this->name;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function GetController()
    {
        return $this->controller;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function GetAction()
    {
        return $this->action;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function GetArgs()
    {
        return $this->args;
    }

    /**
     * GetFormat 
     * 
     * @access public
     * @return void
     */
    public function GetFormat()
    {
        return empty($this->format) ? $this->default_format : $this->format;
    }
}
