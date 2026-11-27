# Notification Emails

It is possible to send notification emails when the status of an application
process, a funding case, a drawdown, or a clearing process changes. This
requires message templates with a `workflow_name` that follows these patterns:

* `funding.[case_type:<type>.]application_process.status_change:<new_status>`
* `funding.[case_type:<type>.]funding_case.status_change:<new_status>`
* `funding.[case_type:<type>.]drawdown.status_change:<new_status>`
* `funding.[case_type:<type>.]clearing_process.status_change:<new_status>`

A notification is also sent when one of these is created, using its initial
status.

When an application process status changes the behavior is like this:

1. Look for a template:
     1. Look for a template with the funding case type in the workflow name (e.g. `funding.case_type:example.application_process.status_change:applied`).
     2. If no template is found: Look for a template without funding case type in the workflow name (e.g. `funding.application_process.status_change:applied`)
2. If a template is found and `is_active` is `TRUE`:
   Send an email to every notification contact with `do_not_email` set to `FALSE`.

Available tokens:

* `{contact.*}` The notification contact.
* `{funding_case.*}`
* `{funding_case_type.*}`
* `{funding_program.*}`
* `{funding_application_process.*}` Only for application process and clearing
  process notifications.
* `{funding_clearing_process.*}` Only for clearing process notifications.
* `{funding_drawdown.*}` and `{funding_payout_process.*}` Only for drawdown
  notifications.

The behavior is the same for the other status changes, apart from the
available tokens.

The message templates have to be created manually via API call. (It's not
possible to create message templates with `workflow_name` via UI.)

The notification contacts can be changed for each funding case via UI. It is
initialized with the creation contact.
