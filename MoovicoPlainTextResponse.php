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
     * content 
     * 
     * @var mixed
     * @access protected
     */
    public $content = '';

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
        $str = $this->content;

        foreach ($this as $k => $v)
        {
            if ($k == 'content') {
                continue;
            }

            $str.= sprintf("%s: %s", $k, $v);
        }

        return $str;
    }
}
