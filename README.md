# Overview

Piko is Web micro Framework to build [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
applications.

[https://piko-framework.github.io/](https://piko-framework.github.io/)

## specifications

 - Lightweight: It requires no composer dependencies and its code base is under 100kb.
 - Fast: Basic routing, components lasy loading and uses PHP as template engine.
 - Customizable: The framework can be extended throw Event hooks and behavior injections.
 - Stable: The framework components have been well tested and the API is fixed.
 - Modular : internal routes correspond to MVC modules.

## Features

 - Router: resolve url to routes and get url from routes.
 - Base Classes for controllers and models.
 - Views : Layout management and themable views.
 - I18n : To internatinalize applications.
 - User session management: user login / logout and user permissions.
 - Assets management : To use external assets in your project

## Installation via composer

```bash
composer require piko/framework
```

## Quick start

The [Piko project skeletton](https://github.com/piko-framework/piko-project) can be used to start a piko based project.

## Documentation

[https://piko-framework.github.io/](https://piko-framework.github.io/)

## Inspiration

Concepts used in Piko were inspired from the [Yii2 framework](https://www.yiiframework.com/).
