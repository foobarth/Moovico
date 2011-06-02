<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class MoovicoExtJSONResponse extends MoovicoJSONResponse
{
    /**
     * success 
     * 
     * @var mixed
     * @access public
     */
    public $success = true;

    /**
     * msg 
     * 
     * @var string
     * @access public
     */
    public $msg = '';

    /**
     * Apply 
     * 
     * @param mixed $payload 
     * @static
     * @access public
     * @return void
     */
    public static function Apply($payload = null)
    {
        $rsp = parent::Apply($payload);
        if ($payload instanceof Exception)
        {
            $rsp->Error($payload->getMessage());
        }

        return $rsp;
    }

    /**
     * Success 
     * 
     * @param mixed $success 
     * @access public
     * @return void
     */
    public function Success($success = true)
    {
        $this->success = $success === true;
    }

    /**
     * Error 
     * 
     * @param mixed $msg 
     * @access public
     * @return void
     */
    public function Error($msg)
    {
        $this->Success(false);

        if ($msg instanceof Exception) {
            $this->msg = $msg->getMessage();
            $this->error_code = $msg->getCode();
        } else {
            $this->msg = $msg;
        }
    }
}

