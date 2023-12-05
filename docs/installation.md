# Installation

The installation described below assumes that CiviCRM and the funding portal run on the same Drupal and that Drupal is installed with composer. It is also possible to install the funding portal on a separate Drupal instance. In this case, the instructions have to be adapted accordingly.

In the following, `$ENV` refers to the drupal installation root, for example `/var/www/web`.

## Status of this document

This status of this document is **work in progress**.

I tried to install it on a system with

- CiviCRM 5.67.3
- Drupal 10.1.6
- PHP 8.1

I currently face the same issue as described [here](https://github.com/systopia/funding/issues/176).

## Configure upload of private files

```bash
mkdir $ENV/drupal/web/sites/default/files/private
chmod g+w $ENV/drupal/web/sites/default/files/private
```

!!! question
    User `www-data` oder ein anderer user als owner?

Set the following setting in `$ENV/drupal/web/sites/default/settings.php`:

```php
$settings['file_private_path'] = 'sites/default/files/private';
```

## Drupal modules and dependencies

Edit `$ENV/drupal/composer.json` and add the following to repositories:

```
        "json_forms": {
            "type": "vcs",
            "url": "git@github.com:systopia/drupal-json_forms.git"
        },
        "opis-json-schema-ext": {
            "type": "vcs",
            "url": "git@github.com:systopia/opis-json-schema-ext"
        },
        "expression-language-ext": {
            "type": "vcs",
            "url": "git@github.com:systopia/expression-language-ext"
        },
        "custom/civiremote_funding": {
            "type": "vcs",
            "url": "git@github.com:systopia/drupal-civiremote_funding.git"
        },
        "custom/civiremote": {
            "type": "vcs",
            "url": "git@github.com:systopia/civiremote"
        },
        "drupal/cmrf_core": {
            "type": "vcs",
            "url": "git@github.com:CiviMRF/cmrf_core.git"
        },
```

Install CMRF Core in the required version

!!! note
    If CMRF Core is published as stable, this is no longer required.

```bash
composer require drupal/cmrf_core:^2.1
```

Further modules/dependencies:

- `fontawesome` not required if Font Awesome provided by other means.
- `formtips` not required, but recommended.
- `symfony/property-access` is dependency of funding extension.

```
composer require custom/civiremote_funding drupal/fontawesome drupal/formtips symfony/property-access
drush pm:enable civiremote_funding fontawesome formtips
```

So that changes to views etc. can be applied:

```
composer require drupal/config_update
drush pm:enable config_update
```

!!! note
    The last step is longer necessary as soon as we have releases of `civiremote_funding` with update routines.

## Configure fontawesome

Provide Font Awesome files locally:

```
drush fa:download
```

Then adjust the configuration under `/admin/config/content/fontawesome` accordingly.

!!! question
    What does this mean specifically?

## Set up API User

 - add a role `CiviCRM API` with the following permissions:
   - AuthX: Authenticate to services with API key
   - CiviCRM: Access CiviCRM Funding
   - CiviCRM: access CiviCRM Backend und API
   - CiviCRM: view debug output
   - RemoteContacts: match and link
   - RemoteContacts: retrieve
 - add a drupal user `api` with the role `CiviCRM API`
 - generate an [API key](https://docs.civicrm.org/sysadmin/en/latest/setup/api-keys/) for the corresponding CiviCRM contact `api`

!!! question
    I did not find `CiviCRM: Remote Funding Program Manager Zugriff` but `CiviCRM: Access CiviCRM Funding`. I did not find the permissions for RemoteContacts.

## Configure civiremote

- Activate `Acquire CiviRemote ID` under `/admin/config/services/civiremote.` with Parameter mapping: Email → email
- Set up CMRF profile under `/admin/config/cmrf/profiles` (the site key can be found in `civicrm.settings.php`).

## Configure formtips

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
- [de.systopia.identitytracker](https://github.com/systopia/de.systopia.identitytracker).
  _Note (4.12.2023): I installed version 1.3.1 with the hotfix described [here](https://github.com/systopia/de.systopia.identitytracker/issues/19#issuecomment-1764529122)._
- The branch `remote-tools-api4` of [de.systopia.remotetools](https://github.com/systopia/de.systopia.remotetools)
- [de.systopia.civioffice](https://github.com/systopia/de.systopia.civioffice) **TODO: 1.0-beta2 statt 1.0-beta1**
- [org.project60.banking](https://github.com/Project60/org.project60.banking). The  _CiviContribute_ component needs to be activated for this extension.
- [activity-entity](https://github.com/systopia/activity-entity)
- [external-file](https://github.com/systopia/external-file)
- [funding](https://github.com/systopia/funding)

## Configure CiviOffice

See [https://docs.civicrm.org/civioffice/en/latest/](https://docs.civicrm.org/civioffice/en/latest/).

The option "Use PHPWord macros for token replacement" needs to be activated.

## Create funding program

Add a funding program via the web interface.

Define permissions for the CiviCRM users.

## Create templates

## transfer contract template

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

### payment instruction template

Copy the template file to a temporary location (e.g. `/tmp/payment-instruction-template.docx`). Determine the ID of the funding case type.

```shell
cv cvli civicrm_api3('Attachment', 'create', [
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
## Remote Contact Matching

Activate the option **Remote Contact Matching Enabled** at `/civicrm/admin/remotetools`.

## Remote role `CiviRemote Funding`

Assign the remote role **CiviRemote Funding** to contacts who should be able to create applications. The roles are automatically synchronized at login.

The role must be created once in Drupal under `/admin/people` via **Sync users with contacts** and the authorization **CiviRemote: CiviRemote Funding** must be assigned.





