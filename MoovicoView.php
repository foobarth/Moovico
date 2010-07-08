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
     * __construct 
     * 
     * @param mixed $template_file 
     * @access public
     * @return void
     */
    public function __construct($template_file)
    {
        $this->template_file = Moovico::GetAppRoot().'Views/'.Moovico::SanitizeFile($template_file).'.phtml';
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
    public function Render()
    {
        ob_start();
        $this->Show();
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
    public function Show()
    {
        include($this->template_file);
    }
}
