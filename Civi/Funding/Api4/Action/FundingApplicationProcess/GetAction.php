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
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractReferencingDAOGetAction {

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    RequestContextInterface $requestContext
  ) {
    parent::__construct(
      FundingApplicationProcess::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
  }

  public function _run(Result $result): void {
    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      $this->addSelect('is_eligible');
      $this->addSelect('funding_case_id');
    }

    parent::_run($result);

    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        // @phpstan-ignore-next-line
        $record['CAN_open_clearing'] = $this->canOpenClearing($record);
      }
    }

    $clearingProcessFields = array_intersect([
      'amount_cleared',
      'amount_admitted',
    ], $this->getSelect());
    if ([] !== $clearingProcessFields) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $clearingProcessAmounts = $this->_api4->execute(FundingClearingProcess::getEntityName(), 'get', [
          'select' => $clearingProcessFields,
          'where' => [
            ['application_process_id', '=', $record['id']],
          ],
        ])->first();

        foreach ($clearingProcessFields as $field) {
          $record[$field] = $clearingProcessAmounts[$field] ?? NULL;
        }
      }
    }
  }

  /**
   * @phpstan-param array{id: int, is_eligible: bool|null, funding_case_id: int} $record
   */
  private function canOpenClearing(array $record): bool {
    if (TRUE !== $record['is_eligible']) {
      return FALSE;
    }

    if (0 !== $this->_api4->countEntities(
        FundingClearingProcess::getEntityName(),
        Comparison::new(
          'application_process_id',
          '=',
          $record['id']
        )
      )) {
      return TRUE;
    }

    $fundingCase = $this->_fundingCaseManager->get($record['funding_case_id']);
    Assert::notNull($fundingCase);

    return $fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_MODIFY) || $fundingCase->hasPermission(
        ClearingProcessPermissions::CLEARING_APPLY);
  }

}
