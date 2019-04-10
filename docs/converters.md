# Available Converters

## [faker](src/Converter/Faker.php)

The `faker` converter allows to use any formatter defined in the [Faker](https://github.com/fzaninotto/Faker) library.

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

## [anonymizeText](src/Converter/Anonymize/AnonymizeText.php)

The `anonymizeText` converter anonymizes any string.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **method** | N | `'obfuscate'`| `obfuscate`: converts all characters to `*`, except the first character of each word<br>`replace`: replaces all word characters by random characters |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'anonymizeText'
```

Example with parameters:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeText'
                parameters: {method: 'replace'}
```

## [anonymizeNumber](src/Converter/Anonymize/AnonymizeNumber.php)

The `anonymizeNumber` converter anonymizes any number.
It converts all alphanumeric characters to random numbers.

For example, it converters "number_123456" to "945694_086714"

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'anonymizeNumber'
```

## [anonymizeEmail](src/Converter/Anonymize/AnonymizeEmail.php)

The `anonymizeEmail` converter anonymizes emails.
It converts all alphanumeric characters to random alphanumeric characters, and generates a random email domain.

Parameters:

| Name | Required | Default | Description |
| --- | --- | --- | --- |
| **method** | N | `'obfuscate'`| `obfuscate`: converts all characters to `*`, except the first character of each word<br>`replace`: replaces all word characters by random characters |
| **domains** | N | `['example.com', 'example.net', 'example.org']` | A list of email domains. |

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'anonymizeEmail'
```

Example with parameters:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeEmail'
                unique: true
                parameters: {method: 'replace', domains: ['smile.fr', 'smile.eu']}
```

## [setNull](src/Converter/Setter/SetNull.php)

The `setNull` converter forces all values to `null`.

Example:

```yaml
tables:
    my_table:
        converters:
            my_column: 'setNull'
```

## [setValue](src/Converter/Setter/SetValue.php)

The `setValue` converter forces all values to the specified value.

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
