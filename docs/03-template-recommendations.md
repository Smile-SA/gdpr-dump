# Template Recommendations

## Tables of Contents

- [Custom Tables](#user-content-custom-tables)
- [Magento 1](#user-content-magento-1)
- [Magento 2](#user-content-magento-2)

## Custom Tables

If your project has custom tables with sensible data, your config file must declare converters that anonymizes this data.

Example of sensible data:

- email
- username
- name
- phone number
- address
- IP address
- encrypted password
- payment data
- comment that could contain customer-related information

## Magento 1

In Magento 1, the payment data is partially stored in a column named `additional_information`.
The data is stored as a serialized array.
Only the `CC_CN` property is anonymized by the `magento1` template.

If this column contains other sensible data in your project, you must anonymize it in your custom config file.
For example:

```yaml
tables:
    sales_flat_quote_payment:
        converters:
            additional_information:
                converter: 'serializedData'
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}

    sales_flat_order_payment:
        converters:
            additional_information:
                converter: 'serializedData'
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}
```

The fields to anonymize will depend on the payment methods that are used in the project.

## Magento 2

In Magento 2, the payment data is partially stored in a JSON-encoded field named `additional_information`.
Only the `CC_CN` property is anonymized by the `magento2` template.

If this column contains other sensible data in your project, you must anonymize it in your custom config file.
For example:

```yaml
tables:
    quote_payment:
        converters:
            additional_information:
                converter: 'jsonData'
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}

    sales_order_payment:
        converters:
            additional_information:
                converter: 'jsonData'
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}
```

The fields to anonymize will depend on the payment methods that are used in the project.

If you use Magento <= 2.2, you will need to use `serializedData` instead of `jsonData`.
