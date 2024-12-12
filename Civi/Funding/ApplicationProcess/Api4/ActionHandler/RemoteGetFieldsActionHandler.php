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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

final class RemoteGetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(Api4Interface $api4, OptionsLoaderInterface $optionsLoader) {
    parent::__construct($api4);
    $this->optionsLoader = $optionsLoader;
  }

  public function getFields(AbstractRemoteGetFieldsAction $action): Result {
    $fields = parent::getFields($action);
    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_clearing_process.status',
      'nullable' => TRUE,
      'title' => E::ts('Clearing Status'),
      'data_type' => 'String',
      'options' => $this->optionsLoader->getOptions(FundingClearingProcess::getEntityName(), 'status'),
      'input_type' => 'Select',
    ];

    return $fields;
  }

  protected function getEntityName(): string {
    return FundingApplicationProcess::getEntityName();
  }

}
