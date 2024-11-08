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

namespace Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler;

use Civi\Api4\FundingApplicationCiviOfficeTemplate;
use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplatesAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class GetTemplatesActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return list<array{id: integer, label: string}>
   *
   * @throws \CRM_Core_Exception
   */
  public function getTemplates(GetTemplatesAction $action): array {
    $fundingCaseTypeId = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => ['funding_case_id.funding_case_type_id'],
      'where' => [['id', '=', $action->getApplicationProcessId()]],
    ])->first()['funding_case_id.funding_case_type_id'] ?? NULL;

    if (NULL === $fundingCaseTypeId) {
      return [];
    }

    /** @phpstan-var list<array{id: integer, label: string}> $templates */
    $templates = $this->api4->execute(FundingApplicationCiviOfficeTemplate::getEntityName(), 'get', [
      'select' => ['id', 'label'],
      'where' => [['case_type_id', '=', $fundingCaseTypeId]],
      'orderBy' => ['label' => 'ASC'],
    ])->getArrayCopy();

    return $templates;
  }

}
