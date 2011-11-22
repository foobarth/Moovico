<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoException extends Exception
{
    /**
     * payload 
     * 
     * @var mixed
     * @access protected
     */
    protected $payload = array();

    /**
     * TODO: short description.
     * 
     * @param mixed $msg  
     * @param mixed $code Optional, defaults to 0. 
     * 
     */
    public function __construct($msg, $code = 0, Array $payload = array())
    {
        if (empty($code)) {
            $code = $this->generateCode($msg);
        }

        if (!empty($payload)) {
            $this->payload = $payload;
        }

        Moovico::Debug($msg, $code);
        parent::__construct($msg, $code);
    }

    /**
     * generateCode 
     * 
     * @param mixed $msg 
     * @access protected
     * @return void
     */
    protected function generateCode($msg) {
        $trace = $this->getTrace();
        $code = crc32($trace[0]['class'].$msg); // auto generate a code

        return $code;
    }

    /**
     * GetPayload 
     * 
     * @access public
     * @return void
     */
    public function GetPayload() {
        return $this->payload;
    }
}
