# Changelog

All notable changes to this project will be documented in this file.

## WIP

- Add a "min_length" parameter in the RandomizeText and RandomizeEmail converters.

## [1.0.0-beta12] - 2019-10-21
[1.0.0-beta12]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta11...1.0.0-beta12

- Fix converter condition sometimes parsed incorrectly

## [1.0.0-beta11] - 2019-10-10
[1.0.0-beta11]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta10...1.0.0-beta11

- Allow to define and use SQL variables
- Better converter condition parsing (tokens + regexp)

## [1.0.0-beta10] - 2019-10-02
[1.0.0-beta10]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta9...1.0.0-beta10

- Revert to PSR-2 coding standard (PSR-12 is not stable enough)
- Refactor the `isAbsolutePath` method of the config path resolver
- Update the contribution guidelines.

## [1.0.0-beta9] - 2019-08-26
[1.0.0-beta9]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta8...1.0.0-beta9

- **Compatibility break**:
    - Move bin/console to bin/gdpr-dump
    - The console application now runs as a single command application
    - Move the configuration to the "app" directory
- Paths specified in the `extends` parameter are now relative to the current configuration file (instead of the current working directory)
- Allow integer and boolean types in PDO settings
- Split functional and unit tests into two tests suites
- Use the PSR-12 coding standard (instead of PSR-2)
- Update the contribution guidelines

## [1.0.0-beta8] - 2019-08-19
[1.0.0-beta8]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta7...1.0.0-beta8

- Add `--port` option in the dump command
- Documentation improvements

## [1.0.0-beta7] - 2019-07-31
[1.0.0-beta7]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta6...1.0.0-beta7

- Fix errors detected by the "MissingImport" PHPMD rule
- Add `ext-json` requirement in composer.json

## [1.0.0-beta6] - 2019-07-16
[1.0.0-beta6]: https://git.smile.fr/dirtech/gdpr-dump/compare/1.0.0-beta5...1.0.0-beta6

- Anonymize `password_hash` customer attribute in magento 1
- Add documentation about the `chain` converter
- Allow disabling converters that are defined as parameters of other converters
- Add `setPrefix` and `setSuffix` converters

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
