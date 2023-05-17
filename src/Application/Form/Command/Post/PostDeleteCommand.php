<?php

namespace App\Application\Form\Command\Post;

use App\Application\Form\Command\Command\Command;
use App\Domain\Entity\Post;

class PostDeleteCommand extends Command
{
    public Post $post;
}
