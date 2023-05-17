<?php

namespace App\Domain\Exception\Form;

use App\Domain\Exception\Exception\DomainBadRequestException;

class FormValidErrorException extends DomainBadRequestException
{
    /**
     * FormValidErrorException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $message = 'An error occurred while validating the form',
        int $code = 400,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
