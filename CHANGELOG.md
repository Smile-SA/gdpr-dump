# Changelog

All notable changes to this project will be documented in this file.

## [5.0.5] - 2025-04-05
[5.0.5]: https://github.com/Smile-SA/gdpr-dump/compare/5.0.4...5.0.5

- Fix database url param not being compatible with env vars ([#157](https://github.com/Smile-SA/gdpr-dump/pull/157))

## [5.0.4] - 2025-04-03
[5.0.4]: https://github.com/Smile-SA/gdpr-dump/compare/5.0.3...5.0.4

- Added optional `database.url` config parameter ([#147](https://github.com/Smile-SA/gdpr-dump/pull/147))

## [5.0.3] - 2025-02-27
[5.0.3]: https://github.com/Smile-SA/gdpr-dump/compare/5.0.2...5.0.3

- Added forbidden statements to the query validator: `begin`, `call`, `check`, `checksum`, `do`, `end`, `import`, `insert`, `replace`, `restart`, `stop` ([#153](https://github.com/Smile-SA/gdpr-dump/pull/153))

## [5.0.2] - 2024-12-02
[5.0.2]: https://github.com/Smile-SA/gdpr-dump/compare/5.0.1...5.0.2

- Removed converter for "email_imported" column from magento2 template file ([#144](https://github.com/Smile-SA/gdpr-dump/pull/144))

## [5.0.1] - 2024-07-10
[5.0.1]: https://github.com/Smile-SA/gdpr-dump/compare/5.0.0...5.0.1

- Fixed invalid column name in the magento2 template ([#141](https://github.com/Smile-SA/gdpr-dump/pull/141))

## [5.0.0] - 2024-07-01
[5.0.0]: https://github.com/Smile-SA/gdpr-dump/compare/4.2.2...5.0.0

New features:

- Added command-line options to specify database credentials: `--host`, `--port`, `--user`, `--password`, `--database` ([#135](https://github.com/Smile-SA/gdpr-dump/pull/135))
- Added command-line option to perform a dry-run: `--dry-run` ([#137](https://github.com/Smile-SA/gdpr-dump/pull/137))

Breaking changes:

- GdprDump now throws an exception if a config file contains an undefined column ([#125](https://github.com/Smile-SA/gdpr-dump/pull/125))
- Removed support of the `filters` parameter. Use the `where` parameter instead ([#128](https://github.com/Smile-SA/gdpr-dump/pull/128))
- Removed undefined column customer_address.vat_id from shopware6 template ([#132](https://github.com/Smile-SA/gdpr-dump/pull/132))
- Stricter config file validation: string parameters don't accept integer values anymore ([#129](https://github.com/Smile-SA/gdpr-dump/pull/129))

## [4.2.2] - 2024-03-26
[4.2.2]: https://github.com/Smile-SA/gdpr-dump/compare/4.2.1...4.2.2

- Changed `limit` parameter type from `int` to `int|null` ([#126](https://github.com/Smile-SA/gdpr-dump/pull/126))
- Removed `orderBy` param from schema.json ([#127](https://github.com/Smile-SA/gdpr-dump/pull/127))

## [4.2.1] - 2024-03-07
[4.2.1]: https://github.com/Smile-SA/gdpr-dump/compare/4.2.0...4.2.1

- Use a readonly connection to create the dump ([#121](https://github.com/Smile-SA/gdpr-dump/pull/121))

## [4.2.0] - 2024-03-05
[4.2.0]: https://github.com/Smile-SA/gdpr-dump/compare/4.1.1...4.2.0

- Drastically improved dump performance ([#117](https://github.com/Smile-SA/gdpr-dump/pull/117))
- Added `where` parameter and deprecated `filters` parameter ([#116](https://github.com/Smile-SA/gdpr-dump/pull/116))
- Write dump information and dump progress bar to stderr when verbose mode is enabled ([#113](https://github.com/Smile-SA/gdpr-dump/pull/113))
- Better converter condition validation by using a php tokenizer ([#114](https://github.com/Smile-SA/gdpr-dump/pull/114))

**WARNING**: the `filters` parameter is now **deprecated**.
It will be removed in the next major version.
Use the `where` parameter instead to apply table filters.

## [4.1.1] - 2024-02-20
[4.1.1]: https://github.com/Smile-SA/gdpr-dump/compare/4.1.0...4.1.1

- Display the table/column names when a data converter throws an exception ([#110](https://github.com/Smile-SA/gdpr-dump/pull/110))
- Resolve Faker formatters before dump creation ([#108](https://github.com/Smile-SA/gdpr-dump/pull/108))

## [4.1.0] - 2024-02-19
[4.1.0]: https://github.com/Smile-SA/gdpr-dump/compare/4.0.3...4.1.0

- Added support for SQL expressions as column filter ([#97](https://github.com/Smile-SA/gdpr-dump/pull/97))
- GdprDump now uses composer to determine the application version

## [4.0.3] - 2024-01-09
[4.0.3]: https://github.com/Smile-SA/gdpr-dump/compare/4.0.2...4.0.3

- Added shopware6 template ([#92](https://github.com/Smile-SA/gdpr-dump/pull/92))
- Replaced "randomizeText" converter with "anonymizeText" in config templates ([#93](https://github.com/Smile-SA/gdpr-dump/pull/93))
- Updated magento2 template (tables added: "integration", "rating_option_vote", "magento_login_as_customer_log")

## [4.0.2] - 2023-10-10
[4.0.2]: https://github.com/Smile-SA/gdpr-dump/compare/4.0.1...4.0.2

- Fix phar file compilation error that appeared after the release of symfony/console v6.2.10
- `requires_version` param was removed from base templates (except magento2)

## [4.0.1] - 2023-03-29
[4.0.1]: https://github.com/Smile-SA/gdpr-dump/compare/4.0.0...4.0.1

- Better error message when a converter is declared without a name ([#83](https://github.com/Smile-SA/gdpr-dump/issues/83))

## [4.0.0] - 2023-01-10
[4.0.0]: https://github.com/Smile-SA/gdpr-dump/compare/3.1.1...4.0.0

- Set minimum PHP version to 8.1
- Replace `ifsnop/mysqldump-php` package with `druidfi/mysqldump-php` ([#78](https://github.com/Smile-SA/gdpr-dump/issues/78))

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
