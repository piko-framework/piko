# Piko

[![build](https://github.com/piko-framework/piko/actions/workflows/php.yml/badge.svg)](https://github.com/piko-framework/piko/actions/workflows/php.yml)
[![Coverage Status](https://coveralls.io/repos/github/piko-framework/piko/badge.svg?branch=main)](https://coveralls.io/github/piko-framework/piko?branch=main)

Piko is a micro framework to build modular [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
applications.

[https://piko-framework.github.io/](https://piko-framework.github.io/)

## specifications

 - Compliant with [PSR-4](https://www.php-fig.org/psr/psr-4), [PSR-7](https://www.php-fig.org/psr/psr-7),
 [PSR-14](https://www.php-fig.org/psr/psr-14) and [PSR-15](https://www.php-fig.org/psr/psr-15)
 - Lightweight: Code base including its dependencies is under 200kb.
 - Blazing fast: Fast router ([Piko router](https://github.com/piko-framework/router)), components lazy loading and 
 using PHP as template engine.
 - Customizable: The framework components can be customized throw events and behavior injections.
 - Stable: All framework parts have been well tested.
 - Modular : MVC logic is packaged into modules.

## Installation via composer

```bash
composer require piko/framework
```

## Quick start

The [Piko project skeletton](https://github.com/piko-framework/piko-project) can be used to start a piko based project.

## Documentation

[https://piko-framework.github.io/](https://piko-framework.github.io/)

## Inspiration

Concepts used in Piko were initially inspired from the [Yii2 framework](https://www.yiiframework.com/).
