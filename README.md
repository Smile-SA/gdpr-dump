# GdprDump

[![Latest Stable Version](https://poser.pugx.org/smile/gdpr-dump/v/stable)](https://packagist.org/packages/smile/gdpr-dump)
![Build Status](https://github.com/Smile-SA/gdpr-dump/workflows/CI/badge.svg)

This tool provides a command that dumps the contents of a MySQL database.

It is the equivalent of mysqldump, with additional features, at the cost of performance (PHP implementation).
The main purpose of this tool is to create anonymized dumps, in order to comply with GDPR regulations.

Features:

- Data converters (transform the data before it is dumped to the file).
- Recursive table filtering.
- Tables whitelist (only these tables will be included in the dump).
- Tables blacklist (not included in the dump).
- Dump options (compression, output type...).
- Predefined configuration templates (Magento, Drupal, OroCommerce).

## Prerequisites

Requirements:

- PHP >= 7.3
- MySQL or equivalent (MariaDB, Percona)

## Installation

**Phar File**

A phar file is available for downloading in the [releases section](https://github.com/Smile-SA/gdpr-dump/releases).
This is the recommended way to install this application.

The following table lists the requirements of each major version:

**GdprDump Version** | **PHP Requirement**
---|---
2.x (latest) | PHP >= 7.3
1.x | PHP >= 7.0

**With Composer**

It can be installed with the following command:

```
composer create-project --no-dev --prefer-dist smile/gdpr-dump
```

## Getting Started

Command:

```
./gdpr-dump.phar <config_file>...
```

Arguments:

- config_file: path(s) to a [configuration file](docs/01-configuration.md).

The complete list of options can be displayed with the `--help` option.

Configuration file examples:

- You can find a config file example in [app/config/example.yaml](app/config/example.yaml).
- The YAML syntax is also used in the [configuration templates](app/config/templates).

## Documentation

1. [Configuration](docs/01-configuration.md)
2. [Data Converters](docs/02-converters.md)
3. [Guidelines](docs/03-guidelines.md) (read this before deploying the tool to a production server!)

## Community Templates

While it is not the aim of this project to cover each framework, you are encouraged to publish your templates on GitHub under the topic [smile-sa-gdpr-dump-template](https://github.com/topics/smile-sa-gdpr-dump-template).

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

## Contributing

You can contribute to this module by submitting issues or pull requests.

For more details, please take a look at the [contribution guidelines](CONTRIBUTING.md).

## License

This project is licensed under the [GPLv3 License](LICENSE.md).

## Changelog

All notable changes are recorded in this [changelog](CHANGELOG.md).

## Contact

Smile Technical Direction <dirtech@smile.fr>
