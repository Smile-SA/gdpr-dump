# Contributing to GdprDump

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include as much relevant information as you can.

A step by step guide on how to reproduce the issue will greatly increase the chances of your issue being resolved in a timely manner.

## Pull Requests

### Guidelines

If you want to add a feature, please first create an issue.
We'll then discuss whether it should be added to the core.

Before submitting a pull request, please ensure that your code meet these requirements:

- The code must be PSR-2 compliant.
- GdprDump has a minimum PHP version requirement of PHP 7.3.
  Don't use features that were introduced later than PHP 7.3.
- Use type hinting and strict typing.
- Use exactly the same formatting as the core classes (PHPDoc, spacing...).
- Use the `@inheritdoc` annotation in functions that extend a parent function.

### How to Submit a Pull Request

Follow these steps:

1. Fork the project.
2. Create a new branch.
3. Implement your bugfix/feature.
   Don't forget to update the functional and unit tests accordingly.
4. Run the tests (phpcs, phpmd, phpunit).
   All tests must succeed.
5. Create the pull request:
    - Source branch: the branch of your fork
    - Target branch: the master branch of the core repository
    - Title/description: as detailed as possible

### How to Run the Tests

#### Running Tests with Docker

**Prerequisites**

If you are running on Linux:

- Execute the commands `id -u` and `id -g`.
- If the output isn't "1000", then copy the ".env.example" file as ".env", and change the value of the UID/GID variables.

If you are running on Mac/Windows, docker should work out of the box (needs confirmation).

**Steps**

1. Install the project dependencies:

    ```
    docker-compose run --rm php composer install
    ```

2. Run the code validation tools (phpcs, phpmd, phpstan):

    ```
    docker-compose run --rm php run-sniffers
    ```

3. Run the unit/functional tests (phpunit):

    ```
    docker-compose run --rm php run-tests
    ```

#### Running Tests Manually

Run the following commands:

```
vendor/bin/phpcs
vendor/bin/phpmd bin,src,tests xml phpmd.xml.dist
vendor/bin/phpstan analyse
vendor/bin/phpunit
```

The PHPUnit tests require a database with the following credentials:

- host: `127.0.0.1` (can be changed by setting the `$DB_HOST` environment variable)
- database: `tests` (can be changed by setting the `$DB_NAME` environment variable)
- user: `tests` (can be changed by setting the `$DB_USER` environment variable)
- password: `tests` (can be changed by setting the `$DB_PASSWORD` environment variable)

## Database Driver Compatibility

As of now, GdprDump only supports the pdo_mysql driver.

To add compatibility with other drivers, the following actions would be required:

- Replace the mysqldump-php dependency by a tool that is compatible with multiple drivers.
- Make the database name optional in the DatabaseConfig class.
- Add driver specific parameters to the schema.json file.
- Replace the "SET" queries that define SQL variables by a database agnostic implementation.
