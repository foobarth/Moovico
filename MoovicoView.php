<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoView
{
    /**
     * template_file 
     * 
     * @var mixed
     * @access protected
     */
    protected $template_file;

    /**
     * payload 
     * 
     * @var mixed
     * @access protected
     */
    protected $payload;

    /**
     * __construct 
     * 
     * @param mixed $template_file 
     * @access public
     * @return void
     */
    public function __construct($template_file)
    {
        $this->template_file = Moovico::GetAppRoot().'views/'.Moovico::SanitizeFile($template_file).'.phtml';
        if (!file_exists($this->template_file))
        {
            throw new MoovicoException('View file not found: '.$this->template_file, Moovico::E_VIEW_NO_TEMPLATE);
        }
    }

    /**
     * Render 
     * 
     * @access public
     * @return void
     */
    public function Render($payload)
    {
        ob_start();
        $this->Show($payload);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Show 
     * 
     * @access public
     * @return void
     */
    public function Show($payload)
    {
        $this->payload = $payload;
        $this->doShow();
    }

    /**
     * __get 
     * 
     * @param mixed $what 
     * @access protected
     * @return void
     */
    protected function __get($what)
    {
        if (!empty($this->payload->{$what}))
        {
            return $this->payload->{$what};
        }

        return null;
    }

    /**
     * doShow 
     * 
     * @access protected
     * @return void
     */
    protected function doShow()
    {
        include($this->template_file);
    }
}
