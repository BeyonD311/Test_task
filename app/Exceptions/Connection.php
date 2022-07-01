<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class Connection extends \Exception
{
    #[Pure] public function __construct($message = "", $code = 0)
    {
        $previous = null;
        parent::__construct($message, $code, $previous);
    }
}
