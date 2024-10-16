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

namespace Civi\Funding\Notification;

use Civi\Api4\MessageTemplate;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\RemoteTools\Api4\Api4Interface;

class NotificationWorkflowDeterminer {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getWorkflowName(
    string $workflowNamePostfix,
    FundingCaseTypeEntity $fundingCaseType
  ): ?string {
    $workflow = $this->getWorkflow(
      sprintf('funding.case_type:%s.%s', $fundingCaseType->getName(), $workflowNamePostfix)
    ) ?? $this->getWorkflow(sprintf('funding.%s', $workflowNamePostfix));
    if (NULL !== $workflow && $workflow['is_active']) {
      return $workflow['workflow_name'];
    }

    return NULL;
  }

  /**
   * @phpstan-return array{workflow_name: string, is_active: bool}|null
   *
   * @throws \CRM_Core_Exception
   */
  private function getWorkflow(string $workflowName): ?array {
    // @phpstan-ignore return.type
    return $this->api4->execute(MessageTemplate::getEntityName(), 'get', [
      'select' => ['workflow_name', 'is_active'],
      'where' => [['workflow_name', '=', $workflowName]],
    ])->first();
  }

}
