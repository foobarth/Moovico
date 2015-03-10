<?php

/**
 * MoovicoResponseInterface 
 * 
 * @abstract
 * @package 
 * @version 
 * @copyright (c) Kodweiss E-Business GmbH
 * @author Kodweiss E-Business GmbH 
 * @license 
 */
abstract class MoovicoResponseInterface
{
    protected $headers = array();
    protected $expiresInSeconds = 0;

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        // empty for now...
    }

    /**
     * Apply 
     * 
     * @param MoovicoResponseInterface $response 
     * @static
     * @access public
     * @return void
     */
    public static function Apply($payload = null)
    {
        $class = get_called_class();
        $obj = new $class;

        if (!empty($payload))
        {
            if ($payload instanceof Exception)
            {
                $obj->msg = $payload->getMessage();
                $obj->code = $payload->getCode();
                $obj->exception = get_class($payload);
            }
            else
            {
                foreach ($payload as $k => $v)
                {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }

    /**
     * GetHeaders 
     * 
     * @access public
     * @return void
     */
    public function GetHeaders()
    {
        return $this->headers;
    }

    public function AddHeader($header) {
        $this->headers[] = $header;
    }

    public function Expires($expiresInSeconds) {
        $this->expiresInSeconds = $expiresInSeconds;

        $now = time();
        $this->AddHeader('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $now + $expiresInSeconds));
        $this->AddHeader('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $now));
        $this->AddHeader('Cache-Control: public, max-age='.$expiresInSeconds);
    }

    public function GetExpireTime() {
        return (int)$this->expiresInSeconds;
    }

    /**
     * __toString 
     * 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function __toString();
}
