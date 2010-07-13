<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoJSONResponse extends MoovicoResponseInterface
{
    /**
     * GetHeaders 
     * 
     * @access public
     * @return void
     */
    public function GetHeaders()
    {
        return array(
            'Content-Type: application/json; charset=utf-8',
        );
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function __toString()
    {
        $this->moovico_time = Moovico::GetTime();
        return json_encode($this);
    }
}

