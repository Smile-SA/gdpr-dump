# Smile Anonymizer

## Description

This is a proof of concept of a data anonymization tool.

Libraries:

- [MySQLDump - PHP](https://github.com/ifsnop/mysqldump-php)
- [Faker](https://github.com/fzaninotto/faker)

## Basic Usage

```
bin/anonymizer [--host=...] [--user=...] [--password=...] db_name
```

If no password is specified, it will be prompted.

This command creates an anonymized dump.

Currently, it only anonymizes the `email` field of the `customer_entity` table (if it exists).

## TODO

Installation:

- Provide a PHAR.

Code:

- Anonymize all personal data
- Use an abstraction layer.
- Compatibility with multiple platforms (Magento 1, Magento 2, Drupal...).
- Use a config file for each platform (e.g. magento2.yml).
- Make it possible to use a custom config file (e.g. myproject.yml that extends magento2.yml).
- Use a schema validator for config files (e.g. yml to json, then validate with json schema)
- Logs?
- Tests

An abstraction layer should be implemented for the following entities:

- Config
- Dumper
- Formatter

Documentation:

- Implement CHANGELOG.LOG
- Implement CONTRIBUTING.MD
- Implement LICENSE.MD
- Complete documentation (in proper english!)

Config example:

```yml
# magento.yml

tables:
  customer_entity:
    fields:
      firstname:
        action: firstname
      lastname:
        action: lastname
      email:
        action: email

  visitor_log:
    action: truncate
```

Using this config file, the tool would:

- Anonymize the `firstname`, `lastname` and `email` columns of the `customer_entity` table.
- Truncate the `visitor_log` table.

The tool must allow config extension:

```yml
# myproject.yml

extends: magento

tables:
  customer_entity:
    action: truncate
```

It should also handle serialized/json data.
For example:

```yml
tables:
  example_table:
    fields:
      example_field:
        action: json_update
        fields:
          - path: customer.firstname
            action: firstname
          - path: customer.lastname
            action: lastname
```

This would allow to anonymize the following value:

`{"customer":{"firstname":"John","lastname":"Doe"}}`

## How To Test Installation

Phar creation:

```php
bin/compile
```

Or with composer:

```php
composer create-project --repository-url=packages.json smile/anonymizer
```

With the following `packages.json` file:

```json
{
    "package": {
        "name": "smile/anonymizer",
        "version": "1.0.0",
        "source": {
          "url": "/path/to/anonymizer/.git",
          "type": "git",
          "reference": "master"
        }
    }
}
```
