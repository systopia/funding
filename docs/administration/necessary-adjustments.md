# Necessary Adjustments

Necessary adjustments to represent the funding workflow of a specific giving
organization can be done via code in a custom extension.
If you need support in this, you can send an email to info@systopia.de.

The development of the funding framework was funded by the German organization _Arbeit und Leben_.
Therefore, the application forms and funding case types are in German.
The funding workflow as described [here](../usage/application-states.md)
reflects the workflow of this specific organization for the funding case types
AVK1, IJB, and Sammelantrag Kurs.

You will need to define your own custom funding case type. Choosing a funding case type for a funding program affects

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

For every funding case type there exists a folder in the funding extension, some files in `funding/ang` and a managed entity.
For example, these are relevant folders and files for the funding case type *Sonstige Aktivität (AVK1)*:

- `funding/Civi/Funding/SonstigeAktivitaet`
- `funding/ang/crmFundingAVK1SonstigeAktivitaet`
- `funding/ang/crmFundingAVK1SonstigeAktivitaet.ang.php`
- `funding/ang/crmFundingAVK1SonstigeAktivitaet.js`

Additionally, there exists a managed entity to make the funding case type
available in a custom extension. (This prevents that the funding case types are
available on systems where there are not required.)

`managed/FundingCaseType_AVK1.mgd.php`:
```php
<?php
declare(strict_types = 1);

return [
  [
    'name' => 'FundingCaseType_AVK1SonstigeAktivitaet',
    'entity' => 'FundingCaseType',
    'cleanup' => 'never',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'title' => 'Sonstige Aktivität (AVK1)',
        'abbreviation' => 'SoA',
        'name' => 'AVK1SonstigeAktivitaet',
        'is_combined_application' => FALSE,
        'application_process_label' => NULL,
        'properties' => NULL,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
];
```

For `properties` an array may be set. These attributes can be used optionally:

* `applicationAddableStatusList`: A new application is added to an existing funding case in any of the given status that has the same funding case type and the same funding program. If no such funding case exists, a new one is created. Only relevant for non-combined applications. (If empty or not specified a new funding case is created for every application.)
* `applicationEditorTagName`: The tag name of an AngularJS directive to use instead of the default application editor for reviewers.
* `applicationReviewSidebarTagName`: The tag name of an AngularJS directive to use instead of the default review sidebar.
* `applicationFormTagName`: The tag name of an AngularJS directive to use instead of the default application form for reviewers.

If a custom AngularJS directive shall be used, the module containing it has to
be registered as a requirement of the `crmFunding` module. This can be achieved
with an event subscriber like this:

```php
declare(strict_types = 1);

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

namespace Civi\MyExtension\EventSubscriber;

final class MyAngularModuleSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_angularModules' => ['onAngularModules', -10]];
  }

  public function onAngularModules(GenericHookEvent $event): void {
    $event->angularModules['crmFunding']['requires'][] = 'MyAngularModule';
  }

}
```

The subscriber can be registered in the DI container like this:

```php
$container->autowire(MyAngularModuleSubscriber::class)
  ->addTag('kernel.event_subscriber');
```
