[![PHP Composer](https://github.com/turahe/core/actions/workflows/php.yml/badge.svg)](https://github.com/turahe/core/actions/workflows/php.yml)

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


