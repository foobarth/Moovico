<?php

/**
 * MoovicoController 
 * 
 * @package 
 * @version 
 * @copyright (c) Kodweiss E-Business GmbH
 * @author Kodweiss E-Business GmbH 
 * @license 
 */
abstract class MoovicoController
{
    /**
     *  
     */
    const AUTH_TOKEN_KEY = '__AUTH_TOKEN__';

    /**
     *  
     */
    const PARAM_SPLIT_TOKEN = ',';

    /**
     * params 
     * 
     * @var mixed
     * @access protected
     */
    protected $params;

    /**
     * __construct 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function __construct()
    {
        $this->params = array_merge($_GET, $_POST);
    }

    /**
     * RequireArg 
     * 
     * @param mixed $arg 
     * @final
     * @access public
     * @return void
     */
    public final function RequireArg(&$arg, $type = 'string')
    {
        if (empty($arg))
        {   
            throw new MoovicoException('Required parameter '.$name.' missing.', Moovico::E_CORE_MISSING_PARAM);
        }

        settype($arg, $type);
    }

    /**
     * RequireParam 
     * 
     * @param mixed $name 
     * @param string $type 
     * @param mixed $default 
     * @param Array $whitelist 
     * @final
     * @access public
     * @return void
     */
    public final function RequireParam($name, $type = 'string', Array $whitelist = array())
    {
        if (!isset($this->params[$name]))
        {   
            throw new MoovicoException('Required parameter '.$name.' missing.', Moovico::E_CORE_MISSING_PARAM);
        }
        else
        {
            if (($pos = strpos($type, 'array:')) === 0)
            {
                $subtype = substr($type, 6);
                $this->params[$name] = explode(self::PARAM_SPLIT_TOKEN, $this->params[$name]);
                foreach ($this->params[$name] as &$v)
                {
                    settype($v, $subtype);
                }
            } 
            else
            {
                settype($this->params[$name], $type);
            }
        }

        if (func_num_args() == 3 && count($whitelist) && !in_array($this->params[$name], $whitelist, true))
        {   
            throw new MoovicoException('Invalid value or type at parameter '.$name, Moovico::E_CORE_INVALID_PARAM);
        }   

        return $this;
    }

    /**
     * OptionalParam 
     * 
     * @param mixed $name 
     * @param string $type 
     * @param mixed $default 
     * @param Array $whitelist 
     * @final
     * @access public
     * @return void
     */
    public final function OptionalParam($name, $type = 'string', $default = null, Array $whitelist = array())
    {
        if (func_num_args() >= 3 && empty($this->params[$name]))
        {   
            $this->params[$name] = $default;
        }

        if (!empty($this->params[$name]) || (isset($this->params[$name]) && $this->params[$name] === 0))
        {
            return $this->RequireParam($name, $type, $whitelist); 
        }

        return $this;
    }

    /**
     * GetParams 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function GetParams()
    {
        return $this->params;
    }

    /**
     * GetParam 
     * 
     * @param mixed $name 
     * @final
     * @access public
     * @return void
     */
    public final function GetParam($name)
    {
        return $this->params[$name];
    }

    /**
     * SetSessionToken 
     * 
     * @param mixed $token 
     * @final
     * @access public
     * @return void
     */
    public final function SetSessionToken($token)
    {
        $id = session_id();
        if (empty($id))
        {
          throw new MoovicoException('No session available', Moovico::E_SESSION_NO_SESSION);
        }

        $_SESSION[self::AUTH_TOKEN_KEY] = $token;
    }

    /**
     * GetSessionToken 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function GetSessionToken()
    {
        if (empty($_SESSION[self::AUTH_TOKEN_KEY]))
        {
          throw new MoovicoException('No token available', Moovico::E_SESSION_NO_TOKEN);
        }

        return $_SESSION[self::AUTH_TOKEN_KEY];
    }

    /**
     * RequireSessionToken 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function RequireSessionToken()
    {
        if (empty($_SESSION[self::AUTH_TOKEN_KEY]))
        {
            throw new MoovicoException('Not authorized', Moovico::E_SESSION_NOT_AUTHORIZED);
        }
    }

    /**
     * ClearSessionToken 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function ClearSessionToken()
    {
        $id = session_id();
        if (empty($id))
        {
          throw new MoovicoException('No session available', Moovico::E_SESSION_NO_SESSION);
        }

        unset($_SESSION[self::AUTH_TOKEN_KEY]);
    }
}
