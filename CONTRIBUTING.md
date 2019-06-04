# Contributing

## Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include as much relevant information as you can.

A step by step guide on how to reproduce the issue will greatly increase the chances of your issue being resolved in a timely manner.

## Contributing Guidelines

**Don't commit directly on this repository.**

Fork the project, create a feature branch, and send a pull request.

Unauthorized commits will always be removed.

## Unit Tests

To run the unit tests:

```
vendor/bin/phpunit
```

The tests require the following MySQL database:

- Name: `test`
- User: `test`
- Password: `test`

These parameters can be modified in the [test.yaml](tests/Resources/config/templates/test.yaml) template file.

It is also possible to disable the unit tests that depend on the database, by setting the `skip_database_tests` to `true` in the [phpunit.xml](phpunit.xml) file.
