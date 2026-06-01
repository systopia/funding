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

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\Api4\FundingCaseTypeProgram;
use Civi\Api4\FundingFormStringTranslation;
use Civi\Api4\FundingNewCasePermissions;
use Civi\Api4\FundingProgram;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Api4\FundingRecipientContactRelation;
use Civi\Funding\Api4\Action\FundingProgram\CloneAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

class CloneHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingProgram';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * Clones the funding program.
   *
   * @param \Civi\Funding\Api4\Action\FundingProgram\CloneAction $action
   *
   * @return array
   * @throws \CRM_Core_Exception
   */
  public function clone(CloneAction $action): array {
    $sourceId = $action->getId();

    // 1. Get source program
    $sourceProgram = $this->api4->execute(FundingProgram::getEntityName(), 'get', [
      'where' => [['id', '=', $sourceId]],
      'checkPermissions' => $action->getCheckPermissions(),
    ])->first();

    if (!$sourceProgram) {
      throw new \CRM_Core_Exception(sprintf('Source FundingProgram with ID %d not found.', $sourceId));
    }

    // 2. Prepare data for new program
    $newProgramData = $sourceProgram;
    unset($newProgramData['id']);

    if (empty($action->getValues()['title'])) {
      $newProgramData['title'] = $this->getUniqueValue(
        FundingProgram::getEntityName(),
        'title',
        E::ts('Copy of %1', [1 => $sourceProgram['title']]),
        255,
        ' '
      );
    }

    if (empty($action->getValues()['abbreviation'])) {
      $newProgramData['abbreviation'] = $this->getUniqueValue(
        FundingProgram::getEntityName(),
        'abbreviation',
        $sourceProgram['abbreviation'] . '_copy',
        20,
        '_'
      );
    }

    // Override with values from action
    $newProgramData = array_merge($newProgramData, $action->getValues());

    // 3. Create new program
    $newProgram = $this->api4->execute(FundingProgram::getEntityName(), 'create', [
      'values' => $newProgramData,
      'checkPermissions' => $action->getCheckPermissions(),
    ])->first();

    $newId = $newProgram['id'];

    // 4. Clone related entities
    $this->cloneRelatedEntities(FundingCaseTypeProgram::getEntityName(), 'funding_program_id', $sourceId, $newId, $action->getCheckPermissions());
    $this->cloneRelatedEntities(FundingProgramContactRelation::getEntityName(), 'funding_program_id', $sourceId, $newId, $action->getCheckPermissions());
    $this->cloneRelatedEntities(FundingRecipientContactRelation::getEntityName(), 'funding_program_id', $sourceId, $newId, $action->getCheckPermissions());
    $this->cloneRelatedEntities(FundingNewCasePermissions::getEntityName(), 'funding_program_id', $sourceId, $newId, $action->getCheckPermissions());
    $this->cloneRelatedEntities(FundingFormStringTranslation::getEntityName(), 'funding_program_id', $sourceId, $newId, $action->getCheckPermissions());

    return [$newProgram];
  }

  /**
   * Generates a unique value for a field.
   */
  private function getUniqueValue(string $entityName, string $fieldName, string $baseValue, int $maxLength, string $separator): string {
    $value = mb_substr($baseValue, 0, $maxLength);
    $counter = 1;
    while (TRUE) {
      $count = $this->api4->execute($entityName, 'get', [
        'select' => ['row_count'],
        'where' => [[$fieldName, '=', $value]],
        'checkPermissions' => FALSE,
      ])->count();

      if ($count === 0) {
        return $value;
      }

      $suffix = $separator . ++$counter;
      $value = mb_substr($baseValue, 0, $maxLength - mb_strlen($suffix)) . $suffix;
    }
  }

  /**
   * Clones related entities.
   */
  private function cloneRelatedEntities(string $entityName, string $fkField, int $sourceId, int $newId, bool $checkPermissions): void {
    $records = $this->api4->execute($entityName, 'get', [
      'where' => [[$fkField, '=', $sourceId]],
      'checkPermissions' => $checkPermissions,
    ]);

    foreach ($records as $record) {
      $newData = $record;
      unset($newData['id']);
      $newData[$fkField] = $newId;

      $this->api4->execute($entityName, 'create', [
        'values' => $newData,
        'checkPermissions' => $checkPermissions,
      ]);
    }
  }

}
