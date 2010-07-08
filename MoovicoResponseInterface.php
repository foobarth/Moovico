<?php

abstract class MoovicoResponseInterface
{
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

    abstract public function GetContentType();
    abstract public function __toString();
}
