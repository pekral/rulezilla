# Rulezilla
List of rules for writing clear code in PHP.

## Installation
```
composer require --dev pekral/rulezilla
```

## Supported tools
### Lint
https://github.com/php-parallel-lint/PHP-Parallel-Lint
```
php rulezilla lint:run
```
### Phpcpd
https://github.com/sebastianbergmann/phpcpd
```
php rulezilla phpcpd:run
```
### Phpcs (with fixer support)
https://github.com/squizlabs/PHP_CodeSniffer
```
php rulezilla phpcs:run
php rulezilla phpcs:fix
```
### Phpmnd
https://github.com/povils/phpmnd
```
php rulezilla phpmnd:run
```
### Phpstan
https://github.com/phpstan/phpstan
```
php rulezilla phpstan:run
```
### Phpunit
https://github.com/phpunit/phpunit
```
php rulezilla phpunit:run
```
### Rector (with fixer support)
https://github.com/rector/rector
```
php rulezilla rector:run
php rulezilla rector:fix
```

## Executing all commands
```
php rulezilla run
```

## Executing command with debug info
```
php rulezilla {command} --debug=true
```
