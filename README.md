<p align="center">
    <a href="https://laravel.com">
        <img alt="Laravel v11.x" src="https://img.shields.io/badge/Laravel-v11.x-FF2D20">
    </a>
    <a href="https://github.com/turahe/core/actions/workflows/php.yml">
<img src="https://github.com/turahe/core/actions/workflows/php.yml/badge.svg" alt="Build Status">
</a>
    <a href="https://packagist.org/packages/turahe/core">
        <img src="https://img.shields.io/packagist/dt/turahe/core" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/turahe/core">
        <img src="https://img.shields.io/packagist/v/turahe/core.svg?label=Packagist" alt="Packagist" />
    </a>
    <a href="https://github.com/turahe/core/blob/main/LICENSE">
        <img src="https://img.shields.io/packagist/l/turahe/core.svg?label=License" alt="Packagist" />
    </a>
</p>

# Turahe Core SDK


## Installation

1. Install the package via composer:

    ```shell
    composer require turahe/core
    ```

2. Publish resources (migrations and config files):

    ```shell
    php artisan vendor:publish --provider="Turahe\Core\CoreServiceProvider"
    ```

3. Execute migrations via the following command:

    ```shell
    php artisan migrate
    ```

4. Done!


