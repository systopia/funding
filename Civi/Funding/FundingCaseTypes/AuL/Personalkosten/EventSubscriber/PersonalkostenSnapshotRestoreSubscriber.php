<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber;

use Civi\Funding\Event\ApplicationProcess\ApplicationSnapshotRestoredEvent;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\PersonalkostenApplicationProcessUpdater;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersonalkostenSnapshotRestoreSubscriber implements EventSubscriberInterface {

  private PersonalkostenApplicationProcessUpdater $personalkostenApplicationProcessUpdater;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationSnapshotRestoredEvent::class => 'onRestored',
    ];
  }

  public function __construct(PersonalkostenApplicationProcessUpdater $personalkostenApplicationProcessUpdater) {
    $this->personalkostenApplicationProcessUpdater = $personalkostenApplicationProcessUpdater;
  }

  public function onRestored(ApplicationSnapshotRestoredEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== PersonalkostenMetaData::NAME) {
      return;
    }

    $fundingProgram = $event->getFundingProgram();
    /** @var int $foerderquote */
    $foerderquote = $fundingProgram->get('funding_program_extra.foerderquote');
    /** @var float $sachkostenpauschale */
    $sachkostenpauschale = $fundingProgram->get('funding_program_extra.sachkostenpauschale');

    $restoredRequestData = $event->getApplicationProcess()->getRequestData();
    $restoredFoerderquote = $restoredRequestData['foerderquote'];
    // @phpstan-ignore cast.double
    $restoredSachkostenpauschale = (float) $restoredRequestData['sachkostenpauschale'];

    if ($foerderquote !== $restoredFoerderquote || $sachkostenpauschale !== $restoredSachkostenpauschale) {
      $this->personalkostenApplicationProcessUpdater->updateApplicationProcess(
        $event->getApplicationProcessBundle(),
        $foerderquote,
        $sachkostenpauschale,
      );
    }
  }

}
