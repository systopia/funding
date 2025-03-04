<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class RemoteValidateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-list<string>>|\stdClass,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateForm(ValidateFormAction $action): array {
    $result = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'validateForm', [
      'data' => $action->getData(),
      'id' => $action->getApplicationProcessId(),
    ]);

    // @phpstan-ignore return.type
    return [
      'valid' => $result['valid'],
      'errors' => $result['errors'],
    ];
  }

}
