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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess\Traits;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

/**
 * This trait permits to set the review flags if permission is missing. To be
 * used in create, update, and save actions.
 */
trait CheckReviewPermissionTrait {

  protected Api4Interface $_api4;

  protected FundingCaseManager $_fundingCaseManager;

  /**
   * @phpstan-param array<string, mixed>&array{
   *   id?: int,
   *   funding_case_id?: int,
   *   is_review_calculative?: bool|null,
   *   is_review_content?: bool|null
   * } $record
   *
   * @throws \API_Exception
   *
   * @phpstan-ignore-next-line
   *   Ignore $record not being contravariant with array.
   */
  protected function formatWriteValues(&$record): void {
    if (!array_key_exists('is_review_calculative', $record) && !array_key_exists('is_review_content', $record)) {
      return;
    }

    $fundingCaseId = $this->getFundingCaseId($record);
    $fundingCase = $this->_fundingCaseManager->get($fundingCaseId);
    $permissions = NULL === $fundingCase ? [] : $fundingCase->getPermissions();

    if (array_key_exists('is_review_calculative', $record) && !in_array('review_calculative', $permissions, TRUE)) {
      $reviewFlags = $this->getReviewFlags($record['id'] ?? NULL);
      $record['is_review_calculative'] = $reviewFlags['is_review_calculative'];
    }

    if (array_key_exists('is_review_content', $record) && !in_array('review_content', $permissions, TRUE)) {
      $reviewFlags ??= $this->getReviewFlags($record['id'] ?? NULL);
      $record['is_review_content'] = $reviewFlags['is_review_content'];
    }

    parent::formatWriteValues($record);
  }

  /**
   * @phpstan-param array<string, mixed>&array{
   *   id?: int,
   *   funding_case_id?: int,
   *   is_review_calculative?: bool|null,
   *   is_review_content?: bool|null
   * } $record
   *
   * @throws \API_Exception
   */
  private function getFundingCaseId(array $record): int {
    if (isset($record['funding_case_id'])) {
      return $record['funding_case_id'];
    }

    $id = $record['id'] ?? $this->getApplicationProcessIdFromWhere();
    if (NULL === $id) {
      throw new \API_Exception('Neither funding case ID nor application process ID available');
    }

    $action = (new DAOGetAction(FundingApplicationProcess::_getEntityName(), 'get'))
      ->addSelect('funding_case_id')
      ->addWhere('id', '=', $id);

    $firstResult = $this->_api4->executeAction($action)->first();
    Assert::notNull($firstResult, sprintf('Invalid application process id %d', $id));

    return $firstResult['funding_case_id'];
  }

  private function getApplicationProcessIdFromWhere(): ?int {
    foreach ($this->where ?? [] as $clause) {
      if ($clause[0] === 'id' && '=' === $clause[1] && is_numeric($clause[2])) {
        return (int) $clause[2];
      }
    }

    return NULL;
  }

  /**
   * @phpstan-return array{is_review_calculative: bool|null, is_review_content: bool|null}
   *
   * @throws \API_Exception
   */
  private function getReviewFlags(?int $id): array {
    $initial = ['is_review_calculative' => NULL, 'is_review_content' => NULL];
    if (NULL === $id) {
      return $initial;
    }

    $action = new DAOGetAction(FundingApplicationProcess::_getEntityName(), 'get');
    $action
      ->addSelect('is_review_calculative', 'is_review_content')
      ->addWhere('id', '=', $id);

    /** @phpstan-var array{is_review_calculative: bool|null, is_review_content: bool|null} $reviewFlags */
    $reviewFlags = $this->_api4->executeAction($action)->first() ?? $initial;

    return $reviewFlags;
  }

}
