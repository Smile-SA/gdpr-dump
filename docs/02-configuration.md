# Configuration

## Table of Contents

- [Overriding Configuration](#user-content-overriding-configuration)
- [Application Version](#user-content-application-version)
- [Database Settings](#user-content-database-settings)
- [Dump Settings](#user-content-dump-settings)
- [Tables Configuration](#user-content-tables-configuration)
    - [Tables to Ignore](#user-content-tables-to-ignore)
    - [Tables to Truncate](#user-content-tables-to-truncate)
    - [Filtering Values](#user-content-filtering-values)
    - [Data Converters](#user-content-data-converters)
    - [Sharing Converter Results](#user-content-sharing-converter-results)
- [Version-specific Configuration](#user-content-version-specific-configuration)

## Overriding Configuration

You can create a custom config file that inherits the properties of another config file, by specifying the following parameter:

```yaml
extends: path/to/config/file.yaml
```

If you override a default template, the path to the file and the extension may be omitted:

```yaml
extends: magento2
```

It is also possible to override multiple config files:

```yaml
extends:
  - magento2
  - path/to/config/file.yaml
```

In the above example, the files will be loaded in this order:

1. config/templates/magento2.yml
2. path/to/config/file.yml
3. your config file

## Application Version

Some templates (e.g. `magento2`) require to specify the version of the application.

The version is required if the template file contains the `if_version` parameter (version-specific configuration).

To define the application version:

```yaml
extends: 'magento2'
version: '2.2.8'
```

## Database Settings

The database information can be specified in the `dababase` object:

```yaml
database:
    driver: 'mysql'
    host: 'my_host'
    port: '3306'
    user: 'my_user'
    password: 'my_password'
    name: 'my_db_name'
    pdo_settings:
        MY_PDO_SETTING: 'some_value'
```

Default values:

- driver: `'mysql'`
- host: `'localhost'`
- user: `'root'`
- port: `'3306'`

Available drivers:

- `mysql`

If command-line options are specified (e.g. `--user`), they will have priority over the parameter in the configuration file.

## Dump Settings

```yaml
dump:
    output: 'my_dump_file.sql'
    settings:
        compress: true
```

The default value of `output` is `'php://stdout'`

The dump settings are listed in the [documentation](https://github.com/ifsnop/mysqldump-php/blob/v2.7/README.md#user-content-dump-settings) of the MySQLDump-PHP library.

The default values are the same, except for two options:

- add-drop-table: `true` instead of `false`
- lock-tables: `false` instead of `true`
- hex-blob: `false` instead of `true`

## Tables Configuration

The configuration of each table must be specified in the `tables` parameter.

```yaml
tables:
    table1:
        # ...
    table2:
        # ...
```

The wildcard character `*` can be used in table names (e.g. `cache_*`).

### Tables to Ignore

You can specify tables to not include in the dump:

```yaml
tables:
    my_table:
        ignore: true
```

### Tables to Truncate

You can specify tables to include without any data (no insert query).

```yaml
tables:
    my_table:
        truncate: true
```

If there are tables with foreign keys to this table, they will also be automatically filtered.

### Filtering Values

It is possible to limit the data dumped for each table.

```yaml
tables:
    my_table:
        limit: 10000
```

The data is automatically filtered for all tables that depend on the target table (foreign keys).

Available properties:

- `limit`: to limit the number of rows to dump
- `orderBy`: same as SQL (e.g. `name asc, id desc`)
- `filters`: filters applied to the table data

How to define a sort order:

```yaml
tables:
    my_table:
        orderBy: 'sku, entity_id desc'
        
```

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
- `isNull` (no value)
- `isNotNull` (no value)
- `in` (value must be an array)
- `notIn` (value must be an array)

To use an expression, you can prefix the value by `expr:`:

```yaml
tables:
    my_table:
        filters:
            - ['updated_at', 'gt', 'expr: DATE_SUB(now(), INTERVAL 30 DAY)']
            - ['website_id', 'eq', 'expr: (SELECT website_id FROM store_website WHERE name = "base")']
```

Note: as of now, it is impossible to define expressions with the `in` and `notIn` operators, because the value must be an array of scalar values.

### Data Converters

It is possible to define data converters for any column.

Short syntax:

```yaml
tables:
    my_table:
        converters:
            my_column: 'randomizeEmail'
```

The key is the column name, the value is the converter name.

Extended syntax:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeEmail'
                unique: true
```

The key is the column name, the value is the converter definition.

List of available properties:

| Property | Required | Default | Description |
| --- | --- | --- | --- |
| **converter** | Y | | Converter name. A list of all converters [is available here](04-converters.md). |
| **condition** | N | `''` | A PHP expression that must evaluate to `true` or `false`. The value is converted if the expression returns `true`. |
| **parameters** | N | `{}` | e.g. `min` and `max` for `numberBetween`. Most converters don't accept any parameter. |
| **unique** | N | `false` | Whether to generate only unique values. May result in a fatal error with converters that can't generate enough unique values. |
| **cache_key** | N | `null` | The generated value will be used by all converters that use this cache key. |

How to use parameters:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeEmail'
                parameters: {domains: ['example.org']}
```

How to define a condition:

```yaml
tables:
    my_table:
        converters:
            my_column:
                converter: 'randomizeEmail'
                condition: '{{another_column}} !== null'
```

The filter is a PHP expression.
Variables must be encapsed by double brackets.

The available variables are the columns of the table.
For example, if the table has a `id` column, the `{{id}}` variable will be available.

### Sharing Converter Results

The `cache_key` parameter can be used to share values between converters.

For example, to generate the same anonymized email in two tables:

```yaml
tables:
    customer_entity:
        converters:
            email:
                converter: 'randomizeEmail'
                cache_key: 'customer_email'
                unique: true
```

```yaml
tables:
    newsletter_subscriber:
        converters:
            subscriber_email:
                converter: 'randomizeEmail'
                cache_key: 'customer_email'
                unique: true
```

Notes:

- If you use the `unique` parameter, it must be specified in all converters that share the same cache key.
  If the parameter is missing somewhere, it can result in a infinite loop situation.
- This feature is not used in the default templates (`magento2`, ...), because it may require a lot of memory, depending on the size of the tables.

### Version-specific Configuration

The `if_version` property allows to define configuration that will be read only if the version of your application matches a requirement.

Syntax:

```yaml
if_version:
    '<2.2.0':
        # version-specific config here (e.g. tables)
```

The application version can be defined with the `version` parameter, as explained earlier in this documentation.

The `version` parameter becomes mandatory if the `requiresVersion` parameter is defined and set to `true`.
The [magento2 template](config/templates/magento2.yaml) uses that feature.

There is little point to use this feature in your custom configuration file(s).
It is mainly used to provide default config templates that are compatible with all versions of a framework.
