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

namespace Civi\Funding\Api4\Action\Generic\FinancePlanItem;

use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

class GetAction extends AbstractReferencingDAOGetAction {

  public function __construct(
    string $entityName,
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(
      $entityName,
      $api4,
      $fundingCaseManager,
      $requestContext
    );
    $this->_fundingCaseIdFieldName = 'application_process_id.funding_case_id';
  }

  public function _run(Result $result): void {
    $this->initOriginalSelect();

    if ([] === $this->getSelect()) {
      $this->setSelect(['*']);
    }

    if ($this->isFieldExplicitlySelected('type_label')
      && !$this->isFieldExplicitlySelected('funding_case_type')
    ) {
      $this->addSelect('funding_case_type');
    }

    parent::_run($result);
  }

  protected function handleRecord(array &$record): bool {
    if (!parent::handleRecord($record)) {
      return FALSE;
    }

    $this->unsetIfNotSelected($record, 'funding_case_type');

    return TRUE;
  }

}
