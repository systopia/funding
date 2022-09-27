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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CRM_Funding_BAO_FundingCaseTypeProgram extends CRM_Funding_DAO_FundingCaseTypeProgram implements EventSubscriberInterface {

  /**
   * Create a new FundingCaseTypeProgram based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Funding_DAO_FundingCaseTypeProgram|NULL
   *
  public static function create($params) {
    $className = 'CRM_Funding_DAO_FundingCaseTypeProgram';
    $entityName = 'FundingCaseTypeProgram';
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
    ];
  }

  /**
   * Provides Afform metadata about this entity.
   *
   * @see \Civi\AfformAdmin\AfformAdminMeta::getMetadata().
   */
  public static function afformAdminMetadata(GenericHookEvent $event): void {
    $entity = pathinfo(__FILE__, PATHINFO_FILENAME);
    $event->entities[$entity] = [
      'entity' => $entity,
      'label' => $entity,
      'icon' => NULL, // TODO.
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

}
