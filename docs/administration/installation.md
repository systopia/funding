# Installation

The installation described below assumes that CiviCRM and the funding portal run on the same Drupal and that Drupal is installed with composer. It is also possible to install the funding portal on a separate Drupal instance. In this case, the instructions have to be adapted accordingly.

The funding extension is still under development and there exists no stable release yet.

In the following, `$DRUPAL_ROOT` refers to the Drupal installation root, for example `/var/www/web`.

## Status of this Document

The status of this document is **work in progress**.

The installation instructions were used on a system with

- `CiviCRM 5.67.3`
- `Drupal 10.1.6`
- `PHP 8.1`

## Configure Upload of Private Files

```bash
mkdir $DRUPAL_ROOT/drupal/web/sites/default/files/private
chmod g+w $DRUPAL_ROOT/drupal/web/sites/default/files/private
```

Grant read and write permissions for the directory `private` to the webserver user.

Set the following setting in `$DRUPAL_ROOT/drupal/web/sites/default/settings.php`:

```php
$settings['file_private_path'] = 'sites/default/files/private';
```

## Drupal Modules and Dependencies

Edit `$DRUPAL_ROOT/drupal/composer.json` and add the following to repositories:

```
        "json_forms": {
            "type": "vcs",
            "url": "git@github.com:systopia/drupal-json_forms.git"
        },
        "custom/civiremote_funding": {
            "type": "vcs",
            "url": "git@github.com:systopia/drupal-civiremote_funding.git"
        },
        "custom/civiremote": {
            "type": "vcs",
            "url": "git@github.com:systopia/civiremote.git"
        },
```

Alternatively, open a terminal at `$DRUPAL_ROOT` and use `composer` in order to add the repositories to `$DRUPAL_ROOT/drupal/composer.json`:

```
composer config repositories.json_forms vcs git@github.com:systopia/drupal-json_forms.git
composer config repositories.custom/civiremote vcs git@github.com:systopia/civiremote.git
composer config repositories.custom/civiremote_funding vcs git@github.com:systopia/drupal-civiremote_funding.git
```

Then, open a terminal at `$DRUPAL_ROOT` and enter:

```bash
composer require custom/civiremote_funding
drush pm:enable civiremote_funding
```

## Further Modules/Dependencies

- `fontawesome` is not required if **Font-Awesome** is provided by other means.
- `formtips` is not required, but recommended.
- `symfony/property-access` is a dependency of the funding extension.

```
composer require drupal/fontawesome drupal/formtips symfony/property-access
drush pm:enable fontawesome formtips
```

Enter this command, so that changes to views etc. can be applied:

```
composer require drupal/config_update
drush pm:enable config_update
```

The last step is no longer necessary as soon as we have releases of `civiremote_funding` with update routines.

### Configure Fontawesome

You can optionally change the settings to provide Font Awesome files locally:

```
drush fa:download
```

Open `/admin/config/content/fontawesome` and uncheck the option **Use external file (CDN) / local file?**


### Configure Formtips

Set the following under `/admin/config/user-interface/formtips` (adjust times if necessary):

```
Trigger Action: Hover
Selector: :not(#funding-form *)
Interval: 100
Timeout: 100
```

The above selector restricts the module to the funding forms.

## Install CiviCRM Extensions

Install the following extensions, use the newest release if not otherwise indicated:

