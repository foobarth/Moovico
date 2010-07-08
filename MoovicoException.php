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
     * TODO: short description.
     * 
     * @param mixed $msg  
     * @param mixed $code Optional, defaults to 0. 
     * 
     */
    public function __construct($msg, $code = 0)
    {
        if (empty($code))
        {
            $trace = $this->getTrace();
            $code = crc32($trace[0]['class'].$msg); // auto generate a code
        }

        parent::__construct($msg, $code);
    }
}
