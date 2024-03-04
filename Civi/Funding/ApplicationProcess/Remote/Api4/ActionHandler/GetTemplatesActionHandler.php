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
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplatesAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

final class GetTemplatesActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private Api4Interface $api4;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager
  ) {
    $this->api4 = $api4;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @phpstan-return list<array{id: integer, label: string}>
   *
   * @throws \CRM_Core_Exception
   */
  public function getTemplates(GetTemplatesAction $action): array {
    $applicationProcess = $this->applicationProcessManager->get($action->getApplicationProcessId());
    if (NULL === $applicationProcess) {
      return [];
    }

    $fundingCase = $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
    Assert::notNull($fundingCase);

    /** @phpstan-var list<array{id: integer, label: string}> $templates */
    $templates = $this->api4->execute(FundingApplicationCiviOfficeTemplate::getEntityName(), 'get', [
      'select' => ['id', 'label'],
      'where' => [['case_type_id', '=', $fundingCase->getFundingCaseTypeId()]],
      'orderBy' => ['label' => 'ASC'],
    ])->getArrayCopy();

    return $templates;
  }

}
