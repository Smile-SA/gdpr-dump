# Configuration

## Format

Configuration files must use the YAML format.

## Override Another Config File

Your config file can automatically inherit the contents of another config file, by specifying the following parameter:

```yaml
extends: path/to/config/file.yaml
```

There are default config templates available in the config/templates directory of the application:

- drupal8.yaml
- magento1.yaml
- magento2.yaml

These templates can be used to anonymize a drupal/magento database.

If you override a default template, the path to the file can be omitted:

```yaml
extends: magento2
```

It is also possible to override multiple config files:

```yaml
extends:
  - magento2
  - path/to/config/file.yaml
```

The files will be loaded sequentially:

1. config/templates/magento2.yml
2. path/to/config/file.yml
3. your config file

## Database Settings

The database information can be specified in the `dababase` object:

```yaml
database:
    driver: mysql
    host: my_host
    port: 3306
    user: my_user
    password: my_password
    name: my_db_name
    pdo_settings:
        MY_PDO_SETTING: some_value
```

Default values:

- driver: `mysql`
- host: `localhost`
- user: `root`
- port: `3306`

Available drivers:

- `mysql`

## Dump Settings

```yaml
dump:
    output: my_dump_file.sql
    settings:
        compress: true
        # TODO list of all settings in a table
```

Default values:

- output: `php://stdout`

## Tables to Ignore

You can specify tables to not include in the dump:

```yaml
tables:
    my_table:
        ignore: true
```

## Tables to Truncate

You can specify tables to include without any data (no insert query):

```yaml
tables:
    my_table:
        truncate: true
```

## Filtering Values

**This feature is not yet implemented.**

It will be possible to limit the data dumped for each table:

```yaml
tables:
    my_table:
        limit: 10000
```

Available properties:

- `limit`: to limit the number of values to dump
- `direction`: `asc` or `desc`
- `filters`: filters applied to the table data

How to define a filter:

```yaml
tables:
    my_table:
        filters:
            - ['id', 'gt', 1000]
            - ['sku', 'isNotNull']
            - ['type', 'in', ['simple', 'configurable']]
```

Available filters:

- `eq` (equal to)
- `gt` (greater than)
- `lt` (less than)
- `ge` (greater than or equal to)
- `le` (less than or equal to)
- `like`
- `notLike`
- `isNull`
- `isNotNull`
- `in`
- `notIn`

The data is automatically filtered for all tables that depend on the target table (foreign keys).

## Table Whitelist

**This feature is not yet implemented.**

You will be able to specify a table whitelist.
If a whitelist is defined, only the tables included in the whitelist will be dumped.

All other tables will be ignored, even if they are mentioned in the config file(s).

```yaml
table_whitelist:
    - customers
    - transactions
```

## Data Converters

It is possible to define data converters for any column.

Short syntax:

```yaml
tables:
    my_table:
        converters:
            my_column: 'anonymizeEmail'
```

The key is the column name, the value is the converter name.

Extended syntax:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeEmail'
                unique: true
```

The key is the column name, the value is the converter definition.

List of available properties:

| Property | Required | Default | Description |
| --- | --- | --- | --- |
| **converter** | Y | | Converter name. A list of all converters [is available here](converters.md). |
| **condition** | N | `''` | A PHP expression that must evaluate to `true` or `false`. The value is converted if the expression returns `true`. |
| **parameters** | N | `{}` | e.g. `min` and `max` for `numberBetween`. Most converters don't accept any parameter. |
| **unique** | N | `false` | Whether to generate only unique values. May result in a fatal error with converters that can't generate enough unique values. |

How to use parameters:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'numberBetween'
                parameters: {min: 0, max: 100}
```

How to define a condition:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'anonymizeEmail'
                condition: '{{another_column}} !== null'
```

The filter is a PHP expression.
Variables must be encapsed by double brackets.

The available variables are the columns of the table.
For example, if the table has a `id` column, the `{{id}}` variable will be available.
