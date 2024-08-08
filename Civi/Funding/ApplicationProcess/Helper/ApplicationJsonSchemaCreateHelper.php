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

namespace Civi\Funding\ApplicationProcess\Helper;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

class ApplicationJsonSchemaCreateHelper {

  use HasReviewPermissionTrait;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *    Status of other application processes in same funding case indexed by ID.
   */
  public function addActionProperty(
    JsonSchema $jsonSchema,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): void {
    $submitActions = $this->getActionsDeterminer($applicationProcessBundle->getFundingCaseType())->getActions(
      $applicationProcessBundle,
      $applicationProcessStatusList
    );
    $this->doAddActionProperty($jsonSchema, $submitActions);
  }

  /**
   * @phpstan-param list<string> $permissions
   */
  public function addInitialActionProperty(
    JsonSchema $jsonSchema,
    FundingCaseTypeEntity $fundingCaseType,
    array $permissions
  ): void {
    $submitActions = $this->getActionsDeterminer($fundingCaseType)->getInitialActions($permissions);
    $this->doAddActionProperty($jsonSchema, $submitActions);
  }

  public function addCommentProperty(
    JsonSchema $jsonSchema,
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): void {
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $properties */
    $properties = $jsonSchema['properties'];

    if ($this->hasReviewPermission($applicationProcessBundle->getFundingCase()->getPermissions())) {
      $properties['comment'] = new JsonSchemaComment();
    }
    else {
      // Prevent adding a comment without permission
      $properties['comment'] = new JsonSchemaNull();
    }
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *    Status of other application processes in same funding case indexed by ID.
   */
  public function addReadOnlyKeywordIfNecessary(
    JsonSchema $jsonSchema,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): void {
    // The readOnly keyword is not inherited, though we use it for informational purposes.
    if (!$this->getActionsDeterminer($applicationProcessBundle->getFundingCaseType())->isEditAllowed(
      $applicationProcessBundle,
      $applicationProcessStatusList
    )) {
      $jsonSchema->addKeyword('readOnly', TRUE);
    }
  }

  /**
   * @phpstan-param list<string> $allowedActions
   */
  private function doAddActionProperty(JsonSchema $jsonSchema, array $allowedActions): void {
    if ([] === $allowedActions) {
      // empty array is not allowed as enum
      $allowedActions = [NULL];
    }

    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $properties */
    $properties = $jsonSchema['properties'];
    $properties['_action'] = new JsonSchemaString(['enum' => $allowedActions]);

    /** @phpstan-var list<string> $required */
    $required = $jsonSchema['_required'] ?? [];
    $required[] = '_action';
    $jsonSchema['_required'] = $required;
  }

  private function getActionsDeterminer(
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationProcessActionsDeterminerInterface {
    return $this->serviceLocatorContainer->get($fundingCaseType->getName())->getApplicationProcessActionsDeterminer();
  }

}
