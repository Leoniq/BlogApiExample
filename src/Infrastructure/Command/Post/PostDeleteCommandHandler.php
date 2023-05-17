<?php

namespace App\Infrastructure\Command\Post;

use App\Application\Form\Command\Command\Command;
use App\Infrastructure\Repository\PostRepository;

class PostDeleteCommandHandler
{
    protected PostRepository $repository;

    /**
     * PostDeleteCommandHandler constructor.
     *
     * @param PostRepository $repository
     */
    public function __construct(PostRepository $repository)
    {
        $this->repository  = $repository;
    }

    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
        $this->repository->remove($command->post);
    }
}
