# Basic Usage

## Command Usage

Usage:

```
bin/gdpr-dump <config_file>...
```

Arguments:

- config_file: path(s) to a [configuration file](02-configuration.md).

The complete list of options can be displayed with the following command:

```
bin/gdpr-dump --help
```

Example:

```
bin/gdpr-dump path/to/my/config.yaml > dump.sql
```

## Configuration Templates

The following configuration templates are available:

- [drupal7](app/config/templates/drupal7.yaml)
- [drupal8](app/config/templates/drupal8.yaml)
- [magento1](app/config/templates/magento1.yaml)
- [magento2](app/config/templates/magento2.yaml)

Each template provides anonymization rules for a specific framework (e.g. "magento1" is for Magento 1 Community Edition).

If you use a configuration template, you **must** specify the application version (e.g. "2.3.2").

**How to use a configuration template:**

1. Create your configuration file:

    ```yaml
    extends: 'magento2'
    version: '2.3.2'
  
    database:
        name: 'mydatabase'
        user: 'myuser'
        password: 'mypassword'
    ```

2. Execute the gdpr-dump command:

    ```
    bin/gdpr-dump my_project.yaml
    ```
