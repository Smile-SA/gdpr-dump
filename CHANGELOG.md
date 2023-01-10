# Changelog

All notable changes to this project will be documented in this file.

## [4.0.0] - 2023-01-10
[4.0.0]: https://github.com/Smile-SA/gdpr-dump/compare/3.1.1...4.0.0

- Set minimum PHP version to 8.1
- Replace `ifsnop/mysqldump-php` package with `druidfi/mysqldump-php`

## [3.1.1] - 2022-12-13
[3.1.1]: https://github.com/Smile-SA/gdpr-dump/compare/3.1.0...3.1.1

- Disallow phar file compilation when composer dev packages are installed

## [3.1.0] - 2022-08-22
[3.1.0]: https://github.com/Smile-SA/gdpr-dump/compare/3.0.0...3.1.0

Improvements:

- Compatibility with Symfony 6 components
- Better phar file compression (json files are now minified)
- Display an error message if the parameter `database` is not defined

Bugfixes:

- Fix doctrine connection not being closed properly

Refactoring:

- Move the compiler binary to a Symfony command
- Rename SqlDumper class to MysqlDumper to avoid confusion

## [3.0.0] - 2022-07-28
[3.0.0]: https://github.com/Smile-SA/gdpr-dump/compare/2.3.3...3.0.0

- Set minimum PHP version to 7.4
- Upgrade Doctrine DBAL version from `^2.10` to `^3.1`
- Remove deprecated features (see [migration guidelines](docs/03-guidelines.md#migration-guidelines))

## [2.3.3] - 2022-07-28
[2.3.3]: https://github.com/Smile-SA/gdpr-dump/compare/2.3.2...2.3.3

- Replace anonymization character for customer name in magento2 template (fixes a validation error)

## [2.3.2] - 2022-02-21
[2.3.2]: https://github.com/Smile-SA/gdpr-dump/compare/2.3.1...2.3.2

- Fix a regression that appeared in version 2.1.0 and prevented from setting empty strings with the converter "setValue"

## [2.3.1] - 2022-02-14
[2.3.1]: https://github.com/Smile-SA/gdpr-dump/compare/2.3.0...2.3.1

- Fix PHP annotations being removed from the phar file

## [2.3.0] - 2022-01-04
[2.3.0]: https://github.com/Smile-SA/gdpr-dump/compare/2.2.1...2.3.0

- Add settings to enable/disable table filter propagation
- Performance optimization: use a single query to fetch all foreign keys

## [2.2.1] - 2021-11-09
[2.2.1]: https://github.com/Smile-SA/gdpr-dump/compare/2.2.0...2.2.1

- [#57](https://github.com/Smile-SA/gdpr-dump/issues/57): Fix PHP fatal error (memory limit) when tables table depend on each other (cyclic dependency)
- Table dependencies are now properly resolved when a table has two foreign keys that reference the same table

## [2.2.0] - 2021-03-29
[2.2.0]: https://github.com/Smile-SA/gdpr-dump/compare/2.1.1...2.2.0

- Add two converters:
    - `replace` (performs a search and replace)
    - `regexReplace` (performs a regular expression search and replace)
- Use stderr to display error messages
- Magento 2 template: anonymize table "email_sms_order_queue"

## [2.1.1] - 2021-01-25
[2.1.1]: https://github.com/Smile-SA/gdpr-dump/compare/2.1.0...2.1.1

- New parameter `faker.locale` added to the dump configuration file
- The following keywords are now forbidden in the `variables` param: `revoke`, `rename`, `lock`, `unlock`, `optimize`, `repair`
- Replace double quotes by single quotes in SQL queries
- Refactor Mysqldump extensions

## [2.1.0] - 2020-11-10
[2.1.0]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.3...2.1.0

Major changes:

- Add support for PHP 8
- The following converters were renamed (old names are still available, but deprecated):
    - `randomizeDate` -> `randomDate`
    - `randomizeDateTime` -> `randomDateTime`
    - `addPrefix` -> `prependText`
    - `addSuffix` -> `appendText`
- The `orderBy` parameter was renamed to `order_by`.
  The `orderBy` syntax is still supported, but deprecated.
- New options available for the following converters:
    - anonymizeText: `delimiters`, `replacement`, `min_word_length`
    - anonymizeEmail: `delimiters`, `replacement`, `min_word_length`
    - anonymizeNumber: `replacement`, `min_number_length`
- New converters: `randomText`, `randomEmail`, `hash`
- Reduce phar file size by ~70%

Minor fixes / code refactoring:

- Remove disabled converters instead of replacing them by dummy converters
- Replace deprecated Doctrine functions
- Fix conditions not working properly in functional tests
- Move the converter name > classname resolution to a new class named "ConverterResolver"
- Move the ArrayHelper class to the "Util" namespace

## [2.0.3] - 2020-10-05
[2.0.3]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.2...2.0.3

- Allow unsetting values declared in config templates
- Add missing type hint in table filter extension

## [1.2.3] - 2020-10-05
[1.2.3]: https://github.com/Smile-SA/gdpr-dump/compare/1.2.2...1.2.3

- Backport of version 2.0.3

## [2.0.2] - 2020-07-28
[2.0.2]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.1...2.0.2

- Add booted state in AppKernel

## [1.2.2] - 2020-07-28
[1.2.2]: https://github.com/Smile-SA/gdpr-dump/compare/1.2.1...1.2.2

- Backport of version 2.0.2

## [2.0.1] - 2020-07-27
[2.0.1]: https://github.com/Smile-SA/gdpr-dump/compare/2.0.0...2.0.1

- Use `getenv` instead of `$_SERVER` to fetch env vars
- Set default values for environment variables in functional tests
- Remove the option to skip tests that depend on the database

## [1.2.1] - 2020-07-27
[1.2.1]: https://github.com/Smile-SA/gdpr-dump/compare/1.2.0...1.2.1

- Backport of version 2.0.1

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

- Initial release
