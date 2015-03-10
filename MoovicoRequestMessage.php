<?php

/**
 * MoovicoRequestMessage 
 * 
 * @package 
 * @version 
 * @copyright (c) Ralf Barth
 * @author Ralf Barth
 * @license 
 */
class MoovicoRequestMessage
{
    /**
     * controller 
     * 
     * @var string
     * @access protected
     */
    protected $controller = '';

    /**
     * action 
     * 
     * @var mixed
     * @access protected
     */
    protected $action = MoovicoRoute::DEFAULT_ACTION;

    /**
     * method 
     * 
     * @var string
     * @access protected
     */
    protected $method = MoovicoRequest::METHOD_GET;

    /**
     * args 
     * 
     * @var array
     * @access protected
     */
    protected $args = array();

    /**
     * params 
     * 
     * @var array
     * @access protected
     */
    protected $params = array();

    /**
     * __construct 
     * 
     * @param MoovicoRoute $route 
     * @access public
     * @return void
     */
    public function __construct(MoovicoRoute $route = null) {
        if (!empty($route)) {
            $this->SetController($route->GetController());
            $this->SetAction($route->GetAction());
            $this->SetArgs($route->GetArgs());
            $this->SetRequestMethod($_SERVER['REQUEST_METHOD']);
            $this->SetController($route->GetController());
        }
    }

    /**
     * SetController 
     * 
     * @param mixed $controller 
     * @access public
     * @return void
     */
    public function SetController($controller) {
       $this->controller = $controller; 

       return $this;
    }

    /**
     * GetController 
     * 
     * @param mixed $controller 
     * @access public
     * @return void
     */
    public function GetController() {
        return $this->controller;
    }

    /**
     * SetAction 
     * 
     * @param mixed $action 
     * @access public
     * @return void
     */
    public function SetAction($action = null) {
       $this->action = empty($action) ? MoovicoRoute::DEFAULT_ACTION : $action; 

       return $this;
    }

    /**
     * GetAction 
     * 
     * @access public
     * @return void
     */
    public function GetAction() {
        return $this->action;
    }

    /**
     * IsDefaultAction 
     * 
     * @access public
     * @return void
     */
    public function IsDefaultAction() {
        return $this->action === MoovicoRoute::DEFAULT_ACTION;
    }

    /**
     * SetRequestMethod 
     * 
     * @param mixed $method 
     * @access public
     * @return void
     */
    public function SetRequestMethod($method) {
        switch ($method) {
            case MoovicoRequest::METHOD_GET:
            case MoovicoRequest::METHOD_POST:
            case MoovicoRequest::METHOD_PUT:
            case MoovicoRequest::METHOD_DELETE:
                $this->method = $method;
                break;

            default:
                throw new MoovicoException('Invalid request method: '.$method, Moovico::E_CORE_INVALID_METHOD);
        }

        return $this;
    }

    /**
     * UseGET 
     * 
     * @access public
     * @return void
     */
    public function UseGET() {
        return $this->SetRequestMethod(MoovicoRequest::METHOD_GET);
    }

    /**
     * UsePOST 
     * 
     * @access public
     * @return void
     */
    public function UsePOST() {
        return $this->SetRequestMethod(MoovicoRequest::METHOD_POST);
    }

    /**
     * UsePUT 
     * 
     * @access public
     * @return void
     */
    public function UsePUT() {
        return $this->SetRequestMethod(MoovicoRequest::METHOD_PUT);
    }

    /**
     * UseDELETE 
     * 
     * @access public
     * @return void
     */
    public function UseDELETE() {
        return $this->SetRequestMethod(MoovicoRequest::METHOD_DELETE);
    }

    /**
     * GetRequestMethod 
     * 
     * @access public
     * @return void
     */
    public function GetRequestMethod() {
        return $this->method;
    }

    /**
     * SetArgs 
     * 
     * @param mixed $args 
     * @access public
     * @return void
     */
    public function SetArgs($args) {
        if (!is_array($args)) {
            $args = func_get_args();
        }

        $this->args = $args; 

        return $this;
    }

    /**
     * GetArgs 
     * 
     * @access public
     * @return void
     */
    public function GetArgs() {
        return $this->args;
    }

    /**
     * SetParams 
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function SetParams($params) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $this->params = $params; 

        return $this;
    }

    /**
     * SetParam 
     * 
     * @param mixed $name 
     * @param mixed $value 
     * @access public
     * @return void
     */
    public function SetParam($name, $value) {
        if (empty($value) && isset($this->params[$name])) {
            unset($this->params[$name]);
            return;
        }

        $this->params[$name] = $value;

        return $this;
    }

    /**
     * GetParams 
     * 
     * @access public
     * @return void
     */
    public function GetParams() {
        return $this->params;
    }

    public function GetSignature() {
        $str = '';
        $str.= $this->method;
        $str.= $this->controller;
        $str.= $this->action;
        $str.= http_build_query($this->args, '', '');
        $str.= http_build_query($this->params, '', '');

        return md5($str);
    }
}
