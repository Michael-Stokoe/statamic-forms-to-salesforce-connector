# Forms To Salesforce Connector

**Seamlessly connect your Statamic forms to Salesforce** - Automatically create Leads, Contacts, and other Salesforce objects from form submissions with advanced field mapping and comprehensive error handling.

## 🔗 Related Connectors

This connector is part of the **Forms To Wherever** ecosystem. Check out our other connectors:

- **[Forms To Wherever](https://statamic.com/addons/stokoe/forms-to-wherever)** - Base package (required)
- **[Forms To Mailchimp](https://statamic.com/addons/stokoe/forms-to-mailchimp-connector)** - Mailchimp email marketing
- **[Forms To HubSpot](https://statamic.com/addons/stokoe/forms-to-hubspot-connector)** - HubSpot CRM integration
- **[Forms To ConvertKit](https://statamic.com/addons/stokoe/forms-to-convertkit-connector)** - ConvertKit email marketing
- **[Forms To ActiveCampaign](https://statamic.com/addons/stokoe/forms-to-activecampaign-connector)** - ActiveCampaign automation

## Features

- **Multiple object support** - Create Leads, Contacts, Accounts, Opportunities, or Cases
- **Custom field mapping** to any Salesforce object field
- **Lead source tracking** - Automatically set lead sources for attribution
- **Required field handling** - Intelligent defaults for required Salesforce fields
- **Comprehensive error handling** with detailed logging

This addon does:

- This
- And this
- And even this

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require stokoe/forms-to-salesforce-connector
```

## How to Use

1. Edit your form in the Statamic Control Panel
2. Navigate to the "Form Connectors" section
3. Enable the Salesforce connector
4. Enter your Salesforce instance URL and access token
5. Select the object type (Lead, Contact, etc.)
6. Configure field mappings as needed
7. Save and test!

### Conditional Field Mapping

You can map multiple form fields to the same Salesforce field to support conditional forms. For example, if you have separate service type fields that show based on a category selection:

| Form Field | Salesforce Field |
|------------|------------------|
| residential_service | Service_Type__c |
| commercial_service | Service_Type__c |
| retirement_service | Service_Type__c |

The connector will use the first non-empty value, ignoring null/empty fields.

## Requirements

- PHP 8.2+ (8.3+ for Statamic 6)
- Statamic 4.0+ | 5.0+ | 6.0+
- Forms To Wherever base package
