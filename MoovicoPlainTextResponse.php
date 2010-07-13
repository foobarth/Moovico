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
     * GetHeaders 
     * 
     * @access public
     * @return void
     */
    public function GetHeaders()
    {
        return array(
            'Content-Type: text/plain; charset=utf-8',
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

        $str = '';
        foreach ($this as $k => $v)
        {
            $str.= sprintf("%s: %s", $k, $v);
        }

        return $str;
    }
}