- [de.systopia.xcm](https://github.com/systopia/de.systopia.xcm)
- [de.systopia.identitytracker](https://github.com/systopia/de.systopia.identitytracker) - version >= 1.4
- [de.systopia.remotetools](https://github.com/systopia/de.systopia.remotetools)
- [de.systopia.civioffice](https://github.com/systopia/de.systopia.civioffice) - version >= 1.0
- [org.project60.banking](https://github.com/Project60/org.project60.banking) - version >= 1.0. The  _CiviContribute_ component needs to be activated for this extension.
- [activity-entity](https://github.com/systopia/activity-entity)
- [external-file](https://github.com/systopia/external-file)
- [funding](https://github.com/systopia/funding)

## Configure CiviOffice

See [https://docs.civicrm.org/civioffice/en/latest/](https://docs.civicrm.org/civioffice/en/latest/).

The option **Use PHPWord macros for token replacement** needs to be activated at the settings page of _CiviOffice_ at `civicrm/admin/civioffice/settings`, in section **CiviOffice Document Renderers**.

## Configure CiviRemote

- Activate **Acquire CiviRemote ID** at `/admin/config/services/civiremote.` with Parameter mapping: **Email** → **email**
- Activate the option **Remote Contact Matching Enabled** at `/civicrm/admin/remotetools`.

## Configure CiviMRF

Set up a Drupal role **CiviCRM API** with the following permissions:

- AuthX: Authenticate to services with API key
- CiviCRM: access CiviCRM Backend and API
- CiviCRM: remote access to Funding Program Manager
- CiviCRM: view debug output
- CiviRemote: Match and link contacts
- CiviRemote: Retrieve contacts

Set up a Drupal User:

- add a API user **api** with the role **CiviCRM API**. This also creates a corresponding CiviCRM contact named **api**.
- verify that the CiviCRM contact has a matching Drupal **User ID**. See field **Contact ID/User ID** at the summary page of the contact.
- generate an [API key](https://docs.civicrm.org/sysadmin/en/latest/setup/api-keys/) for the corresponding CiviCRM contact **api**

Set up a CiviMRF profile under `/admin/config/cmrf/profiles` or edit the default profile:

- Insert the Site-Key. It can be found under **CIVICRM_SITE_KEY** in your `$DRUPAL_ROOT/drupal/web/sites/default/civicrm.settings.php`
- Insert the API Key you just created for user **api**.
- **URL APIv3** and **URL APIv4** must be set to proper url of your CiviCRM instance. Verify that those endpoints can be reached.

[Optional]

Activate **CiviMRF Call Report** at `/admin/modules` or via `drush` at `$DRUPAL_ROOT` 

```
drush pm:enable cmrf_call_report
```

This helps with debugging by showing a report about all API calls sent to CiviCRM and the corresponding results.
The report can be found at `admin/reports/cmrfcalls`.

## Synchronise User Roles

CiviRemote will synchronise permissions that are set for a CiviCRM contact with the associated user in Drupal. For the funding framework, the roles **CiviRemote: CiviRemote User** and **CiviRemote: CiviRemote Funding** are used. During the synchronisation of user roles, these roles are automatically created in Drupal if they don't exist yet. Because of this, we create a test user, synchronise/create the roles and delete the user afterward. You can also create a regular user that you would need to create anyway.

- Create a new Drupal user **Test User**
- Open the associated CiviCRM Contact, scroll down in the summary page and edit the custom field set **RemoteContact Information**. Add the two roles **CiviRemote User** and **CiviRemote Funding**.
- Open the user list of Drupal (`admin/people`) and select the test user you just created
- Perform the action **CiviRemote: Match contacts** and afterwards **CiviRemote: Synchronise CiviRemote Roles**

You should now see the roles listed for **Test User**.

You can delete the test user if you don't need it anymore.
For any other users you create, the roles selected in CiviCRM at **RemoteContact Information** will be automatically synchronised during the login of that user.

Additionally, you need to adapt the permissions for Drupal user roles as described [here](./user-permissions.md#drupal-permissions).

[Troubleshooting]

If no roles are listed for **Test User** after **CiviRemote: Synchronise CiviRemote Roles** has been performed:

1. check **CiviMRF Call Reports** at `admin/reports/cmrfcalls`.

If you see a message **FAIL civiremote RemoteContact match** then the Drupal **Test User** did not receive a **CiviRemote ID** for its CiviCRM contact through action **CiviRemote: Match contacts**.
You can verify this by taking a look at the infopage of the **Test User**, linked at `/admin/people`. The field **CiviRemote ID** might be empty.
                            
2. you can try to solve this issue by relaoding the XCM (Extended Contact Matcher) profile, that is used for matching contacts.

- go to `civicrm/admin/setting/xcm` and edit the XCM-Profile (ie. _default_). 
- save the profile without making any changes
- perform the action **CiviRemote: Match contacts** again for Drupal user **Test User**.

**CiviMRF Call Reports** at `admin/reports/cmrfcalls` should now show a message **DONE civiremote RemoteContact match**
If so, run action **CiviRemote: Synchronise CiviRemote Roles** for Drupal user **Test User** again in order to synchronize roles from CiviCRM.

## Configure Dashboard

Open the basic site settings at `admin/config/system/site-information` and enter `/civiremote/funding` in the field for the default front page.

## Create Templates

The creation of transfer contracts and payment instructions relies on templates in `docx` format. They are created with [CiviOffice](https://docs.civicrm.org/civioffice/en/latest/) and can contain tokens. Currently, there is no admin page available to upload the template files.

### Transfer Contract Template

Copy the template file to a temporary location (e.g. `/tmp/transfer-contract-template.docx`). Determine the ID of the funding case type.

```shell
cv cli civicrm_api3('Attachment', 'create', [
  'name' => "transfer-contract-template.docx",
  'mime_type' => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  'entity_id' => {FUNDING_CASE_TYPE_ID},
  'entity_table' => "civicrm_funding_case_type",
  'options' => ['move-file' => "/tmp/transfer-contract-template.docx"],
]);

exit
```
Set the `file_type_id` (not possible with Attachment API):

```shell
cv api4 File.update +v file_type_id:name=transfer_contract_template +w 'id = {FILE_ID}'
```

### Payment Instruction Template

Copy the template file to a temporary location (e.g. `/tmp/payment-instruction-template.docx`). Determine the ID of the funding case type.

```cv cli```

in the following prompt you can enter

```shell
civicrm_api3('Attachment', 'create', [
  'name' => "payment-instruction-template.docx",
  'mime_type' => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  'entity_id' => "{FUNDING_CASE_TYPE_ID}",
  'entity_table' => "civicrm_funding_case_type",
  'options' => ['move-file' => "/tmp/payment-instruction-template.docx"],
]);

exit
```
Set the `file_type_id` (not possible with Attachment API):

```shell
cv api4 File.update +v file_type_id:name=funding_payment_instruction_template +w 'id = {FILE_ID}'
```

## Time Zone

All requests must be executed with the same time zone. This means that all
CiviCRM contacts must use the same time zone. Therefore, the option
`Users may set their own time zone` in the Drupal `Regional settings`
(`/admin/config/regional/settings`) needs to be disabled.
