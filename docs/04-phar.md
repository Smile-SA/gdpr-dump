# Phar File Compilation

## Table of Contents

- [How to Compile the Phar](#how-to-compile-the-phar)
- [Adding Custom Converters](#adding-custom-converters)
- [Adding Custom Templates](#adding-custom-templates)
- [Faker Locales](#faker-locales)

## How to Compile the Phar

A default phar file is provided in the [releases](https://github.com/Smile-SA/gdpr-dump/releases) section of the project on GitHub.

However, it is also possible to compile the phar manually.
This allows to:

- Add custom converters
- Add custom templates
- Add Faker locales

To compile the phar:

1. Fork the project, then clone it.
2. Do whatever you need (add custom converters, custom templates...).
3. Run the following command: `make compile` (it might take a few minutes).
   This will create a file named "gdpr-dump.phar" in the folder "build/dist".

## Adding Custom Converters

To add converters, there are only two requirements:

- The file must be in the directory "src/Converter" (or a subdirectory).
- The class must implement the interface `ConverterInterface`.

## Adding Custom Templates

Templates are located in the directory "app/config/templates".
The files must use the `.yaml` extension.

## Faker Locales

By default, the phar file is only bundled with the "en_US" locale (to reduce the phar file size).

### Changing the Default Locale

The locale used by default is defined by the parameter `faker.locale` in app/config/services.yaml.
For example, to change the default locale to "fr_FR":

```yaml
parameters:
    # ...
    faker.locale: 'fr_FR'
```

This locale is automatically added to the phar file during the compilation process.

### Adding Multiple Locales

To add multiple locales to the phar file, you must compile it with the `--locale` option.
For example, to compile a phar file that includes "de_DE" and "fr_FR":

```
docker compose run --rm app bin/compile --locale=de_DE --locale=fr_FR
```

The default locale can be omitted, it is automatically added to the phar file.

Available locales can be found in the official [faker documentation](https://fakerphp.github.io/).
