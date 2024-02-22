# Necessary adjustments

Some adjustments that are necessary to represent the funding workflow of a specific giving organization can not be done via configuration pages.
We recommend writing an additional extension that contains the necessary adaptations.
If you need support in this, you can send an email to info@systopia.de.

The development of the funding framework was funded by the German organization _Arbeit und Leben_.
Therefore, the application forms and funding case types are in German.
The funding workflow as described [here](../usage/application-states.md) reflects the workflow of this specific organization for the funding case types AVK1 and IJB.

You will need to define your own funding case type. Choosing a funding case type for a funding program affects

* the form that is shown when creating a new application.
* the possible states of an application.
* the actions that are available in different states of an application.
* which actions lead to a status change.

## Examples

The funding framework defines three different funding case types that can serve as examples:

* Sonstige Aktivität (AVK1)
* Internationale Jugendbegegnung (IJB)
* Sammelantrag Kurs

The funding framework also defines two kinds of applications: Normal Applications and Combined Applications.
_Sammelantrag Kurs_ is a combined application, the other funding case types are normal applications.

For every funding case type there exists a folder in the funding extension and some files in `funding/ang`. The folders corresponding to the existing funding case types are `funding/Civi/Funding/SonstigeAktivitaet`, `funding/Civi/Funding/IJB` and `funding/Civi/Funding/SammelantragKurs`.
