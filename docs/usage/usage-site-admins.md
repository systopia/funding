# Usage for administrators

This section describes necessary administrative tasks besides the initial [installation](../administration/installation.md) and [configuration](../administration/configuration.md).

## Create a new Funding Program

Navigate to **Funding > Add Funding Program**. This directs to a form where you can enter the basic values of the program:

* **Title**: The title of the funding program. It is possible to add a long title here.
*  **Abbreviation**: A short abbreviation of the program title.
* **Identifier Prefix**: The identifier of a funding case within this funding program will start with the given prefix.
* **Start Date**: The start date of the funding program.
* **End Date**: The end date of the funding program.
* **Requests Start Date**: The start date of the application process.
* **Requests End Date**: The end date of the application process.
* **Currency**: The currency for the budget.
* **Budget**: The amount of money that the giving organisation can distribute across the various funding requests.
* **Funding Case Type**: The type of this funding program. The available case types are
    * Sonstige Aktivität (AVK1)
    * Internationale Jugendbegegnung
    * Sammelantrag Kurs

!!! note "Changing Funding Case Types"
    The funding case types are specific to the needs of *Arbeit und Leben*, the organization that funded the development of the funding extension. They will be different for other giving organizations. Currently, there exists no administration page to change the funding case types. They have to be changed directly in the code of the extension. Alternatively, you are welcome to fund the creation of an administration page.

After creating the funding program, you will be redirected to a page that allows you to take further actions. It is recommended to [edit the funding program permissions](todo:add link) via **Actions > Edit permissions** before closing this page. Otherwise, you might not have enough permissions and not find the newly created funding program in the funding program overview.

!!! question
    - What is the maximal recommended length of the abbreviation? In which situations is the abbreviation used?
    - What are start and end date in contrast to requests start / end date? Is there a typical order (`start < start_request < end_request < end`)?
    - Are "Requests" and "Applications" identical?
    - Can the Funding Case Types configured?


## Edit Funding Programs

An overview of funding programs can be found at **Funding > Funding Programs**. It lists all funding programs for which the current CiviCRM user has viewing permissions.
