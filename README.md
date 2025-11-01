# marwa-module

A framework-agnostic, PSR-11–friendly **module management** library for PHP.

It lets you keep reusable features in a `/modules` directory (or in Composer packages) where **each module has its own service provider and manifest**. The library discovers modules, exposes module metadata through a thin `ModuleBuilder`, and lets every module register its own routes, views, events, commands, migrations, and entities — without hard-wiring to Laravel, Symfony, or any specific framework.

> ✅ Designed to work nicely with MarwaPHP
> ✅ But also usable with any container that implements PSR-11

---

## Features

- **Filesystem-based modules**: `modules/User/manifest.php`
- **Composer-based modules** (via extra config or vendor scan)
- **Single entry point**: `ModulesServiceProvider`
- **Thin layer over container**: `ModuleBuilder`
- **Manifest in PHP or JSON**
- **Lazy loading**: modules can register routes/events only when the app asks
- **Framework agnostic**: no Laravel/Symfony hard dependency

---

## Directory structure

```text
project-root/
  src/
  vendor/
  modules/
    User/
      manifest.php
      src/
        Controllers/
        Models/
        Views/
        Commands/
        Events/
        Migrations/
        Entity/
      routes/
        http.php
    Billing/
      manifest.json
```

## Installing

```bash
composer require memran/marwa-module
```

## Requirements

- PHP 8.1+
- PSR-11 container (or adapter that implements it)
- Filesystem access to /modules

## License

MIT
