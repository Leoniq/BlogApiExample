<?php

namespace App\Application\Form\Command\Post;

use App\Application\Form\Command\Command\Command;

class PostUpdateCommand extends Command
{
    public int $id;
    public ?string $title;
    public ?string $description;
}
