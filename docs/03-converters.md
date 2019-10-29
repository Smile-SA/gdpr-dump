# Data Converters

## Table of Contents

- [faker](#user-content-faker)
- [anonymizeText](#user-content-anonymizetext)
- [anonymizeNumber](#user-content-anonymizenumber)
- [anonymizeEmail](#user-content-anonymizeemail)
- [anonymizeDate](#user-content-anonymizedate)
- [anonymizeDateTime](#user-content-anonymizedatetime)
- [randomizeText](#user-content-randomizetext)
- [randomizeNumber](#user-content-randomizenumber)
- [randomizeEmail](#user-content-randomizeemail)
- [randomizeDate](#user-content-randomizedate)
- [randomizeDateTime](#user-content-randomizedatetime)
- [setNull](#user-content-setnull)
- [setValue](#user-content-setvalue)
- [setPrefix](#user-content-setprefix)
- [setSuffix](#user-content-setsuffix)
- [jsonData](#user-content-jsondata)
- [serializedData](#user-content-serializeddata)
- [chain](#user-content-chain)

## [faker](src/Converter/Faker.php)

Allows to use any formatter defined in the [Faker](https://github.com/fzaninotto/Faker) library.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **formatter** | Y | | The formatter name. |
| **arguments** | N | `[]` | The formatter arguments. |

Example without arguments:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'faker'
                parameters: {formatter: 'safeEmail'}
```

Example with arguments:

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

## [anonymizeText](src/Converter/Anonymizer/AnonymizeText.php)

Anonymizes string values by replacing all characters by the `*` character.
The first letter of each word is preserved.
The word separators are ` ` (space), `_` (underscore) and `.` (dot).

For example, it converts "john.doe" to "j\*\*\*.d\*\*".

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeText'
```

## [anonymizeNumber](src/Converter/Anonymizer/AnonymizeNumber.php)

Anonymizes numeric values by replacing all numbers by the `*` character.
The first digit of each number is preserved.

For example, it converts "user123" to "user1\*\*".

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeNumber'
```

## [anonymizeEmail](src/Converter/Anonymizer/AnonymizeEmail.php)

Same as `anonymizeText`, but it doesn't obfuscate the email domain.
The email domain is replaced by a safe domain.

For example, one of the possible conversions for "user1@gmail.com" is "u\*\*\*\*@example.org".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeEmail'
```

## [anonymizeDate](src/Converter/Anonymizer/AnonymizeDate.php)

Anonymizes date values.
It can be used to anonymize a date of birth.

The day and month are randomized, the year is not changed.

For example, one of the possible conversions for "1990-01-01" is "1990-11-25".

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

## [anonymizeDateTime](src/Converter/Anonymizer/AnonymizeDateTime.php)

This is exactly the same as the `anonymizeDate` converter, but the default value of the format parameter is `Y-m-d H:i:s` instead of `Y-m-d`.

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

## [randomizeText](src/Converter/Randomizer/RandomizeText.php)

Converts all alphanumeric characters to random alphanumeric characters.

For example, one of the possible convertions for "john_doe" is "vO7s2pJx".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **min_length** | N | 3 | The minimum length of the generated value. |
| **replacements** | N | [Check here](src/Converter/Randomizer/RandomizeText.php) | A string that contains the replacement characters. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeText'
```

## [randomizeNumber](src/Converter/Randomizer/RandomizeNumber.php)

Converts all numeric characters to random numbers.

For example, one of the possible conversions for "number_123456" is "number_086714"

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeNumber'
```

## [randomizeEmail](src/Converter/Randomizer/RandomizeEmail.php)

Same as `randomizeText`, but it doesn't randomize the email domain.
The email domain is replaced by a safe domain.

For example, one of the possible conversions for "user1@gmail.com" is "Jv4oq@example.org".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |
| **min_length** | N | 3 | The minimum length of the generated username. |
| **replacements** | N | [Check here](src/Converter/Randomizer/RandomizeText.php) | A string that contains the replacement characters. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeEmail'
```

## [randomizeDate](src/Converter/Randomizer/RandomizeDate.php)

Generates a random date.

For example, one of the possible conversions for "1990-01-01" is "2002-01-20".

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
                converter: 'randomizeDate'
```

## [randomizeDateTime](src/Converter/Randomizer/RandomizeDateTime.php)

Generates a random date time.

For example, one of the possible conversions for "1990-01-01 00:00:00" is "2002-01-20 23:05:49".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **format** | N | `'Y-m-d H:i:s'` | The date format. |
| **min_year** | N | `1900` | The min year. If set to `null`, the min year is the current year. |
| **max_year** | N | `null` | The max year. If set to `null`, the max year is the current year. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeDateTime'
```

## [setNull](src/Converter/Setter/SetNull.php)

Converts all values to `null`.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'setNull'
```

## [setValue](src/Converter/Setter/SetValue.php)

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
                parameters: {value: 0}
```

## [setPrefix](src/Converter/Setter/SetPrefix.php)

This converter adds a prefix to every value.

For example, the value `value` is converted to `anonymized_value` if the prefix is `anonymized_`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **prefix** | Y | | The prefix to add. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'setPrefix'
                parameters: {prefix: 'test_'}
```

## [setSuffix](src/Converter/Setter/SetSuffix.php)

This converter adds a suffix to every value.

For example, the value `value` is converted to `value_anonymized` if the suffix is `_anonymized_`.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **suffix** | Y | | The suffix to add. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'setSuffix'
                parameters: {suffix: '_test'}
```

## [jsonData](src/Converter/Proxy/JsonData.php)

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
                        customer.email: 'anonymizeEmail'
                        customer.username: 'anonymizeText'
```

## [serializedData](src/Converter/Proxy/SerializedData.php)

Same as `jsonData` converter, but works with serialized data instead.

The serialized data must be an array.

## [chain](src/Converter/Proxy/Chain.php)

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
