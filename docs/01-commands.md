# Basic Usage

## Dump Creation

Dump creation command:

```
bin/console dump [--host=...] [--user=...] [--password] [--database] [<config_file>]
```

Example:

```
bin/console dump path/to/my/config.yaml 
```

**Templates**

Instead of a config file, you can also use one of the default templates available:

- drupal7
- drupal8
- magento1
- magento2
- magento2_b2b
- magento2_commerce

Example:

```
bin/console dump --database=mydb --user=myuser --password magento2
```

**Application Version**

If you use a default configuration template (e.g. "magento2"), you will need to specify the application version (e.g. "2.2.8").

To specify the application version, there are two alternatives:

1. Using the `additional-config` option in the command line:  
   `bin/console dump magento2 --additional-config='{"version":"2.2.8"}'`
2. Using a custom configuration file:  
   `bin/console dump myproject.yaml`

myproject.yaml:

```yaml
extends: 'magento2'
version: '2.2.8'
```

If you don't use one of the default templates provided by this tool, you don't need to specify any version.


## Configuration File Validation

The following command checks if a configuration file is valid:

```
bin/console config:validate <config_file>
```

If the file is invalid, the command will output the validation errors.
