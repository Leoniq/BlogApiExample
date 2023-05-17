<?php

namespace App\Application\Form\Command\Post;

use App\Application\Form\Command\Command\Command;

class PostCreateCommand extends Command
{
    public string $title;
    public string $description;
}
