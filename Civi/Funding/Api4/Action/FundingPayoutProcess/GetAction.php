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

namespace Civi\Funding\Api4\Action\FundingPayoutProcess;

use Civi\Api4\FundingCase;
use Civi\Api4\FundingPayoutProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Api4\Util\FundingCasePermissionsUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class GetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  private Api4Interface $api4;

  private FundingCaseManager $fundingCaseManager;

  private RequestContextInterface $requestContext;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    RequestContextInterface $requestContext
  ) {
    parent::__construct(FundingPayoutProcess::getEntityName(), 'get');
    $this->api4 = $api4;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->requestContext = $requestContext;
  }

  public function _run(Result $result): void {
    $rowCountSelected = $this->isRowCountSelected();
    if ($rowCountSelected) {
      $this->ensureFundingCasePermissions();
    }

    FundingCasePermissionsUtil::addPermissionsCacheJoin(
      $this,
      'funding_case_id',
      $this->requestContext->getContactId(),
      $this->requestContext->isRemote()
    );
    FundingCasePermissionsUtil::addPermissionsRestriction($this);

    $fundingCaseIdSelected = $this->isFundingCaseIdSelected();
    if (!$fundingCaseIdSelected) {
      $this->addSelect('funding_case_id');
    }

    $limit = $this->getLimit();
    $offset = $this->getOffset();
    $records = [];
    do {
      parent::_run($result);

      /** @phpstan-var array<string, mixed>&array{funding_case_id: int} $record */
      foreach ($result as $record) {
        if ($this->fundingCaseManager->hasAccess($record['funding_case_id'])) {
          if (!$fundingCaseIdSelected) {
            unset($record['funding_case_id']);
          }

          $records[] = $record;
        }
      }

      $limitBefore = $this->getLimit();
      $this->setOffset($offset + count($records));
      $this->setLimit($limit - count($records));
    } while ($this->getLimit() > 0 && count($result) === $limitBefore);

    $result->exchangeArray($records);
    if (!$rowCountSelected) {
      $result->rowCount = count($records);
    }
  }

  private function ensureFundingCasePermissions(): void {
    $action = FundingCase::get(FALSE)
      ->addSelect('DISTINCT id');

    $fundingCaseId = WhereUtil::getInt($this->getWhere(), 'funding_case_id');
    if (NULL !== $fundingCaseId) {
      $action->addWhere('id', '=', $fundingCaseId);
    }
    else {
      $action->addJoin(
        FundingPayoutProcess::getEntityName() . ' AS pp',
        'INNER',
        NULL,
        ['pp.funding_case_id', '=', 'id']
      );
    }

    $this->api4->executeAction($action);
  }

  private function isFundingCaseIdSelected(): bool {
    return $this->isFieldSelected('funding_case_id');
  }

}
