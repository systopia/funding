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
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class PersonalkostenApplicationJsonSchemaFactory implements NonCombinedApplicationJsonSchemaFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private Api4Interface $api4;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    Api4Interface $api4,
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->api4 = $api4;
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

    [$foerderquote, $sachkostenpauschale] = $this->getFoerderquoteAndSachkostenpauschale($fundingProgram->getId());

    return new PersonalkostenApplicationJsonSchema(
      $foerderquote,
      $sachkostenpauschale,
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
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
    [$foerderquote, $sachkostenpauschale] = $this->getFoerderquoteAndSachkostenpauschale($fundingProgram->getId());

    return new PersonalkostenApplicationJsonSchema(
      $foerderquote,
      $sachkostenpauschale,
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
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
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      [],
      [],
    );
  }

  /**
   * @return array{int, float}
   *
   * @throws \CRM_Core_Exception
   */
  public function getFoerderquoteAndSachkostenpauschale(int $fundingProgramId): array {
    $values = $this->api4->execute('FundingProgram', 'get', [
      'select' => [
        'funding_program_extra.foerderquote',
        'funding_program_extra.sachkostenpauschale',
      ],
      'where' => [['id', '=', $fundingProgramId]],
    ])->single();

    return [
      $values['funding_program_extra.foerderquote'],
      $values['funding_program_extra.sachkostenpauschale'],
    ];
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
