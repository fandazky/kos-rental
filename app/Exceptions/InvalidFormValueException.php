<?php
namespace App\Exceptions;

use Exception;

class InvalidFormValueException extends Exception
{

    protected $formField;

    protected $fieldError;

    public function __construct($message, $formField) {
        parent::__construct($message);
        $this->formField = $formField;
        $this->fieldError = $message;
    }

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context()
    {
        return ['form_field' => $this->formField];
    }

    public function getFormField() 
    {
        return $this->formField;
    }

    public function getFieldError() 
    {
        return $this->fieldError;
    }
}