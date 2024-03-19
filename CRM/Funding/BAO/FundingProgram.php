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

final class CRM_Funding_BAO_FundingProgram extends CRM_Funding_DAO_FundingProgram implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.afform_admin.metadata' => 'afformAdminMetadata',
      'civi.afform.get' => 'afformGet',
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
    $event->entities['FundingProgram'] = [
      'entity' => 'FundingProgram',
      'label' => E::ts('Funding Program'),
      // TODO.
      'icon' => NULL,
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

  /**
   * Provides Afform(s) for this entity.
   */
  public static function afformGet(GenericHookEvent $event): void {
    // Early return if forms are not requested.
    if (is_array($event->getTypes) && !in_array('form', $event->getTypes, TRUE)) {
      return;
    }

    // TODO: Provide Afform for a funding program.
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public static function &fields() {
    $fields = parent::fields();
    // Currently needs to be set here,
    // see https://github.com/civicrm/civicrm-core/pull/29768.
    $fields['budget']['html']['step'] = 0.01;

    return $fields;
  }

}
