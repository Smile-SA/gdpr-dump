# Contributing

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include as much relevant information as you can.

A step by step guide on how to reproduce the issue will greatly increase the chances of your issue being resolved in a timely manner.

## Contribution Guidelines

**Don't commit directly on this repository.**

Fork the project, create a feature branch, and send a pull request.

Unauthorized commits will always be removed.

## Code Quality

This project uses phpcs (PSR-12 coding standard), phpmd and phpunit.

To run these tools, the following commands are available:

```php
composer phpcs
composer phpmd
composer phpunit
```

The functional tests require the following MySQL database:

- Host: `mysql`
- Name: `test`
- User: `test`
- Password: `test`

These parameters can be modified in the [test.yaml](tests/functional/Resources/config/templates/test.yaml) template file.

It is also possible to disable the unit tests that depend on the database, by setting the `skip_database_tests` to `true` in the [phpunit.xml](phpunit.xml) file.
