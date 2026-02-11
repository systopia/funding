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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\SubmitFormAction;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-import-type submitResultT from SubmitFormActionHandler
 */
final class RemoteSubmitFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingClearingProcess';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return array{
   *   action: RemoteSubmitResponseActions::SHOW_VALIDATION,
   *   message: string,
   *   errors: non-empty-array<string, non-empty-list<string>>,
   * } | array{
   *   action: RemoteSubmitResponseActions::CLOSE_FORM|RemoteSubmitResponseActions::RELOAD_FORM,
   *   message: string,
   *   files: non-empty-array<string, string>|\stdClass,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitForm(SubmitFormAction $action): array {
    /** @phpstan-var submitResultT $result */
    $result = $this->api4->execute(FundingClearingProcess::getEntityName(), 'submitForm', [
      'id' => $action->getId(),
      'data' => $action->getData(),
    ])->getArrayCopy();

    if (!$result['errors'] instanceof \stdClass) {
      return [
        'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
        'message' => E::ts('Validation failed'),
        'files' => new \stdClass(),
        'errors' => $result['errors'],
      ];
    }

    return [
      'action' => in_array($action->getData()['_action'] ?? NULL, ['save', 'modify'], TRUE)
      ? RemoteSubmitResponseActions::RELOAD_FORM
      : RemoteSubmitResponseActions::CLOSE_FORM,
      'message' => E::ts('Saved'),
      'files' => $result['files'],
    ];
  }

}
