<?php

/**
 * MoovicoExceptionHandlerInterface 
 * 
 * @abstract
 * @package 
 * @version 
 * @copyright (c) Kodweiss E-Business GmbH
 * @author Kodweiss E-Business GmbH 
 * @license 
 */
abstract class MoovicoExceptionHandlerInterface
{
    /**
     * Handle (must return MoovicoResponse)
     * 
     * @param Exception $e 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function Handle(Exception $e);
}
