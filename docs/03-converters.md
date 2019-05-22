# Available Converters

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

## [faker](src/Converter/Faker.php) short syntax

A shorter syntax can be used to define a Faker converter.

```yaml
tables:
    my_table:
        converters:
            my_column: 'safeEmail'
```

How it works: if the specified converter does not exist, it automatically tries to fallback to a Faker converter, with the converter name used as the Faker formatter name.

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
            my_column: 'anonymizeText'
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
            my_column: 'anonymizeNumber'
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
            my_column: 'anonymizeEmail'
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
            my_column: 'anonymizeDate'
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
            my_column: 'anonymizeDateTime'
```

## [obfuscateText](src/Converter/Anonymizer/ObfuscateText.php)

Converts all alphanumeric characters to random alphanumeric characters.

For example, one of the possible convertions for "john_doe" is "vO7s2pJx".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **replacements** | N | [Check here](src/Converter/Anonymizer/ObfuscateText.php) | A string that contains the replacements characters. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'obfuscateText'
```

## [obfuscateNumber](src/Converter/Anonymizer/ObfuscateNumber.php)

Converts all numeric characters to random numbers.

For example, one of the possible conversions for "number_123456" is "number_086714"

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'obfuscateNumber'
```

## [obfuscateEmail](src/Converter/Anonymizer/ObfuscateEmail.php)

Same as `obfuscateText`, but it doesn't obfuscate the email domain.
The email domain is replaced by a safe domain.

For example, one of the possible conversions for "user1@gmail.com" is "Jv4oq@example.org".

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **replacements** | N | [Check here](src/Converter/Anonymizer/ObfuscateText.php) | A string that contains the replacements characters. |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'obfuscateEmail'
```

## [setNull](src/Converter/Setter/SetNull.php)

Converts all values to `null`.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'setNull'
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
