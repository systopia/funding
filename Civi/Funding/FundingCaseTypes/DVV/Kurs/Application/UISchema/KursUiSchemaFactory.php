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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema;

use Civi\Core\Format;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursStatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class KursUiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  public function __construct(
    private readonly Format $format,
    private readonly PossibleRecipientsLoaderInterface $possibleRecipientsLoader,
    private readonly RequestContextInterface $requestContext,
    private readonly KursStatusMarkupFactory $statusMarkupFactory,
  ) {}

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    return $this->crateUiSchema($applicationProcessBundle->getFundingProgram(), 0, [$statusMarkup]);
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingProgram
    );

    return $this->crateUiSchema(
      $fundingProgram,
      1 === count($possibleRecipients) ? KursUiSchema::FLAG_SHOW_RECIPIENTS_CONTROL : 0
    );
  }

  public function createUiSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonFormsLayout {
    return $this->crateUiSchema($fundingProgram, 0);
  }

  /**
   * @param list<\Civi\RemoteTools\JsonForms\JsonFormsElement> $extraElements
   */
  private function crateUiSchema(FundingProgramEntity $fundingProgram, int $flags, array $extraElements = []): KursUiSchema {
    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_reisekosten'))) {
      throw new \RuntimeException('Reisekostengrundbetrag nicht definiert');
    }

    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_teilnehmer'))) {
      throw new \RuntimeException('Teilnehmer*innengrundbetrag nicht definiert');
    }

    if (!is_float($fundingProgram->get('funding_program_dvv.grundbetrag_honorar'))) {
      throw new \RuntimeException('Honorargrundbetrag nicht definiert');
    }
#
    $currency = $fundingProgram->getCurrency();

    return new KursUiSchema(
      $currency,
      $flags,
      $this->format->money($fundingProgram->get('funding_program_dvv.grundbetrag_reisekosten'), $currency),
      $this->format->money($fundingProgram->get('funding_program_dvv.grundbetrag_teilnehmer'), $currency),
      $this->format->money($fundingProgram->get('funding_program_dvv.grundbetrag_honorar'), $currency),
      $extraElements
    );
  }

}
