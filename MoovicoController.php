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
     * @access public
     * @return void
     */
    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') 
        {
            $this->ProcessRawInput();
            return;
        }

        $this->params = array_merge($_GET, $_POST);
    }

    /**
     * ProcessRawInput 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function ProcessRawInput()
    {
        $stdin = file_get_contents('php://input');
        $json = json_decode($stdin);
        $put = is_null($json) == false ? (array)$json : array('PUT' => $stdin);
        $this->params = array_merge($put, $_GET, $_POST);

        return $this;
    }

    /**
     * RequireArg 
     * 
     * @param mixed $arg 
     * @final
     * @access public
     * @return void
     */
    public final function RequireArg(&$arg, $type = 'string', Array $whitelist = array())
    {
        if (empty($arg) && $arg !== 0)
        {   
            throw new MoovicoException('Required argument is empty.', Moovico::E_CORE_MISSING_PARAM);
        }
        else
        {
            if (($pos = strpos($type, 'array:')) === 0)
            {
                $subtype = substr($type, 6);

                if (is_object($arg))
                {
                    $arg = (array)$arg;
                } 
                else if (!is_array($arg))
                {
                    $arg = explode(self::PARAM_SPLIT_TOKEN, $arg);
                }

                foreach ($arg as &$v)
                {
                    $this->handleType($v, $subtype);
                }
            } 
            else
            {
                $this->handleType($arg, $type);
            }
        }

        if (func_num_args() == 3 && count($whitelist) && !in_array($arg, $whitelist, true))
        {   
            throw new MoovicoException('Invalid value or type in argument', Moovico::E_CORE_INVALID_PARAM);
        }   

        return $this;
    }

    /**
     * handleType 
     * 
     * @param mixed $arg 
     * @param mixed $type 
     * @access protected
     * @return void
     */
    protected function handleType(&$arg, $type) 
    {
        switch ($type)
        {
            case 'json':
                $arg = json_decode($arg);
                break;

            case 'date':
                $arg = $this->MangleDate($arg);
                break;

            default:
                settype($arg, $type);
        }
    }

    /**
     * RequireParam 
     * 
     * @param mixed $name 
     * @param string $type 
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

        return $this->RequireArg($this->params[$name], $type, $whitelist);
    }

    /**
     * OptionalArg 
     * 
     * @param mixed $arg 
     * @param string $type 
     * @param mixed $default 
     * @param Array $whitelist 
     * @final
     * @access public
     * @return void
     */
    public final function OptionalArg(&$arg, $type = 'string', $default = null, Array $whitelist = array())
    {
        if (func_num_args() >= 3 && empty($arg))
        {   
            $arg = $default;
        }

        if (!empty($arg) || $arg === 0)
        {
            return $this->RequireArg($arg, $type, $whitelist); 
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
     * MangleDate 
     * 
     * @param mixed $date 
     * @final
     * @access public
     * @return void
     */
    public final function MangleDate($date = null)
    {
        $outformat = 'Y-m-d H:i:s';
        if (empty($date))
        {
            return date($outformat);
        }

        $test_formats = array( // extend as needed
            $outformat, 'Y-m-d\TH:i:s', 'Y-m-d', 'd.m.Y'
        );
        foreach ($test_formats as $format)
        {
            $do = DateTime::createFromFormat($format, $date);
            if ($do) 
            {
                return $do->format($outformat);
            }
        }

        throw new MoovicoException('Invalid date - cannot parse: '.$date, Moovico::E_CORE_INVALID_PARAM);
    }

    /**
     * GetParams 
     * 
     * @final
     * @access public
     * @return void
     */
    public final function GetParams($selection = null)
    {
        if (empty($selection)) 
        {
            return $this->params;
        }

        if (!is_array($selection))
        {
            $selection = func_get_args();
        }

        $params = array();
        foreach ($selection as $k)
        {
            $params[$k] = $this->params[$k];
        }

        return $params;
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
