# Contributing

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include as much relevant information as you can.

A step by step guide on how to reproduce the issue will greatly increase the chances of your issue being resolved in a timely manner.

## Merge Requests

### Guidelines

If you want to add a feature, please first create an issue.
We'll then discuss whether it should be added to the core.

Before submitting a merge request, please ensure that your code meet these requirements:

- The code must be PSR-2 compliant.
- GdprDump has a minimum PHP version requirement of PHP 7.0.
  Don't use features that were introduced later than PHP 7.0.
- Use type hinting and strict typing.
- Use exactly the same formatting as the core classes (PHPDoc, spacing...).
- Use the `@inheritdoc` annotation in functions that extend a parent function.

### How to Submit a Merge Request

Follow these steps:

1. Fork the project.
2. Create a new branch.
3. Implement your bugfix/feature.
   Don't forget to update the functional and unit tests accordingly.
4. Run the tests (phpcs, phpmd, phpunit).
   All tests must succeed.
5. Create the merge request:
    - Source branch: the branch of your fork
    - Target branch: the master branch of the core repository
    - Title/description: as detailed as possible

### How to Run the Tests

Run the following commands:

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
