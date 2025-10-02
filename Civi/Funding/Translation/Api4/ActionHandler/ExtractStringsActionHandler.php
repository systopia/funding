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

namespace Civi\Funding\Translation\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingCaseTypeProgram\ExtractStringsAction;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Translation\FormStringTranslationUpdater;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class ExtractStringsActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCaseTypeProgram';

  private Api4Interface $api4;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FormStringTranslationUpdater $translationUpdater;

  public function __construct(
    Api4Interface $api4,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FormStringTranslationUpdater $translationUpdater,
  ) {
    $this->api4 = $api4;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->translationUpdater = $translationUpdater;
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @return list<array{id: int, funding_case_type_id: int, funding_program_id: int}>
   */
  public function extractStrings(ExtractStringsAction $action): array {
    /** @var list<array{id: int, funding_case_type_id: int, funding_program_id: int}> $fundingCaseTypeProgramList */
    $fundingCaseTypeProgramList = $this->api4->execute(self::ENTITY_NAME, 'get', [
      'select' => ['id', 'funding_case_type_id', 'funding_program_id'],
      'where' => $action->getWhere(),
    ])->getArrayCopy();

    foreach ($fundingCaseTypeProgramList as $fundingCaseTypeProgram) {
      $fundingProgram = $this->fundingProgramManager->get($fundingCaseTypeProgram['funding_program_id']);
      assert(NULL !== $fundingProgram);
      $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCaseTypeProgram['funding_case_type_id']);
      assert(NULL !== $fundingCaseType);
      $this->translationUpdater->extractAndUpdateStrings($fundingProgram, $fundingCaseType);
    }

    return $fundingCaseTypeProgramList;
  }

}
