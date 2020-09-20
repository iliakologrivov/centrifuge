<?php

namespace IliaKologrivov\Centrifuge\Channels;

class PresenceChannel extends Channel
{
    /**
     * Create a new channel instance.
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        parent::__construct('#' . $name);
    }
}
