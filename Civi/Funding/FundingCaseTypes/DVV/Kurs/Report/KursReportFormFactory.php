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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Report;

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursGrunddatenSchema;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema\KursGrunddatenUiSchema;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\JsonSchema\KursDokumenteJsonSchema;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\JsonSchema\KursSachberichtJsonSchema;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\UiSchema\KursDokumenteCategory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\UiSchema\KursSachberichtCategory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursReportFormFactory implements ReportFormFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    return $this->doCreateReportForm($clearingProcessBundle->getFundingProgram());
  }

  public function createReportFormForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): ReportFormInterface {
    return $this->doCreateReportForm($fundingProgram);
  }

  public function doCreateReportForm(FundingProgramEntity $fundingProgram): ReportFormInterface {
    $grunddatenJsonSchema = new KursGrunddatenSchema(
      $fundingProgram->getStartDate(),
      $fundingProgram->getEndDate(),
      TRUE
    );

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'dokumente' => new KursDokumenteJsonSchema(),
        'sachbericht' => new KursSachberichtJsonSchema(),
      ], ['required' => ['grunddaten', 'sachbericht', 'dokumente']]),
    ], [
      'required' => ['reportData'],
      '$limitValidation' => JsonSchema::fromArray([
        'condition' => [
          'evaluate' => [
            'expression' => 'action not in editActions || action === "save"',
            'variables' => [
              'action' => new JsonSchemaDataPointer('/_action', ''),
              'editActions' => ClearingActionsDeterminer::EDIT_ACTIONS,
            ],
          ],
        ],
      ]),
    ]);

    $uiSchema = new JsonFormsCategorization([
      new KursGrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties'),
      new KursSachberichtCategory('#/properties/reportData/properties/sachbericht/properties'),
      new KursDokumenteCategory('#/properties/reportData/properties/dokumente/properties'),
    ]);

    return new ReportForm($jsonSchema, $uiSchema);
  }

}
