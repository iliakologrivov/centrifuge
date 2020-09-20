<?php

namespace IliaKologrivov\Centrifuge;

/**
 * Class CentrifugeException
 *
 * @package IliaKologrivov\Centrifuge
 */
class CentrifugeException extends \Exception
{
    protected $error;

    protected $method;

    protected $data;

    public function __construct($error, $method, $data)
    {
        $this->error = $error;
        $this->method = $method;
        $this->data = $data;

        parent::__construct($error);
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
