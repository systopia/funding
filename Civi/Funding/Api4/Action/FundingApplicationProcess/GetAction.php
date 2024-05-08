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
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

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
    parent::_run($result);

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
            // @phpstan-ignore-next-line
            CompositeCondition::fromFieldValuePairs([
              'application_process_id' => $record['id'],
              'status' => 'accepted',
            ])->toArray(),
          ],
        ])->first();

        foreach ($clearingProcessFields as $field) {
          $record[$field] = $clearingProcessAmounts[$field] ?? NULL;
        }
      }
    }
  }

}
