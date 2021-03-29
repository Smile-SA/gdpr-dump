# Data Converters

## Table of Contents

- [Anonymizers](#anonymizers)
    - [anonymizeText](#anonymizetext)
    - [anonymizeEmail](#anonymizeemail)
    - [anonymizeNumber](#anonymizenumber)
    - [anonymizeDate](#anonymizedate)
    - [anonymizeDateTime](#anonymizedatetime)
- [Randomizers](#randomizers)
    - [randomizeText](#randomizetext)
    - [randomizeEmail](#randomizeemail)
    - [randomizeNumber](#randomizenumber)
- [Generators](#generators)
    - [randomText](#randomtext)
    - [randomEmail](#randomemail)
    - [randomDate](#randomdate)
    - [randomDateTime](#randomdatetime)
    - [numberBetween](#numberbetween)
    - [setNull](#setnull)
    - [setValue](#setvalue)
- [Transformers](#transformers)
    - [toLower](#tolower)
    - [toUpper](#toupper)
    - [prependText](#prependtext)
    - [appendText](#appendtext)
    - [replace](#replace)
    - [regexReplace](#regexreplace)
    - [hash](#hash)
- [Advanced Converters](#advanced-converters)
    - [faker](#faker)
    - [chain](#chain)
    - [jsonData](#jsondata)
    - [serializedData](#serializeddata)
    - [fromContext](#fromcontext)
- [Deprecated Converters](#deprecated-converters)
    - [addPrefix](#addprefix)
    - [addSuffix](#addsuffix)
    - [randomizeDate](#randomizedate)
    - [randomizeDateTime](#randomizedatetime)

## Anonymizers

These converters anonymize an input value.
Empty values are not converted.

### [anonymizeText](../src/Converter/Anonymizer/AnonymizeText.php)

Anonymizes string values by replacing all characters with the `*` character.
The first letter of each word is preserved.
The default word separators are ` ` (space), `_` (underscore) and `.` (dot).

For example, it converts "John Doe" to "J\*\*\* D\*\*".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **replacement** | N | `'*'` | The replacement character. |
| **delimiters** | N | `[' ', '_', '-', .']` | The word separator characters. |
| **min_word_length** | N | `3` | The minimum length per anonymized word. Useful only if at least one word separator is defined. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeText'
```

### [anonymizeEmail](../src/Converter/Anonymizer/AnonymizeEmail.php)

Applies the following transformations on the input value:

- Applies the `anonymizeText` converter on the username part.
- Replaces the domain (if any) by a safe one.

For example, one of the possible conversions for "user1@gmail.com" is "u\*\*\*\*@example.org".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |
| **replacement** | N | `'*'` | The replacement character. |
| **delimiters** | N | `[' ', '_', '-', '.']` | The word separator characters. |
| **min_word_length** | N | `3` | The minimum length per anonymized word. Useful only if at least one word delimiter is defined. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeEmail'
```

### [anonymizeNumber](../src/Converter/Anonymizer/AnonymizeNumber.php)

Anonymizes numeric values by replacing all numbers with the `*` character.
The first digit of each number is preserved.

For example, it converts "user123" to "user1\*\*".

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **replacement** | N | `'*'` | The replacement character. |
| **min_number_length** | N | `1` | The minimum length per anonymized number  (when not empty). |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeNumber'
```

### [anonymizeDate](../src/Converter/Anonymizer/AnonymizeDate.php)

Anonymizes date values.
It can be used to anonymize a date of birth.

The day and month are randomized.
The year is not changed.
For example, one of the possible conversions for "1990-01-01" is "1990-11-25".

The date format of the input value MUST match the `format` parameter, otherwise an exception is thrown.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **format** | N | `'Y-m-d'` | The date format. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeDate'
```

### [anonymizeDateTime](../src/Converter/Anonymizer/AnonymizeDateTime.php)

Same as `anonymizeDate`, but the default value of the format parameter is `Y-m-d H:i:s` instead of `Y-m-d`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **format** | N | `'Y-m-d H:i:s'` | The date format. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeDateTime'
```

## Randomizers

These converters replace parts of the input value with random characters.
For example, the `randomizeNumber` converter replaces all numeric characters with random numbers.

Only non-empty values are processed.

### [randomizeText](../src/Converter/Randomizer/RandomizeText.php)

Converts all alphanumeric characters to random alphanumeric characters.

For example, one of the possible convertions for "john_doe" is "vO7s2pJx".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **min_length** | N | `3` | The minimum length of the generated value (when not empty). |
| **replacements** | N | [Check here](../src/Converter/Randomizer/RandomizeText.php) | A string that contains the replacement characters. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeText'
```

### [randomizeEmail](../src/Converter/Randomizer/RandomizeEmail.php)

Applies the following transformations on the input value:

- Applies the `randomizeText` converter on the username part.
- Replaces the domain (if any) by a safe one.

For example, one of the possible conversions for "user1@gmail.com" is "Jv4oq@example.org".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |
| **min_length** | N | `3` | The minimum length of the generated username (when not empty). |
| **replacements** | N | [Check here](../src/Converter/Randomizer/RandomizeText.php) | A string that contains the replacement characters. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeEmail'
```

### [randomizeNumber](../src/Converter/Randomizer/RandomizeNumber.php)

Converts all numeric characters to random numbers.
Other characters are not modified.

For example, one of the possible conversions for "number_123456" is "number_086714"

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeNumber'
```

## Generators

These converters generate random values.

### [randomText](../src/Converter/Generator/RandomText.php)

Generates a random text value.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **min_length** | N | `3` | The minimum length of the generated value. |
| **max_length** | N | `16` | The minimum length of the generated value. |
| **characters** | N | [Check here](../src/Converter/Generator/RandomText.php) | A string that contains the characters used to generate the value. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomText'
                parameters:
                    min_length: 0
                    max_length: 10
```

### [randomEmail](../src/Converter/Generator/RandomEmail.php)

Generates a random email address.
The username part of the email is generated with the `randomText` converter.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |
| **min_length** | N | `3` | The minimum length of the username. |
| **max_length** | N | `16` | The minimum length of the username. |
| **characters** | N | [Check here](../src/Converter/Generator/RandomText.php) | A string that contains the characters used to generate the username. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomEmail'
```

### [randomDate](../src/Converter/Generator/RandomDate.php)

Generates a random date (e.g. `2005-08-03`).

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **format** | N | `'Y-m-d'` | The date format. |
| **min_year** | N | `1900` | The min year. If set to `null`, the min year is the current year. |
| **max_year** | N | `null` | The max year. If set to `null`, the max year is the current year. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomDate'
                parameters:
                    min_year: 2000
                    max_year: 2050
```

### [randomDateTime](../src/Converter/Generator/RandomDateTime.php)

Same as `randomDate`, but the default value of the format parameter is `Y-m-d H:i:s` instead of `Y-m-d`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **format** | N | `'Y-m-d'` | The date format. |
| **min_year** | N | `1900` | The min year. If set to `null`, the min year is the current year. |
| **max_year** | N | `null` | The max year. If set to `null`, the max year is the current year. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomDateTime'
                parameters:
                    min_year: 2000
                    max_year: 2050
```

### [numberBetween](../src/Converter/Generator/NumberBetween.php)

Generates a number between a min and a max value.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **min** | Y | | The min value. |
| **max** | Y | | The max value. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'numberBetween'
                parameters:
                    min: 0
                    max: 100
```

### [setNull](../src/Converter/Generator/SetNull.php)

Converts all values to `null`.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'setNull'
```

### [setValue](../src/Converter/Generator/SetValue.php)

This converter always returns the same value.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **value** | Y | | The value to set. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'setValue'
                parameters:
                    value: 0
```

## Transformers

These converters apply transformations on the input value (e.g. converting to lower case).
Empty values are not converted.

### [toLower](../src/Converter/Transformer/ToLower.php)

Converts all characters to lower case.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'toLower'
```

### [toUpper](../src/Converter/Transformer/ToUpper.php)

Converts all characters to upper case.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'toUpper'
```

### [prependText](../src/Converter/Transformer/PrependText.php)

This converter adds a prefix to every value.

For example, the value `user1` is converted to `test_user1` if the prefix is `test_`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **value** | Y | | The value to prepend. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'prependText'
                parameters:
                    value: 'test_'
```

### [appendText](../src/Converter/Transformer/AppendText.php)

This converter adds a suffix to every value.

For example, the value `user1` is converted to `user1_test` if the suffix is `_test`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **value** | Y | | The value to append. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'appendText'
                parameters:
                    value: '_test'
```

### [replace](../src/Converter/Transformer/Replace.php)

This converter replaces all occurrences of the search string with the replacement string.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **search** | Y | | The text to replace. |
| **replacement** | N | '' | The replacement text. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'replace'
                parameters:
                    search: 'bar'
                    replacement: 'baz'
```

### [regexReplace](../src/Converter/Transformer/RegexReplace.php)

This converter performs a regular expression search and replace.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **pattern** | Y | | The pattern to find. |
| **replacement** | N | '' | The replacement text. |
| **limit** | N | -1 | The max number of replacements to perform. No limit if set to -1 (default value). |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'regexReplace'
                parameters:
                    pattern: '/[0-9]+/'
                    replacement: '15'
```

### [hash](../src/Converter/Transformer/Hash.php)

This converter applies a hash algorithm on the value.

The default algorithm is `sha1`.

Any algorithm returned by the function [hash_algos](https://www.php.net/manual/en/function.hash-algos.php) can be used.
Examples: md5, sha1, sha256, sha512, crc32.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **algorithm** | Y | `'sha1'` | The algorithm to use. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'hash'
                parameters:
                    algorithm: 'sha256'
```

## Advanced Converters

### [faker](../src/Converter/Proxy/Faker.php)

Allows to use any formatter defined in the [Faker](https://github.com/FakerPHP/Faker) library.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **formatter** | Y | | The formatter name. |
| **arguments** | N | `[]` | The formatter arguments. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'faker'
                parameters:
                    formatter: 'numberBetween'
                    arguments: [1, 100]
```

To use a formatter that requires the original value as an argument, you can use the `{{value}}` placeholder:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'faker'
                parameters:
                    formatter: 'shuffle'
                    arguments: ['{{value}}']
```

The faker locale can be set in the [configuration file](01-configuration.md#faker-locale) and defaults to `en_US`.

## [chain](../src/Converter/Proxy/Chain.php)

This converter executes a list of converters.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **converters** | Y | | A list of converter definitions. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'chain'
                parameters:
                    converters:
                        - converter: 'anonymizeText'
                          condition: 'another_column == 0'
                        - converter: 'randomizeText'
                          condition: 'another_column == 1'
```

If you need to override a chained converter defined in a parent config file, you must specify the key index.
For example, to disable the 2nd converter of a chain:

```yaml
tables:
    my_table:
        converters:
            my_column:
                parameters:
                    converters:
                        1:
                            disabled: true
```

## [jsonData](../src/Converter/Proxy/JsonData.php)

This converter can be used to anonymize data that are stored in a JSON object.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **converters** | Y | | A list of converter definitions. The key of each converter definition is the path to the value within the JSON object. |

For example, if the following JSON data is stored in a column:

`{"customer":{"email":"john.doe@example.com","username":"john.doe"}}`

The following converter can be used:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'jsonData'
                parameters:
                    converters:
                        customer.email:
                            converter: 'anonymizeEmail'
                        customer.username:
                            converter: 'anonymizeText'
```

## [serializedData](../src/Converter/Proxy/SerializedData.php)

Same as `jsonData` converter, but works with serialized data instead.

The serialized data must be an array.

## [fromContext](../src/Converter/Proxy/FromContext.php)

This converter returns a value from the `$context` array passed to converters.

The context array contains the following data:

- `row_data`: an array containing the value of each column of the table row
- `processed_data`: an array containing the values of the row that were transformed by a converter

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **key** | Y | | The key associated to the value to retrieve in the context array. |

Example:

```yaml
tables:
    my_table:
        converters:
            email:
                converter: 'randomizeEmail'
            email_lowercase:
                converter: 'chain'
                parameters:
                    converters:
                        - converter: 'fromContext'
                          parameters:
                              key: 'processed_data.email'
                        - converter: 'toLower'
```

# Deprecated Converters

These converters are deprecated.
They will be removed from the next major release of GdprDump.

## addPrefix

This converter is **deprecated**, use `prependText` instead.

## addSuffix

This converter is **deprecated**, use `appendText` instead.

## randomizeDate

This converter is **deprecated**, use `randomDate` instead.

## randomizeDateTime

This converter is **deprecated**, use `randomDateTime` instead.
