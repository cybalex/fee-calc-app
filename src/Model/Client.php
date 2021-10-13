<?php

declare(strict_types=1);

namespace FeeCalcApp\Model;

/**
 * @Entity
 * @Table(name="clients")
 */
class Client
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
