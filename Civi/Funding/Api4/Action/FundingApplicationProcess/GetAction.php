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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractReferencingDAOGetAction {

  private ?FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL,
    ?FundingCaseTypeMetaDataProviderInterface $metaDataProvider = NULL
  ) {
    parent::__construct(
      FundingApplicationProcess::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
    $this->metaDataProvider = $metaDataProvider;
  }

  public function _run(Result $result): void {
    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      $this->addSelect('status');
      $this->addSelect('funding_case_id');
      $this->addSelect('funding_case_id.funding_case_type_id.name');
    }

    parent::_run($result);

    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        // @phpstan-ignore-next-line
        $record['CAN_open_clearing'] = $this->canOpenClearing($record);
        $this->unsetIfNotSelected($record, 'status');
        $this->unsetIfNotSelected($record, 'funding_case_id');
        $this->unsetIfNotSelected($record, 'funding_case_id.funding_case_type_id.name');
      }
    }
  }

  /**
   * @phpstan-param array{
   *   id: int,
   *   "funding_case_id.funding_case_type_id.name": string,
   *   status: string,
   *   funding_case_id: int,
   * } $record
   */
  private function canOpenClearing(array $record): bool {
    $metaData = $this->getMetaDataProvider()->get($record['funding_case_id.funding_case_type_id.name']);
    if (TRUE !== $metaData->getApplicationProcessStatus($record['status'])?->isClearingAvailable()) {
      return FALSE;
    }

    if (0 !== $this->getApi4()->countEntities(
        FundingClearingProcess::getEntityName(),
        CompositeCondition::new('AND',
          Comparison::new('application_process_id', '=', $record['id']),
          Comparison::new('status', '!=', 'not-started')
        ),
      )) {
      return TRUE;
    }

    $fundingCase = $this->getFundingCaseManager()->get($record['funding_case_id']);
    Assert::notNull($fundingCase);

    // If the clearing isn't started, yet, permission to modify or apply is required.
    return $fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_MODIFY) || $fundingCase->hasPermission(
        ClearingProcessPermissions::CLEARING_APPLY);
  }

  private function getMetaDataProvider(): FundingCaseTypeMetaDataProviderInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->metaDataProvider ??= \Civi::service(FundingCaseTypeMetaDataProviderInterface::class);
  }

}
