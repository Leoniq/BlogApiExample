<?php

namespace App\Infrastructure\Representation;

use App\Domain\Entity\Post;
use JMS\Serializer\Annotation as Serializer;

class PostRepresentation
{
    /**
     * @Serializer\Groups ({
     *     "post_create",
     *     "post_list",
     *     "post_read",
     *     "post_update",
     * })
     */
    protected int $id;

    /**
     * @Serializer\Groups({
     *     "post_create",
     *     "post_list",
     *     "post_read",
     *     "post_update",
     * })
     */
    protected string $title;

    /**
     * @Serializer\Groups({
     *     "post_create",
     *     "post_list",
     *     "post_read",
     *     "post_update",
     * })
     */
    protected ?string $description;

    /**
     * @Serializer\Groups({
     *     "post_list",
     *     "post_read",
     * })
     */
    protected string $createdAt;

    /**
     * @Serializer\Groups({
     *     "post_list",
     *     "post_read",
     * })
     */
    protected string $updatedAt;

    /**
     * PostRepresentation constructor.
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->id = $post->getId();
        $this->title = $post->getTitle();
        $this->description = $post->getDescription();
        $this->createdAt = $post->getCreatedAt();
        $this->updatedAt = $post->getUpdatedAt();
    }
}
