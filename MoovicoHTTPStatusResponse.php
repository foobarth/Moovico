<?php

/**
 * MoovicoHTTPStatusResponse 
 * 
 * @uses MoovicoResponseInterface
 * @package 
 * @version 
 * @copyright (c) Kodweiss E-Business GmbH
 * @author Kodweiss E-Business GmbH 
 * @license 
 */
class MoovicoHTTPStatusResponse extends MoovicoResponseInterface
{
    /**
     * some references to most important status codes 
     */
    const STATUS_OK                     = 200;
    const STATUS_CREATED                = 201;
    const STATUS_ACCEPTED               = 202;
    const STATUS_NO_CONTENT             = 204;
    const STATUS_MOVED                  = 301;
    const STATUS_FOUND                  = 302;
    const STATUS_NOT_MODIFIED           = 304;
    const STATUS_BAD_REQUEST            = 400;
    const STATUS_UNAUTHORIZED           = 401;
    const STATUS_FORBIDDEN              = 403;
    const STATUS_NOT_FOUND              = 404;
    const STATUS_NOT_ACCEPTABLE         = 406;
    const STATUS_CONFLICT               = 409;
    const STATUS_GONE                   = 410;
    const STATUS_PRECONDITION_FAILED    = 412;
    const STATUS_EXPECTATION_FAILED     = 417;

    /**
     * code descriptions
     */
    static protected $descriptions = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    );

    /**
     * GetHeaders 
     * 
     * @access public
     * @return void
     */
    public function GetHeaders()
    {
        $str = sprintf("%d %s", $this->status, self::$descriptions[$this->status]);

        return array(
            "HTTP/1.0 $str",
            "Status: $str",
        );
    }

    /**
     * Apply 
     * 
     * @param MoovicoResponseInterface $response 
     * @static
     * @access public
     * @return void
     */
    public static function Apply($status)
    {
        $obj = parent::Apply();
        $obj->status = $status;

        return $obj;
    }

    /**
     * __toString 
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        return '';
    }
}
