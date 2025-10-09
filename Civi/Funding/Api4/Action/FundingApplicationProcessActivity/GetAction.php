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

namespace Civi\Funding\Api4\Action\FundingApplicationProcessActivity;

use Civi\Api4\Activity;
use Civi\Api4\Contact;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationProcessActivity;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\Api4Trait;
use Civi\Funding\Api4\Action\Traits\ApplicationProcessIdParameterTrait;
use Civi\Funding\Api4\Action\Traits\ApplicationProcessManagerTrait;
use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractGetAction {

  use ApplicationProcessIdParameterTrait;

  use Api4Trait;

  use ApplicationProcessManagerTrait;

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?ApplicationProcessManager $applicationProcessManager = NULL
  ) {
    parent::__construct(FundingApplicationProcessActivity::getEntityName(), 'get');
    $this->_api4 = $api4;
    $this->_applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    Assert::notNull($this->applicationProcessId);

    // Ensure access permission is granted
    $applicationProcess = $this->getApplicationProcessManager()->get($this->applicationProcessId);
    if (NULL === $applicationProcess) {
      return;
    }

    $action = Activity::get(FALSE)
      ->addJoin(
        FundingApplicationProcess::getEntityName() . ' AS ap', 'INNER', 'EntityActivity',
        ['ap.id', '=', $this->applicationProcessId]
      )->setWhere($this->getWhere())
      ->setLimit($this->getLimit())
      ->setOffset($this->getOffset())
      ->setOrderBy($this->getOrderBy())
      ->setSelect($this->getSelect())
      ->addSelect('activity_type_id:name')
      ->setDebug($this->getDebug());

    if ([] === $this->getSelect()) {
      $action->addSelect('*', 'custom.*');
    }

    if ([] === $this->getOrderBy()) {
      $action->addOrderBy('id', 'DESC');
    }

    $getResult = $this->getApi4()->executeAction($action);
    $result->debug = $getResult->debug;
    /** @phpstan-var array{id: int, "activity_type_id:name"?: string}&array<string, mixed> $record */
    foreach ($getResult as $record) {
      $record['source_contact_name'] = $this->getSourceContactName($record);
      if (isset($record['activity_type_id:name'])) {
        $result->append($this->flattenCustomFieldNames($record));
      }
    }
  }

  /**
   * Adds custom fields stripped by custom group names to the given record.
   *
   * @phpstan-param array<string, mixed> $record
   *
   * @phpstan-return array<string, mixed>
   */
  private function flattenCustomFieldNames(array $record): array {
    foreach ($record as $field => $value) {
      if (str_contains($field, '.')) {
        [$activityTypeName, $flatField] = explode('.', $field, 2);
        if ($record['activity_type_id:name'] === $activityTypeName && !array_key_exists($flatField, $record)) {
          $record[$flatField] = $value;
        }
      }
    }

    return $record;
  }

  /**
   * @phpstan-param array{id: int} $activity
   *
   * @throws \CRM_Core_Exception
   */
  private function getSourceContactName(array $activity): string {
    $action = Contact::get(FALSE)
      ->addJoin('ActivityContact AS ac', 'INNER', NULL, ['id', '=', 'ac.contact_id'])
      ->addSelect('id', 'display_name')
      ->addWhere('ac.activity_id', '=', $activity['id'])
      ->addWhere('ac.record_type_id:name', '=', 'Activity Source');

    /** @phpstan-var array{id: int, display_name: ?string}|null $contact */
    $contact = $this->getApi4()->executeAction($action)->first();
    if (NULL === $contact) {
      return '-';
    }

    return ContactUtil::getDisplayName($contact);
  }

}
