<?php

/**
 * MoovicoCSVResponse 
 * 
 * @uses MoovicoResponseInterface
 * @package 
 * @version 
 * @copyright (c) Kodweiss E-Business GmbH
 * @author Kodweiss E-Business GmbH 
 * @license 
 */
class MoovicoCSVResponse extends MoovicoResponseInterface
{
    /**
     * filename 
     * 
     * @var mixed
     * @access protected
     */
    protected $filename;

    /**
     * data 
     * 
     * @var mixed
     * @access public
     */
    public $data;

    /**
     * enclosed 
     * 
     * @var mixed
     * @access protected
     */
    protected $enclosed;

    /**
     * terminated 
     * 
     * @var mixed
     * @access protected
     */
    protected $terminated;

    /**
     * seperated 
     * 
     * @var mixed
     * @access protected
     */
    protected $seperated;

    /**
     * __construct 
     * 
     * @param mixed $filename 
     * @access public
     * @return void
     */
    public function __construct($filename, $data, $e = '"', $s = ';', $t = "\n")
    {
        parent::__construct();

        $this->filename = preg_replace('/\W\./', '', $filename);
        $this->data = $data;
        $this->seperated = $s;
        $this->enclosed = $e;
        $this->terminated = $t;
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
            'Pragma: public',
            'Expires: 0',
            'Cache-Control: must-revalidate, post-check=0, pre-check=0',
            'Cache-Control: private',
            'Content-Type: application/csv',
            'Content-Disposition: attachment; filename='.$this->filename,
            'Content-Transfer-Encoding: binary'
        );
    }

    /**
     * __toString 
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        $str = '';
        foreach ($this->data as $idx => $row)
        {
            $str.= $row->toCSV($idx === 0, $this->enclosed, 
                    $this->seperated, $this->terminated); // print headers on first row
        }
        
        return $str;
    }
}

