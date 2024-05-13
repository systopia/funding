# Introduction

Foundations and other organizations that distribute funding to applicants are faced with many administrative tasks. They also need to interact with applicants and grant recipients in a specific workflow. The funding framework - a combination of the funding extension with other extensions and Drupal modules - provides a holistic solution for the entire funding process.

## Administrative tasks

The funding framework provides neatly tailored and adaptable support for all administrative tasks that the funding organization has to perform. This includes receiving, reviewing and approving applications; viewing the history of applications; adding comments to applications (internally or visible for applicants); allocate funds; administer transfer contracts; receiving and checking accountability reports.

## Web portal for applicants

The funding framework also provides a web portal which acts as interface for applicants. It allows applicants to submit applications, view ongoing application procedures, download transaction contracts, apply for disbursements and create where-used lists.

The web portal is a Drupal website, which allows organizations to enhance it with additional content such as an FAQ or information pages about funding opportunities. (The API of the funding extension would be accessible from any other external system, though.)

## Adapt the funding framework to your needs

The funding framework has a modular structure. Additional to the existing configuration options within CiviCRM, the code provides many possibilities for developers to adapt it to a different funding workflow. This documentation currently doesn't mention all of these possibilities.

The current implementation mainly reflects the workflow of _Arbeit und Leben_. This German organization pioneered the development of this extension framework, with substancial funding from Deutsche Stiftung Engagement und Ehrenamt and the German Federal Ministry for Family and Youth Affairs. In some places, you will need to adapt it to your own needs, or ask us for assistance. For example, the current application forms are in German and reflect very specific administrative processes. A form editor is planned, but not yet available at this moment.

## Known issues and To-dos
- Documentation is still work in progress - if you want to use this extension and run into issues that should be covered by documentation, feel free to contact us
- Configuration options are limited so far - configurability will be improved in future versions
- API is still under development, so it is best to get in touch if you want to integrate it into your projects

## We need your support

This CiviCRM extension is provided as Free and Open Source Software, and we are happy if you find it useful. However, we have put a lot of work into it (and continue to do so), much of it unpaid for. So if you benefit from our software, please consider making a financial contribution so we can continue to maintain and develop it further.

As part of the overall funding management framework, we have so far developed, enhanced or integrated the following extensions and modules (eventually not exhaustive):

- [funding](https://github.com/systopia/funding) (CiviCRM extension)
- [civiremote_funding](https://github.com/systopia/drupal-civiremote_funding/) (Drupal module)
- [drupal-json_forms](https://github.com/systopia/drupal-json_forms) (Drupal module)
- [opis-json-schema-ext](https://github.com/systopia/opis-json-schema-ext) (PHP library that enhances Opis JSON Schema)
- [expression-language-ext](https://github.com/systopia/expression-language-ext) (PHP library that enhances Symfony ExpressionLanguage)
- [external-file](https://github.com/systopia/external-file) (CiviCRM extension)
- [activity-entity] (https://github.com/systopia/activity-entity) (CiviCRM extension)
- [civiremote](https://github.com/systopia/civiremote) + [cmrf](https://www.drupal.org/project/cmrf_core) + [de.systopia.xcm](https://github.com/systopia/de.systopia.xcm) + [de.systopia.identitytracker](https://github.com/systopia/de.systopia.identitytracker) (Tools for connecting Drupal portal environments with CiviCRM)
- [de.systopia.civioffice](https://github.com/systopia/de.systopia.civioffice) (CiviCRM extension)
- [de.systopia.remotetools](https://github.com/systopia/de.systopia.remotetools) (CiviCRM extension)

If you are willing to support us in developing this CiviCRM extension, please send an email to info@systopia.de to get an invoice or agree a different payment method. Thank you!
