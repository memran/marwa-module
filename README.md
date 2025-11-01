# marwa-module

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-blue?style=flat-square)
![Downloads](https://img.shields.io/packagist/dt/memran/marwa-module?color=brightgreen&style=flat-square)
![License](https://img.shields.io/badge/license-MIT-lightgrey?style=flat-square)

A **framework-agnostic, PSR-11–friendly module management library** for PHP.

It enables modular application architecture — each module is self-contained with its own `manifest.php`, routes, views, models, and service provider.  
**marwa-module** discovers and bootstraps them automatically.

---

## ✨ Features

- 📁 Filesystem and Composer-based module discovery
- 🚀 One entry point (`ModulesServiceProvider`)
- 🧱 `ModuleBuilder` for clean module introspection
- 🧾 PHP or JSON manifest format
- ⚡ Static in-memory caching for instant reloads
- 💤 Lazy loading of routes, events, and commands
- 🔌 Framework-agnostic, PSR-11 compatible
- 🧪 Unit tested with PHPUnit

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
