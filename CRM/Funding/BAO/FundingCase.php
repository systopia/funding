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
use CRM_Funding_ExtensionUtil as E;

final class CRM_Funding_BAO_FundingCase extends CRM_Funding_DAO_FundingCase implements EventSubscriberInterface {

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
   *
   * TODO: Replace with "afformEntities/*.php" files?
   *       See civicrm/civicrm-core/mixin/afform-entity-php@1/mixin.php.
   */
  public static function afformAdminMetadata(GenericHookEvent $event): void {
    $event->entities['FundingCase'] = [
      'entity' => 'FundingCase',
      'label' => E::ts('Funding Case'),
      // TODO.
      'icon' => NULL,
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

}
