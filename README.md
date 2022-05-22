# Frostnova Starter Project

Starter project for [Frostnova](https://github.com/ironexdev/frostnova) - a fully customizable and [PSR](https://www.php-fig.org) compatible PHP framework.

## Requirements

- PHP `^8.0.0`

## How to create a new project
Run `composer create-project frostnova/starter <folder>`

## Hints
- Run `composer run add-docker` to add Docker environment for the project
- Rename project in `composer.json`
- Define interfaces in `config/config-di.php`
- Define routes in `config/api/routes.php`
- Create custom `AbstractController` and extend `Frostnova\Api\AbstractController` to override selected methods to add custom response handling
- Search (case insensitive) for frostnova and frost nova in the project and replace it with your project's name
- Configure or comment out `CorsMiddleware` in `config/config-di.php`
    - Middleware is handled before request gets to `Controller`
