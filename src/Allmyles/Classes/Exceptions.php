<?php
namespace Allmyles\Exceptions;

class ServiceException extends \Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}
