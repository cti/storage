<?php

namespace Common;

use Exception;

class ExceptionHandlingController
{

    function processException(Exception $e)
    {
        return $e->getMessage();
    }
}