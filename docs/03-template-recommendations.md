# Template Recommendations

## Custom Tables

If your project has custom tables with sensible data, your config file must declare converters that anonymizes this data.

Example of sensible data:

- email
- username
- name
- date of birth
- phone number
- address
- IP address
- encrypted password
- payment data
- comment that could contain customer-related information

## Performance

Since this tool is a pure PHP implementation of a dumper, it is way slower than mysqldump.

If the database to dump has tables with 

## Magento

**Admin Accounts**

The `magento1` and `magento2` templates anonymize all admin accounts.

If you want to keep the email/password for some accounts, you can set a condition on the `email`, `username` and `password` columns of the `admin_user` table.

Example:

```yaml
tables:
    admin_user:
        converters:
            email:
                condition: '{{username}} != "admin_smile"'
            username:
                condition: '{{username}} != "admin_smile"'
            password:
                condition: '{{username}} != "admin_smile"'
```

**Payment Data**

In Magento 1 and Magento 2, the payment data is partially stored in a column named `additional_information`.
The data is stored as a serialized array.
Only the `CC_CN` property is anonymized by the `magento1` and `magento2` templates.

If this column contains other sensible data in your project, you must anonymize it in your custom config file.
For example, in Magento 1:

```yaml
tables:
    sales_flat_quote_payment:
        converters:
            additional_information:
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}

    sales_flat_order_payment:
        converters:
            additional_information:
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}
```

In Magento 2:

```yaml
tables:
    quote_payment:
        converters:
            additional_information:
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}

    sales_order_payment:
        converters:
            additional_information:
                parameters:
                    converters: {fieldToAnonymize: 'anonymizeText'}
```

The fields to anonymize will depend on the payment methods that are used in the project.
