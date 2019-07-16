# Changelog

All notable changes to this project will be documented in this file.

WIP:

- Anonymize password_hash customer attribute in magento 1
- Add documentation about the `chain` converter

## [1.0.0-beta5] - 2019-07-09
[1.0.0-beta5]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta4...1.0.0-beta5

- Truncate tables named `*_replica` in magento2 template
- Code cleanup

## [1.0.0-beta4] - 2019-07-08
[1.0.0-beta4]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta3...1.0.0-beta4

- Allow date format in dump output file name
- Fix dump settings in schema.json
- Remove unused `optional` parameter in ConverterFactory
- Generate the phar file in the `build/dist` directory

## [1.0.0-beta3] - 2019-07-01
[1.0.0-beta3]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta2...1.0.0-beta3

- The `version` parameter is now properly parsed if specified in the `additional-config` command option
- Fix wrong property name in magento1 template

## [1.0.0-beta2] - 2019-06-13
[1.0.0-beta2]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta1...1.0.0-beta2

- Use the ipv4 faker formatter in Drupal Commerce tables
- Use the "beta" stability in the composer-create project command

## 1.0.0-beta1 - 2019-06-13

- First version of the tool
