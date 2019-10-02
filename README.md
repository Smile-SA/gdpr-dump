# GdprDump

## Description

This tool provides a command that dumps the contents of a database to the specified output (e.g. dump file).

It is the equivalent of mysqldump, with additional features, at the cost of performance (PHP implementation).
The main purpose of this tool is to create anonymized dumps, in order to comply with GDPR regulations.

**It is still under development (beta). There is no stable version yet.**

You can use a config file to specify how the database should be dumped.
In the config file, you can:

- specify data converters that allow you to transform the data before it is dumped to the file.
  It can be used to create an anonymize
  d dump file.
- specify table filters.
- specify a list of tables to whitelist (only these tables will be included in the dump).
- specify a list of tables to blacklist (not included in the dump).
- specify the database connection info (host, user, password...).
- specify dump options (compression, output type...).

## Prerequisites

Requirements:

- PHP >= 7.0
- MySQL, or one of its variants (MariaDB, Percona)

If you use a PHP version < 7.0, you need to upgrade to a [supported version of PHP](http://php.net/supported-versions.php).
Each release branch of PHP is supported for 3 years (2 years of full support, then 1 year of security support).

## Installation

**With Composer**

This tool is designed to be used as a standalone application.
It can be installed with the following command:

```php
composer create-project --no-dev --stability=beta --prefer-dist smile/gdpr-dump
```

Important: your composer configuration must include [Smile repositories](https://wiki.smile.fr/view/PHP/HowToConfigComposer).

**Phar File**

Alternatively, you can download a PHAR file [here](http://packages.vitry.intranet/products/smile/gdpr-dump/gdpr-dump-1.0.0-beta10.phar).

## Documentation

Table of contents:

1. [Basic Usage](docs/01-commands.md)
2. [Configuration](docs/02-configuration.md)
3. [Converters](docs/03-converters.md)
4. [Guidelines](docs/04-guidelines.md)

Also, there are multiple examples of config files in the config/templates directory.

## FAQ

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
The dump file is generated with [MySQLDump-PHP](https://github.com/ifsnop/mysqldump-php) instead, which is only compatible with MySQL.
Doctrine is used by this tool, but only to detect the dependencies between tables (foreign keys).

## Contributing

You can contribute to this module by submitting issues or pull requests.

For more details, please take a look at the [contribution guidelines](CONTRIBUTING.md).

## License

This project is licensed under the [GPLv3 License](LICENSE.md).

## Changelog

All notable changes are recorded in this [changelog](CHANGELOG.md).

## Contact

Guillaume Vrac <dirtech@smile.fr>
