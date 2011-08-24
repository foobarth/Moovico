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
     * css 
     * 
     * @var mixed
     * @access protected
     */
    protected $css;

    /**
     * js 
     * 
     * @var mixed
     * @access protected
     */
    protected $js;

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
     * AddCSS 
     * 
     * @param mixed $type 
     * @param mixed $file 
     * @access public
     * @return void
     */
    public function AddCSS($type, $file) {
        settype($this->css[$type], 'array');
        $this->css[$type][] = $file;
    }

    /**
     * renderCSS 
     * 
     * @param mixed $type 
     * @access protected
     * @return void
     */
    protected function renderCSS($type) {
        if (empty($this->css[$type])) {
            return;
        }

        foreach ($this->css[$type] as $file) {
            printf("<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\">\n", $file);
        }
    }

    /**
     * AddJS 
     * 
     * @param mixed $type 
     * @param mixed $file 
     * @access public
     * @return void
     */
    public function AddJS($type, $file) {
        settype($this->js[$type], 'array');
        $this->js[$type][] = $file;
    }

    /**
     * renderJS 
     * 
     * @param mixed $type 
     * @access protected
     * @return void
     */
    protected function renderJS($type) {
        if (empty($this->js[$type])) {
            return;
        }

        foreach ($this->js[$type] as $file) {
            printf("<script type=\"text/javascript\" src=\"%s\"></script>\n", $file);
        }
    }

    /**
     * __get 
     * 
     * @param mixed $what 
     * @access public
     * @return void
     */
    public function __get($what)
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
