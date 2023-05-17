<?php

namespace App\Infrastructure\Command\Post;

use App\Application\Form\Command\Command\Command;
use App\Infrastructure\Repository\PostRepository;

class PostUpdateCommandHandler
{
    protected PostRepository $repository;

    /**
     * PostUpdateCommandHandler constructor.
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
        $post = $this->repository->find($command->id);

        (null !== $command->title and "" !== $command->title) ?
            $post->addTitle($command->title) : null;
        (null !== $command->description and "" !== $command->description) ?
            $post->addDescription($command->description) : null;
        (null !== $command->title or null !== $command->description) ?
            $post->setUpdatedAt(new \DateTime()) : null;

        $this->repository->update($post);
    }
}
