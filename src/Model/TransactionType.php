<?php

declare(strict_types=1);

namespace FeeCalcApp\Model;

/**
 * @Entity
 * @Table(name="transaction_types")
 */
class TransactionType
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private int $id;

    /**
     * @Column(type="string", length="25")
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
