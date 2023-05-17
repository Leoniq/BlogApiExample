<?php

namespace App\Infrastructure\Command\Post;

use App\Application\Form\Command\Command\Command;
use App\Domain\Entity\Post;
use App\Infrastructure\Repository\PostRepository;

class PostCreateCommandHandler
{
    protected PostRepository $repository;

    /**
     * PostCreateCommandHandler constructor.
     *
     * @param PostRepository $repository
     */
    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
        $post = Post::create($command->title, $command->description);
        $this->repository->save($post);
    }
}
