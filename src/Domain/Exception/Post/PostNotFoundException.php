<?php

namespace App\Domain\Exception\Post;

use App\Domain\Exception\Exception\DomainNotFoundException;

class PostNotFoundException extends DomainNotFoundException
{
    /**
     * PostNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $message = 'Post does not exist',
        int $code = 404,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
