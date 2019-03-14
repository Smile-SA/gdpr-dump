# Smile Anonymizer

## Description

This is a proof of concept of a data anonymization tool.

Libraries:

- [MySQLDump - PHP](https://github.com/ifsnop/mysqldump-php)
- [Faker](https://github.com/fzaninotto/faker)

## Basic Usage

```
bin/dump [--host=...] [--user=...] [--password=...] <db_name> [<dump_file>]
```

If no password is specified, it will be prompted.

This command creates an anonymized dump.

Currently, it only anonymizes a table named `customer_entity` (if it exists).

## Installation

Phar creation:

```php
bin/compile
```

Or with composer:

```php
composer create-project --repository-url=packages.json smile/database-anonymizer
```

With the following `packages.json` file:

```json
{
    "package": {
        "name": "smile/database-anonymizer",
        "version": "0.1.0",
        "source": {
          "url": "/path/to/database-anonymizer/.git",
          "type": "git",
          "reference": "master"
        }
    }
}
```

## Going Further

TODO:

- Add dump options / database driver in the console command (currently the driver is hardcoded to mysql)
- Use a config file for each platform (e.g. magento1, magento2, drupal8...).
- Make it possible to use a custom config file (e.g. myproject.yml that extends magento2.yml).
- Use a schema validator for config files (e.g. yml to json, then validate with json schema).
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

```yaml
tables:
  tmp_*:
    ignore: true

  cache:
    truncate: true
  cache_tag:
    truncate: true
  session:
    truncate: true

  customer_entity:
    limit: 1000
    fields:
      - field: email
        value: unique_email
      - field: firstname
        value: random_firstname
      - field: middlename
        value: set_null
      - field: lastname
        value: random_lastname

  admin_user:
    fields:
      - field: email
        value: unique_email
      - field: firstname
        value: random_firstname
      - field: lastname
        value: random_lastname
      - field: username
        value: unique_username
        condition: username <> 'admin'
```

The tool must allow config extension:

```yml
# myproject.yml

# File name without extension of a built-in template, or absolute path to a custom template
extends: magento2

# ...
```

It should also handle serialized/json data.
For example:

```yml
- field: additonial_data
  json_data:
    - path: customer.email
      value: random_email
```
