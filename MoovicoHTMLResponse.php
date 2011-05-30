<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoHTMLResponse extends MoovicoResponseInterface
{
    /**
     * view 
     * 
     * @var mixed
     * @access protected
     */
    protected $view;

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct($template = null)
    {
        if (empty($template))
        {
            $route = Moovico::GetRoute();
            $template = $route->GetController().DIRECTORY_SEPARATOR.$route->GetAction();
        }

        $this->view = new MoovicoView($template);
    }

    /**
     * GetHeaders 
     * 
     * @access public
     * @return void
     */
    public function GetHeaders()
    {
        return array(
            'Content-Type: text/html; charset=utf-8',
        );
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function __toString()
    {
        $out = $this->view->Render($this);

        /*
        $moovico_time = Moovico::GetTime();
        $out.= '<!-- Moovico time: '.$moovico_time.' sec -->';
        */

        return $out;
    }
}

