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

namespace Civi\Funding\Form\FundingCase;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCaseType\FundingCaseTypeServiceInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

/**
 * @see FundingCaseTypeServiceInterface
 */
interface FundingCaseUiSchemaFactoryInterface extends FundingCaseTypeServiceInterface {

  public const SERVICE_TAG = 'funding.case.ui_schema_factory';

  /**
   * Called when updating a funding case.
   */
  public function createUiSchemaUpdate(FundingCaseBundle $fundingCaseBundle): JsonFormsElement;

  /**
   * Called when creating a new funding case.
   */
  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsElement;

}
