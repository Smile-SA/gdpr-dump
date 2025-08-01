# GdprDump

[![Latest Stable Version](https://poser.pugx.org/smile/gdpr-dump/v/stable)](https://packagist.org/packages/smile/gdpr-dump)
![Build Status](https://github.com/Smile-SA/gdpr-dump/workflows/Tests/badge.svg)

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

## Documentation

The documentation (including installation instructions) is available in the [wiki](https://github.com/Smile-SA/gdpr-dump/wiki).

## Community Templates

While it is not the aim of this project to cover each framework, you are encouraged to publish your templates on GitHub under the topic [smile-sa-gdpr-dump-template](https://github.com/topics/smile-sa-gdpr-dump-template).

## Contributing

You can contribute to this module by submitting issues or pull requests.

For more details, please take a look at the [contribution guidelines](CONTRIBUTING.md).

## License

This project is licensed under the [GPLv3 License](LICENSE.md).

## Changelog

All notable changes are recorded in this [changelog](CHANGELOG.md).
