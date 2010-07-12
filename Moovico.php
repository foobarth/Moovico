<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class Moovico
{
    const E_CORE_MISSING_PARAM      = 101;
    const E_CORE_INVALID_PARAM      = 102;
    const E_CORE_NO_CONFIG          = 103;
    const E_CORE_INVALID_URL        = 104;
    const E_CORE_NO_ROUTE           = 105;
    const E_CORE_NO_CONTROLLER      = 106;
    const E_CORE_NO_ACTION          = 107;
    const E_CORE_INVALID_RESPONSE   = 108;
    const E_CORE_INVALID_FORMAT     = 109;
    const E_CORE_NO_PLUGIN          = 110;
    const E_CORE_PLUGIN_HALT        = 111;

    const E_SESSION_NO_SESSION      = 201;
    const E_SESSION_NO_TOKEN        = 202;
    const E_SESSION_NOT_AUTHORIZED  = 203;

    const E_DB_CONNECT_FAILED       = 301;
    const E_DB_INVALID_SQL          = 302;
    const E_DB_EXECUTE_FAILED       = 303;
    const E_DB_BINDING_FAILED       = 304;
    const E_DB_UNDEFINED_COLUMN     = 305;
    const E_DB_DATA_NOT_AVAILABLE   = 306;
    const E_DB_INSUFFICIENT_DATA    = 307;

    const E_VIEW_NO_TEMPLATE        = 401;

    /**
     * TODO: description.
     * 
     * @var array
     */
    protected static $app_root;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected static $time_start;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected static $time_needed;

    /**
     *  
     */
    protected static $time_checkpoint;

    /**
     * TODO: description.
     * 
     * @var double
     */
    protected static $debug;

    /**
     *  
     */
    protected static $debug_stack;

    /**
     * TODO: description
     *
     * @var array
     */
    protected static $conf;

    /**
     * TODO: description
     * 
     * @var array
     */
    protected static $url;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected static $exception_handlers;

    /**
     * TODO: description.
     * 
     * @var resource
     */
    protected static $routes;

    /**
     * TODO: description.
     * 
     * @var resource
     */
    protected static $route;

    /**
     * TODO: description.
     * 
     * @var resource
     */
    protected static $response;

    /**
     *  
     */
    protected static $db;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    protected static $plugins;

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public static function Setup()
    {
        self::$time_start = microtime(true);
        self::$time_checkpoint = self::$time_start;
        self::$app_root = isset($_SERVER['MOOVICO_APP_ROOT']) ? $_SERVER['MOOVICO_APP_ROOT'] : '../';
        spl_autoload_register(array(__CLASS__, '__autoload'));
        set_exception_handler(array(__CLASS__, '__exceptionHandler'));
        self::startSession();
        self::RegisterExceptionHandler('MoovicoException', new MoovicoExceptionHandler());
        self::parseURL();
        self::LoadConf();
    }

    /**
     * LoadConf 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function LoadConf()
    {
        $file = realpath(self::$app_root.'Conf/'.self::$url['host'].'.ini');
        if (!$file)
        {
            throw new MoovicoException('Config file for host "'.self::$url['host'].'" not found', Moovico::E_CORE_NO_CONFIG);
        }

        self::$conf = parse_ini_file($file, true);

        self::setDebug((boolean)self::$conf['global']['debug']);

        $tz = isset(self::$conf['global']['timezone']) ? self::$conf['global']['timezone'] : 'Europe/Berlin';
        date_default_timezone_set($tz);

        self::$routes = array();
        foreach (self::$conf['routes'] as $name => $regex)
        {
            $default_format = isset(self::$conf['formats'][$name]) ? self::$conf['formats'][$name] : 'html';
            self::AddRoute(new MoovicoRoute($name, $regex, $default_format));
        }

        self::$plugins = array();
        if (!empty(self::$conf['plugins']))
        {
            foreach (self::$conf['plugins'] as $name => $plugin)
            {
                self::AddPlugin($name, new $plugin);
            }
        }
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public static function Run()
    {
        self::Fire('onStart');
        self::start();
        self::Fire('onRoute');
        self::routeRequest();
        self::Fire('onController');
        self::runController();
        self::Fire('onResponse');
        self::sendResponse();
        self::Fire('onFinish');
        self::finish();
    }

    /**
     * setDebug 
     * 
     * @param mixed $debug 
     * @static
     * @access protected
     * @return void
     */
    protected static function setDebug($debug = true)
    {
        self::$debug = $debug === true;
        if (self::$debug === true)
        {
            self::$debug_stack = array();
            ini_set('display_startup_errors', 1);
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        }
    }

    /**
     * Debug 
     * 
     * @param mixed $what 
     * @static
     * @access public
     * @return void
     */
    public static function Debug($what)
    {
        if (self::$debug === true)
        {
            $bt = debug_backtrace();
            $time_needed = self::GetTime(true);
            $str = $bt[1]['class']
                 . '::'
                 . $bt[1]['function']
                 . '('.@implode(', ', $bt[1]['args']).")" 
                 . " [".sprintf("%.2f", $time_needed * 1000).' msec]'
                 . "\n  ".str_replace("\n", "\n  ", print_r($what, true))
                 ;

            self::$debug_stack[] = $str;
        }
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function start()
    {
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function finish()
    {
        self::$time_needed = self::GetTime();
        self::Debug("Processing time: ".sprintf("%.5f", self::$time_needed)." sec");
    }

    /**
     * GetTime 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function GetTime($sinceCheckpoint = false)
    {
        $now = microtime(true);
        if ($sinceCheckpoint)
        {
            $time_needed = $now - self::$time_checkpoint;
            self::$time_checkpoint = $now;
        }
        else
        {
            $time_needed = $now - self::$time_start;
        }

        return $time_needed;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $type          
     * @param mixed $handler_class 
     * 
     * @return TODO
     */
    public static function RegisterExceptionHandler($type, MoovicoExceptionHandlerInterface $handler)
    {
        self::$exception_handlers[$type] = $handler;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $class 
     * 
     * @return TODO
     */
    public static function __autoload($class)
    {
        self::Debug("Loading class $class");
        $file = self::SanitizeFile($class);
        $path = self::GetClassPath($class);
        require($path.$file.'.php');
    }

    /**
     * GetClassPath 
     * 
     * @param mixed $class 
     * @static
     * @access public
     * @return void
     */
    public static function GetClassPath($class)
    {
        $path = self::$app_root.'Lib/'; // assume a locally available class in Lib
        if (strpos($class, 'Moovico') !== 0) // no Lib file
        {
            foreach (array('Model', 'Controller', 'Plugin') as $suffix)
            {
                if (substr($class, (strlen($suffix) * -1)) == $suffix)
                {
                    $path = self::$app_root.$suffix.'s/';
                    break;
                }
            }
        }
        else
        {
            $path = ''; // include a Moovico Class
        }

        return $path;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public static function GetAppRoot()
    {
        return self::$app_root;
    }

    /**
     * __exceptionHandler 
     * 
     * @param Exception $e 
     * @static
     * @access public
     * @return void
     */
    public static function __exceptionHandler(Exception $e)
    {
        $type = get_class($e);
        if (isset(self::$exception_handlers[$type]))
        {
            try
            {
                $result = self::$exception_handlers[$type]->Handle($e);
                self::$response = self::createResponse($result);
                self::sendResponse();
                return;
            }
            catch (Exception $e) 
            { 
                throw $e;
            }
        }

        // last exit
        throw $e;
    }

    /**
     * TODO: short description.
     * 
     * @param MoovicoRoute $route 
     * 
     * @return TODO
     */
    public static function AddRoute(MoovicoRoute $route)
    {
        self::$routes[$route->GetName()] = $route;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed         $name   
     * @param MoovicoPlugin $plugin 
     * 
     * @return TODO
     */
    public static function AddPlugin($name, MoovicoPluginInterface $plugin)
    {
        self::$plugins[$name] = $plugin;
    }

    /**
     * GetDB 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function GetDB()
    {
        if (!empty(self::$db))
        {
            return self::$db;
        }

        $type = self::$conf['database']['connector'];
        $connector = "Moovico{$type}Connector";
        self::$db = $connector::GetInstance(self::$conf['database']);

        return self::$db;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $name 
     * 
     * @return TODO
     */
    public static function GetPlugin($name)
    {
        if (empty(self::$plugins[$name]))
        {
            throw new MoovicoException('Requested plugin not registered', Moovico::E_CORE_NO_PLUGIN);
        }

        return self::$plugins[$name];
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function parseURL()
    {
        self::$url = parse_url(self::getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        if (empty(self::$url['host']) || empty(self::$url['path']))
        {
            throw new MoovicoException('Unparseable URL', Moovico::E_CORE_INVALID_URL);
        }
    }

    /**
     * startSession 
     * 
     * @static
     * @access protected
     * @return void
     */
    protected static function startSession()
    {
        session_start();
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function getScheme()
    {
        return strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false ? 'https' : 'http';
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function routeRequest()
    {
        foreach (self::$routes as $route)
        {
            if ($route->IsMatching(self::$url['path']))
            {
                self::$route = &$route;
                return;
            }
        }

        throw new MoovicoException('No suitable route found', Moovico::E_CORE_NO_ROUTE);
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function runController()
    {
        $class = self::getControllerClassName(self::$route->GetController());
        if (!class_exists($class))
        {
            throw new MoovicoException('Class not found: '.$class, Moovico::E_CORE_NO_CONTROLLER);
        }
        self::Debug("Loaded class $class");

        $controller = new $class();
        $action = self::getControllerMethod(self::$route->GetAction());

        if ($controller instanceof MoovicoRESTInterface)
        {
            $method = $_SERVER['REQUEST_METHOD'];
            $action = self::$route->GetAction() == MoovicoRoute::DEFAULT_ACTION ? $method : $action.'_'.$method;
            self::Debug("Using REST interface: $action");
        }

        if (!is_callable(array($controller, $action)))
        {
            throw new MoovicoException('Method not found: '.$action, Moovico::E_CORE_NO_ACTION);
        }

        $params = self::$route->GetParams();

        self::Debug('Calling '.$class.'::'.$action.'('.implode(', ', $params).')');
        self::Fire('onAction', $controller);
        self::$response = call_user_func_array(array($controller, $action), $params);
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    protected static function sendResponse()
    {
        if (self::$response instanceof MoovicoResponse || self::$response instanceof Exception) // autoconvert format
        {
            $response = self::createResponse(self::$response);
        }
        else
        {
            if (!(self::$response instanceof MoovicoResponseInterface))
            {
                throw new MoovicoException('Incompatible Response returned', Moovico::E_CORE_INVALID_RESPONSE);
            }

            $response = self::$response;
        }

        if (self::$debug === true)
        {
            $response->debug = self::$debug_stack;
        }

//        header('Content-Type: '.$response->GetContentType());
        echo $response;
    }

    /**
     * createResponse 
     * 
     * @param mixed $format 
     * @static
     * @access protected
     * @return void
     */
    protected static function createResponse($payload = null)
    {
        $format = empty(self::$route) ? 'debug' : self::$route->GetFormat();
        switch ($format)
        {
            case 'extjson':
                $response = MoovicoExtJSONResponse::Apply($payload);
                break;

            case 'json':
                $response = MoovicoJSONResponse::Apply($payload);
                break;

            case 'xml':
                $response = MoovicoXMLResponse::Apply($payload);
                break;

            case 'txt':
                $response = MoovicoPlainTextResponse::Apply($payload);
                break;

            case 'debug':
                $response = MoovicoDebugResponse::Apply($payload);
                break;

            case 'html':
                $response = MoovicoHTMLResponse::Apply($payload);
                break;

            default:
                throw new MoovicoException('Invalid format requested', Moovico::E_CORE_INVALID_FORMAT);
                break;
        }

        return $response;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $controller 
     * 
     * @return TODO
     */
    protected static function getControllerClassName($controller)
    {
        return ucfirst($controller).'Controller';
    }

    /**
     * TODO: short description.
     * 
     * @param array $action 
     * 
     * @return TODO
     */
    protected static function getControllerMethod($action)
    {
        return ucfirst($action);
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $event 
     * 
     * @return TODO
     */
    public static function Fire($event)
    {
        if (empty(self::$plugins)) return;

        $args = func_get_args();
        array_shift($args); // skip first arg (== $event)
        foreach (self::$plugins as $name => $plugin)
        {
            if (!call_user_func_array(array($plugin, $event), $args))
            {
                throw new MoovicoException('Plugin returned error, execution halted', Moovico::E_CORE_PLUGIN_HALT);
            }
        }
    }

    /**
     * GetRoute 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function GetRoute()
    {
        return self::$route;
    }

    /**
     * SanitizeFile 
     * 
     * @param mixed $file 
     * @static
     * @access public
     * @return void
     */
    public static function SanitizeFile($file)
    {
        return str_replace('..', '', $file);
    }
}

// self invoking if configured as fcgi handler
if (__FILE__ == $_SERVER['SCRIPT_FILENAME'])
{
    Moovico::Setup();
    Moovico::Run();
}

