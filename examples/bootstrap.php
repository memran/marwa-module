<?php

declare(strict_types=1);

namespace Marwa\Module\Examples;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class ExampleApplication implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $entries = [];

    /**
     * @var list<ModuleServiceProviderInterface>
     */
    private array $providers = [];

    public function add(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }

    public function set(string $id, mixed $value): void
    {
        $this->add($id, $value);
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new RuntimeException("Entry [$id] not found.");
        }

        return $this->entries[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }

    public function addServiceProvider(string $providerClass): void
    {
        /** @var ModuleServiceProviderInterface $provider */
        $provider = new $providerClass();

        if (method_exists($provider, 'setContainer')) {
            $provider->setContainer($this);
        }

        $provider->register($this);
        $this->providers[] = $provider;
    }

    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot($this);
        }
    }
}
