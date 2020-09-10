<?php

namespace site;

class ErrorException extends \Exception
{
    protected $errors = [];

    public function setErrors(array $errors) : ErrorException
    {
        $this->errors = $errors;

        return $this;
    }
    public function getErrors() : array
    {
        return $this->errors;
    }
}
