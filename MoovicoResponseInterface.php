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
     * @abstract
     * @access public
     * @return void
     */
    abstract public function GetHeaders();

    /**
     * __toString 
     * 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function __toString();
}
