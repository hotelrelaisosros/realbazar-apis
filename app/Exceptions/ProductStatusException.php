<?php

namespace App\Exceptions;

use Exception;


class ProductStatusException extends Exception
{
    public function __construct($message = "Product is not active or has been deleted.", $code = 404)
    {
        parent::__construct($message, $code);
    }
}
