# Basic Usage

## Dump Creation

Dump creation command:

```
bin/console dump [--database=...] [--user=...] [--password] [--host=...] [--port=...] [--driver=...] [--additional-config=...] [<config_file>]
```

Arguments:

- config_file (optional): path to a YAML [configuration file](02-configuration.md).

Options:

- --database: database name
- --user: database user (defaults to `root`)
- --password: whether to prompt a password
- --host: database host (defaults to `localhost`)
- --port: database port
- --additional-config: JSON-formatted data that will be merged with the config file data

**Examples**

With config file:

```
bin/console dump path/to/my/config.yaml > dump.sql
```

No config file:

```
bin/console dump --database=mydb --user=myuser --password > dump.sql
```

With the `--additional-config` option:

```
bin/console dump --database=mydb --user=myuser --password --additional-config='{"dump":{"output":"dump-{YmdHis}.sql.gz","compress":"gzip"}}'
```

**Templates**

Instead of using a custom config file, you can use one of the default templates available:

- drupal7
- drupal8
- magento1
- magento1_commerce
- magento2
- magento2_b2b
- magento2_commerce

If you use a default template (e.g. "magento2"), you will need to specify the application version (e.g. "2.3.2").

To specify the application version, there are two alternatives:

- Using the `additional-config` option in the command line:
    ```
    bin/console dump --database=mydb --user=myuser --password --additional-config='{"version":"2.3.2"}' magento2
    ```

- Using a custom configuration file with the following contents:
    ```yaml
    extends: 'magento2'
    version: '2.3.2'
    ```

If you don't use one of the default templates provided by this tool, you don't need to specify any version.

## Configuration File Validation

The following command checks if a configuration file is valid:

```
bin/console config:validate <config_file>
```

If the file is invalid, the command will output the validation errors.
