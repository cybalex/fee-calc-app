<?php

declare(strict_types=1);

namespace FeeCalcApp\Model;

/**
 * @Entity
 * @Table(name="currency")
 */
class Currency
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private int $id;

    /**
     * @Column(type="string", columnDefinition="CHAR(2) NOT NULL")
     */
    private string $code;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
