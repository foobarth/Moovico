<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoDebugResponse extends MoovicoResponseInterface
{
    /**
     * GetContentType 
     * 
     * @access public
     * @return void
     */
    public function GetContentType()
    {
        return 'text/plain; charset=utf-8';
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function __toString()
    {
        $this->moovico_time = Moovico::GetTime();
        return print_r($this, true);
    }
}
