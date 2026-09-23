<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema;

use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

class KursJsonSchemaFactory implements NonCombinedApplicationJsonSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->existingCaseRecipientLoader = $existingCaseRecipientLoader;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    return $this->createJsonSchema(
      $applicationProcessBundle->getFundingProgram(),
      $this->existingCaseRecipientLoader->getRecipient($applicationProcessBundle->getFundingCase())
    );
  }

  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    return $this->createJsonSchema(
      $fundingProgram,
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram)
    );
  }

  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return $this->createJsonSchema($fundingProgram, []);
  }

  /**
   * @param array<int, string> $possibleRecipients
   */
  private function createJsonSchema(FundingProgramEntity $fundingProgram, array $possibleRecipients): JsonSchema {
    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_reisekosten'))) {
      throw new \RuntimeException("Reisekostengrundbetrag nicht definiert");
    }

    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_teilnehmer'))) {
      throw new \RuntimeException('Teilnehmer*innengrundbetrag nicht definiert');
    }

    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_honorar'))) {
      throw new \RuntimeException('Honorargrundbetrag nicht definiert');
    }

    return new KursJsonSchema(
      $fundingProgram->getStartDate(),
      $fundingProgram->getEndDate(),
      $possibleRecipients,
      $fundingProgram->get('funding_program_dvv.grundbetrag_reisekosten'),
      $fundingProgram->get('funding_program_dvv.grundbetrag_teilnehmer'),
      $fundingProgram->get('funding_program_dvv.grundbetrag_honorar'),
    );
  }

}
