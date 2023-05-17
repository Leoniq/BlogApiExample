<?php

namespace App\Domain\Entity;

use App\Domain\Entity\Entity\DomainEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Infrastructure\Repository\PostRepository;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post extends DomainEntity
{
    /**
     * @ORM\Column(type="string")
     */
    protected ?string $title;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $description;

    /**
     * Post constructor.
     *
     * @param int|null $id
     * @param string|null $title
     * @param string|null $description
     */
    public function __construct(
        int $id = null,
        ?string $title = null,
        ?string $description = null
    ) {
        $this->title = $title;
        $this->description = $description;

        parent::__construct($id);
    }

    /**
     * @param string|null $title
     * @param string|null $description
     *
     * @return static
     */
    public static function create(?string $title, ?string $description): self
    {
        return new self(null, $title, $description);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function addTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function addDescription(string $description): void
    {
        $this->description = $description;
    }
}
