# Smile Anonymizer

## Description

This tool provides a command that dumps the contents of a database to the specified output (e.g. dump file).

You can use a config file to specify how the database should be dumped.

In the config file, you can:
 
- specify data converters that allow you to transform the data before it is dumped to the file.
  It can be used to create an anonymized dump file.
- specify the tables to ignore (not included in the dump).
- specify the tables to truncate (included in the dump, but without any data).
- specify the database connection info (host, user, password...)
- specify dump options (compression, output type...)

## Prerequisites

Requirements:

- PHP >= 7.0
- MySQL, or one of its variants (MariaDB, Percona)

## Installation

You can create a PHAR file with the following command:

```php
bin/compile
```

Alternatively, you can install the project with composer:

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

The package is not in packagist yet, we need to find a name first.

## Documentation

Table of contents:

1. [Basic usage](docs/01-commands.md)
1. [Configuration](docs/02-configuration.md)
2. [Converters](docs/03-converters.md)

Also, there are multiple examples of config files in the config/templates directory.

## FAQ

**What if I don't meet the requirements?**

SQL: if you use any other DBMS than MySQL, there are two options:

- You can dump the structure of your database with your usual dump utility, and use our tool to dump only the INSERT queries.
  This will work only if your DBMS allows to temporary disable foreign keys. 
- You can set up a cron job on your production environment that clones your database, anonymizes it (by running SQL queries), and creates the dump file.

PHP: if you use a PHP version < 7.0, you have bigger worries than not being able to anonymize your data!
You need to upgrade to a [supported version of PHP](http://php.net/supported-versions.php) as soon as possible.
Each release branch of PHP is supported for 3 years (2 years of full support, then 1 year of security support).

**Why don't you use Doctrine to generate the dump?**

The goal of Doctrine is to support a wide array of DBMS.
It does not support features that are specific to some databases.

For example, in MySQL, it is possible to create an index on BLOB columns.
There is a restriction though, you need to specify the length of the index.

This feature is used in Magento 2.
Since Doctrine does not support this feature, it cannot be used to create a working dump file of a Magento 2 database.
The following error would trigger during the import of the generated dump file:

```
ERROR 1170 (42000) at line 254: BLOB/TEXT column 'code' used in key specification without a key length
```

Also, the schema manager of Doctrine can only manage tables.
It does not handle triggers, procedures, views...

As a consequence, we don't use Doctrine to generate the dump file.
The dump file is generated with [MySQLDump-PHP](https://github.com/ifsnop/mysqldump-php) instead, which is only compatible with MySQL and SQLite.

**How is the config loaded?**

The config services are defined in the [services.yaml](config/services.yaml) file.

The config is loaded with the `load` method of the [ConfigLoader](src/Config/ConfigLoader.php) service.
It has the following dependencies:

- [Config](src/Config/Config.php): contains the parsed config
- [ConfigParser](src/Config/ConfigParser.php): reads YAML files and returns the data
- [PathResolver](src/Config/Resolver/PathResolver.php): resolves the path of the config files (`~` character, realpath...)

At the end of the process, the [Config](src/Config/Config.php) object is filled with the config data.

The config data is then [validated against a JSON schema](src/Config/Validator/JsonSchemaValidator.php).

The dumper uses this configuration data to initialize its own configuration object: [DumperConfig](src/Dumper/Sql/DumperConfig.php).
This allows to use getters/setters for each config value.

## Roadmap

Done / mostly done:

- Console command application
- Dependency injection
- Config files
- Overriding config files
- Schema validation with [JSON schema](https://github.com/justinrainbow/json-schema/)
- Phar file creation (with `bin/compile`)
- SQL Dumper, with [mysqldump-php](https://github.com/ifsnop/mysqldump-php)
- Value generators, with [Faker](https://github.com/fzaninotto/Faker/), or custom
- Wildcard character "*" in table names (only compatible with `ignore` and `truncate` characters)
- Config per framework version (e.g. Magento 2.3)

WIP:

- Config templates (Magento, Drupal)
- Documentation (in proper english)

TODO:

- Filter table data (e.g. dump only 1000 rows)
- Find a package name
- Tests

## Contributing

You can contribute to this module by submitting issues or pull requests.

For more details, please take a look at the [contribution guidelines](CONTRIBUTING.md).

## License

[TODO - determine the license](LICENSE.md)

## Changelog

All notable changes are recorded in this [changelog](CHANGELOG.md).

## Contact

Guillaume Vrac <dirtech@smile.fr>
