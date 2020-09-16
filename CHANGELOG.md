# Changelog

All notable changes to this project will be documented in this file.

## WIP

- Allow unsetting config declared in config templates
- Add missing type hint in table filter extension

## [2.0.2] - 2020-07-28
[2.0.2]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.1...2.0.2

- Add booted state in AppKernel

## [2.0.1] - 2020-07-27
[2.0.1]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.0...2.0.1

- Use `getenv` instead of `$_SERVER` to fetch env vars
- Set default values for environment variables in functional tests
- Remove the option to skip tests that depend on the database

## [2.0.0] - 2020-07-16
[2.0.0]: https://github.com/Smile-SA/gdpr-dump/compare/1.2.0...2.0.0

- Set minimum PHP version to 7.3

## [1.2.0] - 2020-07-03
[1.2.0]: https://github.com/Smile-SA/gdpr-dump/compare/1.1.1...1.2.0

- Allow referencing environment variables in the configuration

## [1.1.1] - 2020-05-13
[1.1.1]: https://github.com/Smile-SA/gdpr-dump/compare/1.1.0...1.1.1

- Optimize the data converter hook (huge performance gain)
- Add PHPStan static code analysis
- Raise minimum required version of `ifsnop/mysqldump-php` to `^2.9`
- Replace `@expectedException` annotation by `$this->expectException()` method
- Add a unit test of the condition builder
- Fix typos in documentation

## [1.1.0] - 2020-03-09
[1.1.0]: https://github.com/Smile-SA/gdpr-dump/compare/1.0.0...1.1.0

- Validate the config file before prompting for a password
- Add parameter `skip_conversion_if` (table row is not converted if the condition evaluates to true) 
- Remove unused parameter `ignore` from schema.json
- Move TableDependencyResolver to the Database namespace
- Add Mysqldump extension logic:
    - ColumnTransformer class converted to DataConverterExtension
    - TableWheresBuilder class converted to TableFilterExtension

## 1.0.0 - 2020-02-05

- Prompt the user for a password if the database password is not set
- Generate only lowercase values in RandomizeText and RandomizeEmail converters
- YAML validation: allow number values when a string is expected
- Replace custom tokenizer implementation by [theseer/tokenizer](https://packagist.org/packages/theseer/tokenizer) package
- Refactored the Dumper\Sql namespace:
    - Smile\GdprDump\Dumper\Sql\Config moved to Smile\GdprDump\Dumper\Config
    - Smile\GdprDump\Dumper\Sql\Tools moved to Smile\GdprDump\Dumper\Tools
    - Smile\GdprDump\Dumper\Sql moved to Smile\GdprDump\Database

## 1.0.0-beta16 - 2019-12-17

- Hopefully the last beta version before release :)
- Remove service autowiring (not phar friendly)
- Minor improvements in the services.yaml file
- Allow PCRE functions (preg_\*), multibyte functions (mb_\*) and "ord" function in converter conditions
- Fix invalid filter condition in example.yaml file

## 1.0.0-beta15 - 2019-11-25

- Enable autowiring in services.yaml
- Better data validation in converter constructors
- Remove group by clause in the mysql metadata class
- Allow additional functions in converter conditions

## 1.0.0-beta14 - 2019-11-18

- **Compatibility break**:
    - Move contents of magento1_* and magento2_* templates to magento1 and magento2 templates
    - Rename setPrefix/setSuffix converters to addPrefix/addSuffix
    - Prevent the use of unsafe statements in var queries (e.g. insert/delete)
- Add oro4 configuration template
- New converters: numberBetween, toLower, toUpper, fromContext
- Move the config version parsing to a VersionLoader implementation
- Add a PHP tokenizer abstraction layer
- Add support for version numbers that contain capitalized letters

## 1.0.0-beta13 - 2019-10-31

This update aims to reduce the complexity of the configuration.
For example, there were two ways to declare a converter, there is now only one.

- **Compatibility break**:
    - Remove driver-specific options from the command-line utility (--database, --host...)
    - Rename `pdo_settings` database parameter to `driver_options`
    - Rename `requiresVersion` parameter to `requires_version`
    - Allow only array type for converter definitions
- Compatibility with databases that use custom Doctrine types
- Allow using multiple configuration files in the command-line
- Allow using driver-specific parameters in the database config
- Add a `min_length` parameter in the RandomizeText and RandomizeEmail converters

## 1.0.0-beta12 - 2019-10-21

- Fix converter condition sometimes parsed incorrectly

## 1.0.0-beta11 - 2019-10-10

- Allow to define and use SQL variables
- Better converter condition parsing (tokens + regexp)

## 1.0.0-beta10 - 2019-10-02

- Revert to PSR-2 coding standard (PSR-12 is not stable enough)
- Refactor the `isAbsolutePath` method of the config path resolver
- Update the contribution guidelines.

## 1.0.0-beta9 - 2019-08-26

- **Compatibility break**:
    - Move bin/console to bin/gdpr-dump
    - The console application now runs as a single command application
    - Move the configuration to the "app" directory
- Paths specified in the `extends` parameter are now relative to the current configuration file (instead of the current working directory)
- Allow integer and boolean types in PDO settings
- Split functional and unit tests into two tests suites
- Use the PSR-12 coding standard (instead of PSR-2)
- Update the contribution guidelines

## 1.0.0-beta8 - 2019-08-19

- Add `--port` option in the dump command
- Documentation improvements

## 1.0.0-beta7 - 2019-07-31

- Fix errors detected by the "MissingImport" PHPMD rule
- Add `ext-json` requirement in composer.json

## 1.0.0-beta6 - 2019-07-16

- Anonymize `password_hash` customer attribute in magento 1
- Add documentation about the `chain` converter
- Allow disabling converters that are defined as parameters of other converters
- Add `setPrefix` and `setSuffix` converters

## 1.0.0-beta5 - 2019-07-09

- Truncate tables named `*_replica` in magento2 template
- Code cleanup

## 1.0.0-beta4 - 2019-07-08

- Allow date format in dump output file name
- Fix dump settings in schema.json
- Remove unused `optional` parameter in ConverterFactory
- Generate the phar file in the `build/dist` directory

## 1.0.0-beta3 - 2019-07-01

- The `version` parameter is now properly parsed if specified in the `additional-config` command option
- Fix wrong property name in magento1 template

## 1.0.0-beta2 - 2019-06-13

- Use the ipv4 faker formatter in Drupal Commerce tables
- Use the "beta" stability in the composer-create project command

## 1.0.0-beta1 - 2019-06-13

- First version of the tool
