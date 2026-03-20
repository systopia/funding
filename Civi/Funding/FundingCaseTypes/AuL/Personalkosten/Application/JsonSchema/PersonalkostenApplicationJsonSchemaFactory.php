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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema;

use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class PersonalkostenApplicationJsonSchemaFactory implements NonCombinedApplicationJsonSchemaFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->existingCaseRecipientLoader = $existingCaseRecipientLoader;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    return new PersonalkostenApplicationJsonSchema(
    // @phpstan-ignore argument.type
      $fundingProgram->get('funding_program_extra.foerderquote'),
      // @phpstan-ignore argument.type
      $fundingProgram->get('funding_program_extra.sachkostenpauschale'),
      $fundingProgram->getStartDate(),
      $fundingProgram->getEndDate(),
      $this->existingCaseRecipientLoader->getRecipient($fundingCase),
      $this->getLimitedValidationActions(),
    );
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    return new PersonalkostenApplicationJsonSchema(
      // @phpstan-ignore argument.type
      $fundingProgram->get('funding_program_extra.foerderquote'),
      // @phpstan-ignore argument.type
      $fundingProgram->get('funding_program_extra.sachkostenpauschale'),
      $fundingProgram->getStartDate(),
      $fundingProgram->getEndDate(),
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram),
      $this->getLimitedValidationActions(),
    );
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return new PersonalkostenApplicationJsonSchema(
      0,
      0.0,
      $fundingProgram->getStartDate(),
      $fundingProgram->getEndDate(),
      [],
      [],
    );
  }

  /**
   * @return list<string>
   */
  private function getLimitedValidationActions(): array {
    return [
      'save',
      'delete',
      'withdraw',
      'review',
      'request-change',
      'reject-calculative',
      'reject-content',
      'reject',
      'add-comment',
    ];
  }

}
