<?php

namespace IliaKologrivov\Centrifuge\Channels;

class PrivateChannel extends Channel
{
    public function __construct($name)
    {
        parent::__construct('$' . $name);
    }
}
