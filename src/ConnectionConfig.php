<?php
/**
 * This file is part of the Makise-Co Postgres Client
 * World line: 0.571024a
 *
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Postgres;

use MakiseCo\Connection\ConnectionConfig as BaseConnectionConfig;

use function addcslashes;
use function implode;

class ConnectionConfig extends BaseConnectionConfig
{
    private array $options;
    private float $connectTimeout;
    private bool $unbuffered;

    private string $dsn = '';

    /**
     * ConnectConfig constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string|null $password
     * @param string|null $database
     * @param string[] $options
     * @param float $connectTimeout
     * @param bool $unbuffered should Postgres client work in unbuffered mode?
     *
     */
    public function __construct(
        string $host,
        int $port,
        string $user,
        ?string $password = null,
        ?string $database = null,
        array $options = [],
        float $connectTimeout = 2,
        bool $unbuffered = false
    ) {
        parent::__construct($host, $port, $user, $password, $database);

        $this->options = $options;
        $this->connectTimeout = $connectTimeout;
        $this->unbuffered = $unbuffered;
    }

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function getUnbuffered(): bool
    {
        return $this->unbuffered;
    }

    public function __toString(): string
    {
        if ('' !== $this->dsn) {
            return $this->dsn;
        }

        $parts = [
            'host=' . $this->getHost(),
            'port=' . $this->getPort(),
            'user=' . $this->escapeValue($this->getUser()),
        ];

        $password = $this->getPassword();
        if ($password) {
            $parts[] = 'password=' . $this->escapeValue($password);
        }

        $database = $this->getDatabase();
        if ($database) {
            $parts[] = 'dbname=' . $this->escapeValue($database);
        }

        foreach ($this->options as $option => $value) {
            $parts[] = "{$option}={$this->escapeValue($value)}";
        }

        return $this->dsn = implode(' ', $parts);
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function getConnectionString(): string
    {
        return $this->__toString();
    }

    protected function escapeValue(string $value): string
    {
        if ('' === $value) {
            return $value;
        }

        $escaped = addcslashes($value, "'\\");

        return "'{$escaped}'";
    }
}
