<?php

declare(strict_types=1);

namespace Marwa\Module;

use Psr\Container\ContainerInterface;
use Marwa\Module\Contracts\ModuleServiceProviderInterface;

class ModulesServiceProvider implements ModuleServiceProviderInterface
{
      public function __construct(
            private string $modulesPath
      ) {
            $this->modulesPath = rtrim($modulesPath, DIRECTORY_SEPARATOR);
      }

      public function register($app): void
      {
            // Discover all modules
            $repository = new ModuleRepository($this->modulesPath);
            $registry   = new ModuleRegistry($repository);
            $builder    = new ModuleBuilder($registry, $app);

            // Bind core objects
            if (method_exists($app, 'add')) {
                  $app->add(ModuleRepository::class, $repository);
                  $app->add(ModuleRegistry::class, $registry);
                  $app->add(ModuleBuilder::class, $builder);
            }
            // var_dump($builder->all());
            // die;

            // Automatically register each module’s service provider
            foreach ($builder->all() as $module) {
                  foreach ($module->getProviders() as $providerClass) {
                        if (class_exists($providerClass)) {
                              // This uses your container’s method
                              if (method_exists($app, 'addServiceProvider')) {
                                    $app->addServiceProvider($providerClass);
                              } else {
                                    // fallback: manual instantiation
                                    $provider = new $providerClass();
                                    if (method_exists($provider, 'register')) {
                                          $provider->register($app);
                                    }
                              }
                        }
                  }
            }
      }

      public function boot($app): void
      {
            // If container bootstraps providers lazily, this may be empty.
      }
}
