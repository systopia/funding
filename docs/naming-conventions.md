# Naming conventions

The framework provided by the funding extension involves multiple entities, extensions and groups of people that interact which each other. The following list provides a short description of the terms that are mostly used.

**Funding Framework**: The CiviCRM extension [funding](https://github.com/systopia/funding), as well as the Drupal module [civiremote_funding](https://github.com/systopia/drupal-civiremote_funding/), as well as a [set of other extensions and modules](./index.md#we-need-your-support).

**Giving Organization**: The organization that hosts funding programs and allocates money to recipients with eligible applications. The giving organization hosts and administers CiviCRM.

**Funding Portal**: A Drupal website that is created for the interaction with applicants and recipients. The Funding Portal doesn't need to be identical to the Drupal instance that hosts CiviCRM.

-------------

**Applicant**: A person or organization that applies for the funding program. Applicants have access to the Funding Portal. They don't have access to CiviCRM.

**Recipient**: A person or organization that receives the funding.

**Reviewer**: A person that is associated with the giving organization and reviews applications to be rejected or eligible. There can be a distinction between the following roles: content reviewer, calculative reviewer and drawdown reviewer. Reviewers have access to CiviCRM.

**Funding Admin**: A person that administers the funding framework in CiviCRM and in the Funding Portal. This includes creating new funding programs, creating new user accounts for the Funding Portal and setting correct permissions for those user accounts.

------------

**Funding program**: The context in which the money is to be allocated. It contains a time interval for applications, a time interval in which recipients can collect the money, and it may contain a budget.

**Application / Request**: A specific application in the context of a funding program.

An application has different components:

  * The data that was entered in the application form
  * The [status](./usage/application-states.md) of the application
  * Assigned reviewers
  * Comments by applicants or reviewers that can be private or public
  * A history of all states and comments
  * Other general data, for example the creation date or if a review is passed

There are (currently) two types of applications: Normal Applications and Combined Applications. Both contain different application forms which are specific to the needs of *Arbeit und Leben*, the organization that funded the development of the Funding Extension.

**Funding Case**: All applications that were created by the same applicant for a specific funding program. A funding case might contain multiple applications.
