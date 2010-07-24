<?php

class MoovicoExceptionHandler extends MoovicoExceptionHandlerInterface
{
    /**
     * TODO: short description.
     * 
     * @param Exception $e 
     * 
     * @return TODO
     */
    public function Handle(Exception $e)
    {
        error_log($e->getMessage());
        return $e; // haha
    }
}
