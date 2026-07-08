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
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

class ApplicationJsonSchemaCreateHelper {

  public function __construct(
    private readonly ApplicationProcessActionsDeterminerInterface $actionsDeterminer
  ) {}

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *    Status of other application processes in same funding case indexed by ID.
   */
  public function addActionProperty(
    JsonSchema $jsonSchema,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): void {
    $submitActions = $this->actionsDeterminer->getActions(
      $applicationProcessBundle,
      $applicationProcessStatusList
    );
    $this->doAddActionProperty($jsonSchema, $submitActions);
  }

  /**
   * @param list<string> $permissions
   * @param \Civi\Funding\Entity\FundingCaseEntity|null $fundingCase
   *   The funding case a new application process is going to be added. NULL if
   *   no funding case exists, yet.
   */
  public function addInitialActionProperty(
    JsonSchema $jsonSchema,
    FundingCaseTypeEntity $fundingCaseType,
    array $permissions,
    ?FundingCaseEntity $fundingCase
  ): void {
    $submitActions = $this->actionsDeterminer->getInitialActions(
      $permissions,
      $fundingCaseType,
      $fundingCase
    );
    $this->doAddActionProperty($jsonSchema, $submitActions);
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *   Status of other application processes in same funding case indexed by ID.
   */
  public function addCommentProperty(
    JsonSchema $jsonSchema,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): void {
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $properties */
    $properties = $jsonSchema['properties'];

    if ($this->actionsDeterminer->isActionAllowed(
      'add-comment',
      $applicationProcessBundle,
      $applicationProcessStatusList
    )) {
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
    if (!$this->actionsDeterminer->isEditAllowed($applicationProcessBundle, $applicationProcessStatusList)) {
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

}
