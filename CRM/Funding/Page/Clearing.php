<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

use Civi\Api4\FundingClearingProcess;

final class CRM_Funding_Page_Clearing extends CRM_Core_Page {

  public function run(): void {
    /** @var \Civi\Angular\AngularLoader $angularLoader */
    $angularLoader = Civi::service('angularjs.loader');
    $angularLoader->addModules(['crmFunding', 'crmUi']);

    $this->assign('applicationProcessId', $this->getApplicationProcessId());

    parent::run();
  }

  private function getApplicationProcessId(): int {
    /** @var positive-int|null $applicationProcessId */
    $applicationProcessId = CRM_Utils_Request::retrieve('applicationProcessId', 'Positive');
    if (NULL !== $applicationProcessId) {
      return $applicationProcessId;
    }

    $clearingProcessId = CRM_Utils_Request::retrieve('id', 'Positive', abort: TRUE);

    return FundingClearingProcess::get(FALSE)
      ->addSelect('application_process_id')
      ->addWhere('id', '=', $clearingProcessId)
      ->execute()
      ->single()['application_process_id'];
  }

}
