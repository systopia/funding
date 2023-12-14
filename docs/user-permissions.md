# Users and permissions

In a funding program and for a specific funding case, different people from the giving and applying organisation are involved. These people have different roles that come with different permissions regarding the funding case, for example creating an application or rejecting it.

### Permissions within the giving organisation

We assume that your organisation is the giving organisation and that it is configured to be the default organisation of your CiviCRM instance, namely the organisation specified at `civicrm/admin/domain` with Contact-Id 1.

!!! question
    Is my assumption about the giving organization correct? Is it important to specify this at all?

Within each funding program, you can configure roles that have different permission sets. You can assign permissions to specific contacts, contact types or contacts that have a relationship to a specific contact or contact type. In the last case, you need to specify the relationship type.

The following permissions exist:

- Application: view
- Application: request rework
- Review: content
- Review: calculative
- Review: drawdown

A typical example would be a separation in three roles:

- The **content reviewer** checks the content of an application ... . They have the permission "Application: view", "Application: request rework" and "Review: content".
- The **calculative reviewer** checks weather the amount of money ... . They have the permission "Application: view", "Application: request rework" and "Review: calculative".
- The **drawdown reviewer** checks if the funding of an accepted application have been called up. They have the permission "Application: view" and "Review: drawdown".

For a description of what the permissions mean in detail, see the section about the workflow of a funding case.

If your funding program is big and allows for applications in different thematic areas, you might want to assign one of your employees to be the content reviewer for topic A and another employee to be the content reviewer for topic B. To allow for this or other specific needs, the permissions are set on a case by case basis and not for the whole funding program. You will need to configure an initial permission set that is applied automatically for each new application. Additionally, it is possible to change the permissions for specific funding applications.

### Permissions within the receiving organisation

An organisation that applies for a funding and maybe receives money might have different members that interact with your organisation during the process.

Also in this case there exist different permissions that can be assigned to specific contacts or groups of contacts. The following permissions exist:

- Application: create
- Application: view
- Application: modify
- Application: apply
- Application: withdraw
- Drawdown: create

As an example consider the following situation:

- The **main applicant** is responsible for the final application and has all permissions listed above, including applying and withdrawing the application.
- The **applicant assistant** can help with the content of the application and has the permission "Application: modify".

### Configure permissions

The permissions need to be configured on different levels. There are permissions that are set for the whole funding program and permissions that can be configured for every funding case individually. Additionally, there are CiviRemote user roles which have to be assigned to every user.

#### Funding program

Open the funding program overview at **Funding** → **Funding Programs** and choose the Action **Edit permissions** for your funding program.

Add permissions for CiviCRM users or user groups.

- Contacts with the permission **Application: create** will be able to create and edit an application for this funding program
- Contacts with the permission **Application: apply** will be able to send the finalized application to your organization
- Contacts with the permission **View** can view all applications of this funding program

A configuration could look like this:

!["Example configuration of permissions on funding program level"](./img/permissions_funding_program.png )

!!! question
    How are these permissions related to the permissions on the level of a funding case?

#### Funding case

Open the funding program overview at **Funding** → **Funding Programs** and choose the Action **Edit initial Funding Case Permissions** for your funding program.

A configuration could look like this:

!["Example configuration of permissions on funding program level"](./img/permissions_funding_case.png )

To alter the permissions of a specific funding case, open the funding case list at `/civicrm/funding/case/list`, select the case and choose the action **edit permissions**.


#### CiviRemote roles

Every person you want to give any of the permissions listed above needs to have a drupal user account. This refers to reviewers of your organisation as well as applicants of external organisations. You manually need to add CiviRemote Roles to these user accounts.

Every drupal user is mapped to a CiviCRM contact. To assign a CiviRemote role, open the summary of the CiviCRM contact and edit the field set **RemoteContact Information**.
- for reviewers add the role **CiviRemote User**.
- for applicants add the roles **CiviRemote User** and **CiviRemote Funding**.

!!! note
    If you cannot find the roles **CiviRemote User** or **CiviRemote Funding**, open the drupal user overview at `admin/people`, select the user and perform the action **CiviRemote: Match contact(s)** and afterward **CiviRemote: Synchronise CiviRemote roles**.


