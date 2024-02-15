# Users and permissions

In a funding program and for a specific funding case, different people from the giving organisation might be involved. If the receiving contact is an organisation, there might be different people interacting with the funding case as well. These people have different roles that come with different permissions.

The permissions need to be configured on different levels:

* Drupal permissions
* CiviRemote User roles on a CiviCRM contact summary page
* Funding program permissions
* Allowed recipients for a funding program
* Funding Case permissions

Some permissions need to be configured only once at the initial configuration of the funding framework. Others have to be configured during the creation of a new funding program. Some have to be configured for every individual contact.

## Drupal permissions

After installing the funding extension, the Drupal roles need to be given the correct permissions. You can edit the Drupal permissons at **People > Permissions** or `admin/people/permissions`, respectively.

* Grant the following permissions to the role *CiviRemote: CiviRemote Funding*:
    * Access CiviCRM Funding
* Reviewers need to have a role that grants the following permissions:
    * CiviCRM: access Funding Program Manager
* Site Admins need to have a role that grants the following permissions:
    * CiviCRM: access Funding Program Manager
    * CiviCRM: Funding Program Manager Administration

## CiviRemote User Roles

Every person you want to give any of the permissions listed above needs to have a drupal user account. This refers to reviewers of the giving organisation as well as individuals or members of external organisations that want to apply for the funding. You manually need to add CiviRemote roles to these user accounts.

Every drupal user is mapped to a CiviCRM contact. To assign a CiviRemote role, open the summary of the CiviCRM contact and edit the field set **RemoteContact Information**.

- for reviewers add the role **CiviRemote User**.
- for receiving contacts add the roles **CiviRemote User** and **CiviRemote Funding**.

!!! question
    It can be handy to allow the creation of Drupal user accounts for unauthenticated users, for example new applicants. This is done at *Arbeit und Leben*, I think. How is this done exactly? I want to add this as a tip.

!!! note
    The CiviRemote User Roles are automatically synchronised with the Drupal user account during login. If there are problems with the synchronisation, open the drupal user overview at `admin/people`. Select the user that you want to assign the roles to and perform the action **CiviRemote: Match contact(s)** and afterward **CiviRemote: Synchronise CiviRemote roles**.


## Funding Program

After creating a funding program, it will be listed at **Funding > Funding Programs**. There are several actions available, namely **Edit Recipients**, **Edit permissions** and **Edit initial funding case permissions**. These three settings have to be configured correctly before opening the program for applicants.

### Edit Recipients

When an applicant creates an application, one field of the application form asks who is going to be the recipient of the funding. The recipient is not necessarily identical to the applicant. The field in the application form is a dropdown menu where the correct recipient needs to be chosen. The values that are shown in the dropdown menu need to be configured at **Edit Recipients**.

As an example, consider a funding program that funds organizations. The application is not created by the organization but by an employee of that organization. In this case, the applicant is the employee and the recipient is the organization. If both exist as contacts in CiviCRM and are connected by a relationship of type _Employee_, the configuration would look as follows:

![](../img/permissions_edit_recipients.png)

The dropdown menu will then show all organizations that have an _Employee_ relationship with the applicant that is logged in and creating the application.

### Edit permissions

These permissions are related to a funding program, independently of a specific funding case or application.

The following permissions exist:

* **Application: create** allows contacts to create a new application for this funding program.
* **Application: apply** allows contacts to create a new application and apply it in one step.
* **View** specifies which funding programs are available to create an application for. It also specifies which funding programs are visible in the CiviCRM funding program list.

The permissions **Application: create** and **Application: apply** are usually granted to applicants. Both implicitly include the permission **View**. The **View** permission is typically given to reviewers and site admins.

Permissions can be assigned to specific contacts, contact types or contacts that have a relationship to a specific contact or contact type. In the last case, you need to specify the relationship type.

A configuration could look like this:

!["Example configuration of permissions on funding program level"](../img/permissions_funding_program.png )

### Initial funding case permissions

Additional to the permissions that are set for a whole funding program, there exist permissions that are on the level of a funding case and can be different between cases. Funding cases are created automatically during the application process and there might be many cases for one funding program. This requires a default set of permissions that are automatically assigned during the creation of a new funding case. These initial permissions can be configured with the action **Initial funding case permissions** of a funding program. To alter the permissions for a specific funding case, go to **Funding > Funding Cases**, find the funding case in the list and choose the action **Edit permissions** for this case.

The following permissions exist for reviewers:

* Review: content
* Review: calculative
* Review: drawdown

The following permissions exist for applicants:

* Application: create
* Application: view
* Application: modify
* Application: apply
* Application: withdraw
* Application: request rework
* Drawdown: create

As before, permissions can be assigned to specific contacts, contact types or contacts that have a relationship to a specific contact or contact type. In the last case, you need to specify the relationship type.

A configuration could look like this:

!["Example configuration of permissions on funding program level"](../img/permissions_funding_case.png)
