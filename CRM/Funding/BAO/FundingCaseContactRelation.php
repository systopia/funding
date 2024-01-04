<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// phpcs:disable Generic.Files.LineLength.TooLong
final class CRM_Funding_BAO_FundingCaseContactRelation extends CRM_Funding_DAO_FundingCaseContactRelation implements EventSubscriberInterface {
// phpcs:enable

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.afform_admin.metadata' => 'afformAdminMetadata',
    ];
  }

  /**
   * Provides Afform metadata about this entity.
   *
   * @see \Civi\AfformAdmin\AfformAdminMeta::getMetadata()
   */
  public static function afformAdminMetadata(GenericHookEvent $event): void {
    $entity = pathinfo(__FILE__, PATHINFO_FILENAME);
    $event->entities[$entity] = [
      'entity' => $entity,
      'label' => $entity,
      // TODO.
      'icon' => NULL,
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

}
