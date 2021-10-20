<?php

declare(strict_types=1);

namespace FeeCalcApp\Model;

/**
 * @Entity
 * @Table(name="client_types")
 */
class ClientType
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private int $id;

    /**
     * @Column(type="string", length="25", nullable="false")
     */
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
