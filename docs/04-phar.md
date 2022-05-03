# Phar File Compilation

## Table of Contents

- [How to Compile the Phar](#how-to-compile-the-phar)
- [Adding Custom Converters](#adding-custom-converters)
- [Adding Custom Templates](#adding-custom-templates)
- [Adding Faker Locales](#adding-faker-locales)

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

## Adding Faker Locales

By default, the phar file is bundled with the en_US locale of Faker.
It does not contain the other locales.

To add other locales, you must edit the following parameters in app/config/services.yaml:

- `faker.locale`: the locale used by default
- `faker.installed_locales`: the locales that are included in the compiled phar

For example, to replace the `en_US` locale by `fr_FR`:

```yaml
    faker.locale: 'fr_FR'
    faker.installed_locales:
        - 'fr_FR'
```

Available locales can be found in the official [faker documentation](https://fakerphp.github.io/).
