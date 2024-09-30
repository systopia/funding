<?php
declare(strict_types = 1);

namespace Civi\Funding\SammelantragKurs\EventSubscriber;

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class KursAngularModuleSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_angularModules' => ['onAngularModules', -10]];
  }

  public function onAngularModules(GenericHookEvent $event): void {
    $event->angularModules['crmFunding']['requires'][] = 'crmFundingSammelantragKurs';
  }

}
