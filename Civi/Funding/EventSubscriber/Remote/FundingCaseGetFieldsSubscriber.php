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

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Funding\Event\Remote\FundingGetFieldsEvent;
use Civi\RemoteTools\Event\GetFieldsEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteGetFieldsSubscriber;
use CRM_Funding_ExtensionUtil as E;

final class FundingCaseGetFieldsSubscriber extends AbstractRemoteGetFieldsSubscriber {

  protected const BASIC_ENTITY_NAME = 'FundingCase';

  protected const ENTITY_NAME = 'RemoteFundingCase';

  protected const EVENT_CLASS = FundingGetFieldsEvent::class;

  public function onGetFields(GetFieldsEvent $event): void {
    parent::onGetFields($event);

    $event->addField([
      'nullable' => FALSE,
      'name' => 'funding_case_type_id.is_combined_application',
      'title' => E::ts('Is Combined Application'),
      'data_type' => 'Boolean',
      'serialize' => NULL,
      'options' => FALSE,
      'label' => E::ts('Is Combined Application'),
      'operators' => NULL,
    ]);

    $event->addField([
      'nullable' => TRUE,
      'name' => 'funding_case_type_id.application_process_label',
      'title' => E::ts('Application Process Label'),
      'data_type' => 'String',
      'serialize' => NULL,
      'options' => FALSE,
      'label' => E::ts('Application Process Label'),
      'operators' => NULL,
    ]);
  }

}
