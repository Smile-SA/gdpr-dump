# Changelog

All notable changes to this project will be documented in this file.

## WIP

- The following keywords are now forbidden in the `variables` param: `revoke`, `rename`, `lock`, `unlock`, `optimize`, `repair`
- Replace double quotes by single quotes in SQL queries

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
