<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoXMLResponse extends MoovicoResponseInterface
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
            'Content-Type: text/xml; charset=utf-8',
        );
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function __toString()
    {
        return 'NOT IMPLEMENTED';
    }
}

