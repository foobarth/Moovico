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
     * __construct 
     * 
     * @param mixed $filename 
     * @access public
     * @return void
     */
    public function __construct($filename, $data)
    {
        parent::__construct();

        $this->filename = preg_replace('/\W/', '', $filename);
        $this->data = $data;
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
            'Content-Type: application/csv',
            'Content-Disposition: attachment; filename='.$this->filename,
            'Pragma: no-cache'
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
            $str.= $row->toCSV($idx === 0); // print headers on first row
        }
        
        return $str;
    }
}

