<?php

namespace App\Exceptions;

use Throwable;

class Config extends \Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        $code = 400;
        $previous = null;
        parent::__construct($message, $code, $previous);
    }
}
