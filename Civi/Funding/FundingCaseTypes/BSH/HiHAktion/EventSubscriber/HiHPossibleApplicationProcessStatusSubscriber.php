<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\EventSubscriber;

use Civi\Funding\Event\ApplicationProcess\GetPossibleApplicationProcessStatusEvent;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHConstants;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CRM_Funding_ExtensionUtil as E;

final class HiHPossibleApplicationProcessStatusSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetPossibleApplicationProcessStatusEvent::class => 'onGetPossibleApplicationProcessStatus'];
  }

  public function onGetPossibleApplicationProcessStatus(GetPossibleApplicationProcessStatusEvent $event): void {
    if (HiHConstants::FUNDING_CASE_TYPE_NAME === $event->getFundingCaseTypeName()) {
      $optionNames = ['new', 'draft', 'withdrawn', 'applied', 'review', 'rejected', 'eligible', 'complete'];
      $options = array_filter(
        $event->getOptions(),
        fn (array $option) => in_array($option['name'], $optionNames, TRUE)
      );
      $event->setOptions($options);

      $event->addOption([
        'id' => 'advisory',
        'name' => 'advisory',
        'label' => E::ts('Advisory'),
        'icon' => 'fa-eye',
      ]);

      $event->stopPropagation();
    }
  }

}
