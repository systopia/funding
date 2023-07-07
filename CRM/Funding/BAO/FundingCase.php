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

use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\Event\FundingCase\GetPossibleFundingCaseStatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CRM_Funding_ExtensionUtil as E;

class CRM_Funding_BAO_FundingCase extends CRM_Funding_DAO_FundingCase implements EventSubscriberInterface {

  /**
   * Create a new FundingCase based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Funding_DAO_FundingCase|NULL
   *
  public static function create($params) {
    $className = 'CRM_Funding_DAO_FundingCase';
    $entityName = 'FundingCase';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

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
   * @see \Civi\AfformAdmin\AfformAdminMeta::getMetadata().
   *
   * TODO: Replace with "afformEntities/*.php" files?
   *       See civicrm/civicrm-core/mixin/afform-entity-php@1/mixin.php.
   */
  public static function afformAdminMetadata(GenericHookEvent $event): void {
    $event->entities['FundingCase'] = [
      'entity' => 'FundingCase',
      'label' => E::ts('Funding Case'),
      'icon' => NULL, // TODO.
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

    // TODO: Provide Afform for a funding case.
  }

}
