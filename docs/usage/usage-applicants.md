# Usage for Applicants

This chapter describes the typical use of the Funding Portal by a person who is applying for funding under a funding program. This includes the application phase as well as the workflow after a funding has been granted.

The actions described below are grouped into the various [phases](./usage.md#phases-of-a-funding-case) that a funding case can go through.

## Funding Portal

### Login

Before an application can be created, applicants need to be provided with a username and password to log into the funding portal. Their user account needs to have the correct permissions, see the chapter [User Permissions](../administration/user-permissions.md) for more details.

After login in, applicants can change their password at **My account** → **Edit**. All other possible actions can be found on the dashboard page.

## Dashboard

The dashboard is the central page of the funding portal and is usually configured to be the home page. All possible actions that are available to applicants are listed there.

![Dashboard page](.././img/drupal_dashboard.png){ width="500" }

### Application List

The dashboard element **My Applications**  shows the existing applications with some details. Different actions can be performed for applications in this list.

![](../img/drupal_application_list.png)

Applications of funding case type _Sammelantrag_ are listed separately in the dashboard element **My combined Applications**.

#### Create Documents

If application templates are configured by the giving organization, there are actions available in the application list with names of the form **Create: ...**. Those actions will create a PDF document based on the template. It can be downloaded and printed.

An example use case is a confirmation of the accuracy of the data that needs to be signed by hand. Or a list of participants of a workshop, that needs to be submitted during the clearing phase.

## Application Phase

### Create a new Application

Applicants can create a new application by opening the dashboard and choosing **New Application**. The first step is to choose the funding program they want to apply for. This leads to the application form which can be long and divided into multiple tabs.

It is possible to save a draft and continue to work on the application at another time. When it is finished, it can be applied by clicking on the button **Apply**.

### Change an Application

You can open the application from the application list via the action **Open application**. This leads to the same form that was used during creation, containing the details of the application. However, the available buttons and associated actions in this form depend on the status that the application currently has. The application might be in a status that does not allow changes.

Before applying, there are two actions possible that are reflected in the buttons available at the bottom of the form: **Save** the application after editing, or **Apply** to apply the application.

All possible states and the available actions for the currently built-in funding case types are explained [here](./application-states.md).

### View the Application History

The applications listed in **My Applications** also provide the action **Show History**. This leads to a flow chart showing every status that the application has gone through in chronological order, including comments created by the reviewers.

It is possible to hide all comments or to hide all workflow actions.

![screenshot of application history](../img/drupal_application_history.png){ width="500" }

## Drawdown Phase

Once a funding case has been approved, the drawdown phase begins. The dashboard element **Transfer Contracts -
Download transfer contracts and manage drawdowns** leads to a list of approved funding cases and provides different actions.

![](../img/drupal_transfercontract_list.png)

### Transfer Contracts

A transfer contract is signed between the giving organization and the recipient. The document is available for **Download** by the applicants. A corresponding template is configured by the giving organization.

### Drawdowns

There are two actions available: **Create drawdown** and **Show drawdowns**. It is possible to create multiple drawdowns, which allow the distribution of the available amount in several parts.

The creation of a drawdown is a very simple webform:

![](../img/drupal_drawdown_create.png)

The action **show drawdown** leads to a list such as this:

![](../img/drupal_drawdown_show.png)

## Clearing Process

Once an application has been approved by the reviewers, applicants can start recording expenses and other information about their project. If they find their report is complete, they can submit it for review. 
