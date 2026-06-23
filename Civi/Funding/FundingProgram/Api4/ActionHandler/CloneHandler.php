<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-import-type fundingProgramT from FundingProgramEntity
 */
class CloneHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingProgram';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * Prepares the data for a new program based on source and action values.
   *
   * @param \Civi\Funding\Entity\FundingProgramEntity $sourceFundingProgram
   * @param array<string, mixed> $values
   *
   * @return \Civi\Funding\Entity\FundingProgramEntity
   */
  public function prepareTargetFundingProgramData(
    FundingProgramEntity $sourceFundingProgram,
    array $values
  ): FundingProgramEntity {
    $sourceFundingProgramData = $sourceFundingProgram->toArray();
    unset($sourceFundingProgramData['id']);

    $sourceFundingProgramData = array_merge($sourceFundingProgramData, $values);
    // @phpstan-ignore argument.type
    $targetFundingProgram = FundingProgramEntity::fromArray($sourceFundingProgramData);

    if (!isset($values['title']) || $values['title'] === '') {
      $targetFundingProgram->setTitle($this->getUniqueValue(
        FundingProgram::getEntityName(),
        'title',
        E::ts('Copy of %1', [1 => $sourceFundingProgram->getTitle()]),
        255,
        ' '
      ));
    }

    if (!isset($values['abbreviation']) || $values['abbreviation'] === '') {
      $targetFundingProgram->setAbbreviation($this->getUniqueValue(
        FundingProgram::getEntityName(),
        'abbreviation',
        $sourceFundingProgram->getAbbreviation() . '_copy',
        20,
        '_'
      ));
    }

    return $targetFundingProgram;
  }

  /**
   * @param \Civi\Funding\Api4\Action\FundingProgram\CloneAction $action
   *
   * @return list<fundingProgramT>
   */
  public function clone(CloneAction $action): array {
    $result = [];
    foreach ($action->getBatchRecords() as $record) {
      /** @var fundingProgramT $record */
      $sourceFundingProgramEntity = FundingProgramEntity::fromArray($record);
      $targetFundingProgramData = $this->prepareTargetFundingProgramData(
        $sourceFundingProgramEntity,
        $action->getValues()
      );
      $result[] = $this->executeClone(
        $sourceFundingProgramEntity->getId(),
        $targetFundingProgramData,
        $action->getCheckPermissions()
      )->toArray();
    }
    return $result;
  }

  private function executeClone(
    int $sourceFundingProgramId,
    FundingProgramEntity $targetFundingProgramEntity,
    bool $checkPermissions
  ): FundingProgramEntity {
    $result = $this->api4->createEntity(FundingProgram::getEntityName(), $targetFundingProgramEntity->toArray());
    $targetFundingProgram = FundingProgramEntity::singleFromApiResult($result);

    $targetId = $targetFundingProgram->getId();

    $this->cloneRelatedEntities(
      FundingCaseTypeProgram::getEntityName(), $sourceFundingProgramId, $targetId, $checkPermissions
    );
    $this->cloneRelatedEntities(
      FundingProgramContactRelation::getEntityName(), $sourceFundingProgramId, $targetId, $checkPermissions
    );
    $this->cloneRelatedEntities(
      FundingRecipientContactRelation::getEntityName(), $sourceFundingProgramId, $targetId, $checkPermissions
    );
    $this->cloneRelatedEntities(
      FundingNewCasePermissions::getEntityName(), $sourceFundingProgramId, $targetId, $checkPermissions
    );
    $this->cloneRelatedEntities(
      FundingFormStringTranslation::getEntityName(), $sourceFundingProgramId, $targetId, $checkPermissions
    );

    return $targetFundingProgram;
  }

  private function getUniqueValue(
    string $entityName,
    string $fieldName,
    string $baseValue,
    int $maxLength,
    string $separator
  ): string {
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

  private function cloneRelatedEntities(
    string $entityName,
    int $sourceFundingProgramId,
    int $targetFundingProgramId,
    bool $checkPermissions
  ): void {
    $sourceRelatedEntities = $this->api4->execute($entityName, 'get', [
      'where' => [['funding_program_id', '=', $sourceFundingProgramId]],
      'checkPermissions' => $checkPermissions,
    ]);

    /**
     * @var array<string, mixed> $entity
     */
    foreach ($sourceRelatedEntities as $entity) {
      $targetData = $entity;
      unset($targetData['id']);
      $targetData['funding_program_id'] = $targetFundingProgramId;

      $this->api4->execute($entityName, 'create', [
        'values' => $targetData,
        'checkPermissions' => $checkPermissions,
      ]);
    }
  }

}
