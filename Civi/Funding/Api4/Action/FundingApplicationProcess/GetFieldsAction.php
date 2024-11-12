<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type fieldsT array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseContactsLoaderInterface $contactsLoader;

  private FundingCaseManager $fundingCaseManager;

  private bool $fundingCaseLoaded = FALSE;

  private ?FundingCaseEntity $fundingCase = NULL;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseContactsLoaderInterface $contactsLoader,
    FundingCaseManager $fundingCaseManager
  ) {
    parent::__construct(FundingApplicationProcess::getEntityName(), 'getFields');
    $this->applicationProcessManager = $applicationProcessManager;
    $this->contactsLoader = $contactsLoader;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @phpstan-return fieldsT
   */
  protected function getRecords(): array {
    $fields = parent::getRecords();
    foreach ($fields as &$field) {
      if ('reviewer_calc_contact_id' === $field['name']) {
        // @todo Make permission depend on funding case type.
        $field['options'] = $this->getReviewerContactOptions('review_calculative');
      }
      elseif ('reviewer_cont_contact_id' === $field['name']) {
        // @todo Make permission depend on funding case type.
        $field['options'] = $this->getReviewerContactOptions('review_content');
      }
    }

    $fields[] = [
      'name' => 'currency',
      'title' => E::ts('Currency'),
      'type' => 'Extra',
      'data_type' => 'String',
      'readonly' => TRUE,
      'sql_renderer' => new AliasSqlRenderer('funding_case_id.funding_program_id.currency'),
    ];
    $fields[] = [
      'name' => 'amount_cleared',
      'title' => E::ts('Amount Cleared'),
      'type' => 'Custom',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => TRUE,
      'operators' => [],
    ];
    $fields[] = [
      'name' => 'amount_admitted',
      'title' => E::ts('Amount Admitted'),
      'type' => 'Custom',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => TRUE,
      'operators' => [],
    ];
    $fields[] = [
      'name' => 'CAN_open_clearing',
      'type' => 'Extra',
      'data_type' => 'Boolean',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'operators' => [],
      // Without sql renderer the query would fail. The actual value is fetched afterward.
      'sql_renderer' => fn () => '(SELECT NULL)',
    ];

    return $fields;
  }

  private function getFundingCaseFromValues(): ?FundingCaseEntity {
    if (!$this->fundingCaseLoaded) {
      if (isset($this->values['id'])) {
        $applicationProcess = $this->applicationProcessManager->get($this->values['id']);
        $this->fundingCase = NULL === $applicationProcess ? NULL :
          $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
      }
      $this->fundingCaseLoaded = TRUE;
    }

    return $this->fundingCase;
  }

  /**
   * @param string $permission
   *
   * @phpstan-return array<int, string>|bool
   */
  private function getReviewerContactOptions(string $permission) {
    if (FALSE === $this->loadOptions) {
      // Show contact selection field in Afforms.
      return FALSE;
    }

    $fundingCase = $this->getFundingCaseFromValues();
    if (NULL === $fundingCase) {
      return TRUE;
    }

    $contacts = $this->contactsLoader->getContactsWithPermission($fundingCase, $permission);

    /** @phpstan-ignore-next-line */
    return array_map(fn (array $contact) => ContactUtil::getDisplayName($contact), $contacts);
  }

}
