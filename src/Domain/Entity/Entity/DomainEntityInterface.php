<?php

namespace App\Domain\Entity\Entity;

interface DomainEntityInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id): void;
}
