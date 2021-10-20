<?php

declare(strict_types=1);

namespace FeeCalcApp\Model;

use DateTime;

/**
 * @Entity
 * @Table(name="transactions",indexes={
 *     @Index(name="date_idx", columns={"datetime"})
 * }))
 */
class Transaction
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private int $id;

    /**
     * @Column(type="datetime")
     */
    private DateTime $dateTime;

    /**
     * @ManyToOne(targetEntity="Client")
     * @JoinColumn(name="client_id", referencedColumnName="id")
     */
    private Client $client;

    /**
     * @ManyToOne(targetEntity="TransactionType")
     * @JoinColumn(name="transaction_id", referencedColumnName="id")
     */
    private TransactionType $transactionType;

    /**
     * @ManyToOne(targetEntity="Currency")
     * @JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private Currency $currency;

    /**
     * @ManyToOne(targetEntity="ClientType")
     * @JoinColumn(name="client_id", referencedColumnName="id")
     */
    private ClientType $clientType;

    /**
     * @Column(type="integer", options={"unsigned"=true})
     */
    private int $amount;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getTransactionType(): TransactionType
    {
        return $this->transactionType;
    }

    public function setTransactionType(TransactionType $transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getClientType(): ClientType
    {
        return $this->clientType;
    }

    public function setClientType(ClientType $clientType): self
    {
        $this->clientType = $clientType;

        return $this;
    }
}
