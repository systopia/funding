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
use Webmozart\Assert\Assert;

/**
 * @phpstan-type applicationTemplateT array{id: int, label: string}
 */
final class GetTemplatesActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return list<applicationTemplateT>|array<int, list<applicationTemplateT>>
   *
   * @throws \CRM_Core_Exception
   */
  public function getTemplates(GetTemplatesAction $action): array {
    if (NULL !== $action->getApplicationProcessId()) {
      Assert::null(
        $action->getApplicationProcessIds(),
        'Only "applicationProcessId" or "applicationProcessIds" is allowed'
      );

      return $this->getSingle($action->getApplicationProcessId());
    }
    elseif (NULL !== $action->getApplicationProcessIds()) {
      return $this->getMultiple($action->getApplicationProcessIds());
    }

    throw new \InvalidArgumentException('Either "applicationProcessId" or "applicationProcessIds" must be set');
  }

  /**
   * @phpstan-return list<array{id: integer, label: string}>
   *
   * @throws \CRM_Core_Exception
   */
  private function getSingle(int $applicationProcessId) {
    $fundingCaseTypeId = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => ['funding_case_id.funding_case_type_id'],
      'where' => [['id', '=', $applicationProcessId]],
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

  /**
   * @phpstan-param list<int> $applicationProcessIds
   *
   * @phpstan-return array<int, list<applicationTemplateT>>
   *
   * @throws \CRM_Core_Exception
   */
  private function getMultiple(array $applicationProcessIds): array {
    if ([] === $applicationProcessIds) {
      return [];
    }

    $result = array_fill_keys($applicationProcessIds, []);
    $joinRecords = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => [
        'id',
        'tpl.id',
        'tpl.label',
      ],
      'where' => [
        ['id', 'IN', $applicationProcessIds],
      ],
      'join' => [
        [
          'FundingApplicationCiviOfficeTemplate AS tpl',
          'INNER',
          ['funding_case_id.funding_case_type_id', '=', 'tpl.case_type_id'],
        ],
      ],
    ]);

    /** @phpstan-var array{id: int, 'tpl.id': int, 'tpl.label': string} $joinRecord */
    foreach ($joinRecords as $joinRecord) {
      $result[$joinRecord['id']][] = ['id' => $joinRecord['tpl.id'], 'label' => $joinRecord['tpl.label']];
    }

    return $result;
  }

}
