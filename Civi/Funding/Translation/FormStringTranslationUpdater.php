<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Translation;

use Civi\Api4\FundingFormStringTranslation;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class FormStringTranslationUpdater {

  private Api4Interface $api4;

  private StringExtractor $stringExtractor;

  public function __construct(Api4Interface $api4, StringExtractor $stringExtractor) {
    $this->api4 = $api4;
    $this->stringExtractor = $stringExtractor;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function extractAndUpdateStrings(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): void {
    $strings = $this->stringExtractor->extractStrings($fundingProgram, $fundingCaseType);
    if ([] === $strings) {
      $this->api4->deleteEntities(
        FundingFormStringTranslation::getEntityName(),
        CompositeCondition::new(
          'AND',
          Comparison::new('funding_program_id', '=', $fundingProgram->getId()),
          Comparison::new('funding_case_type_id', '=', $fundingCaseType->getId())
        )
      );

      return;
    }

    $this->api4->deleteEntities(
      FundingFormStringTranslation::getEntityName(),
      CompositeCondition::new(
        'AND',
        Comparison::new('funding_program_id', '=', $fundingProgram->getId()),
        Comparison::new('funding_case_type_id', '=', $fundingCaseType->getId()),
        Comparison::new('msg_text', 'NOT IN', $strings)
      )
    );

    $currentStrings = $this->api4->getEntities(FundingFormStringTranslation::getEntityName(), CompositeCondition::new(
      'AND',
      Comparison::new('funding_program_id', '=', $fundingProgram->getId()),
      Comparison::new('funding_case_type_id', '=', $fundingCaseType->getId())
    ))->column('msg_text');

    $newStrings = array_diff($strings, $currentStrings);
    foreach ($newStrings as $newString) {
      $this->api4->createEntity(FundingFormStringTranslation::getEntityName(), [
        'funding_program_id' => $fundingProgram->getId(),
        'funding_case_type_id' => $fundingCaseType->getId(),
        'msg_text' => $newString,
        'new_text' => $newString,
        'modification_date' => NULL,
      ]);
    }
  }

}
