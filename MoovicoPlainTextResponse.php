<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoPlainTextResponse extends MoovicoResponseInterface
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

        $str = '';
        foreach ($this as $k => $v)
        {
            $str.= sprintf("%s: %s", $k, $v);
        }

        return $str;
    }
}
