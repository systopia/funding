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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report;

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1GrunddatenSchema;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\UISchema\AVK1GrunddatenUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\JsonSchema\AVK1DokumenteJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\JsonSchema\AVK1SachberichtJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\UiSchema\AVK1DokumenteCategory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\UiSchema\AVK1SachberichtCategory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class AVK1ReportFormFactory implements ReportFormFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    $fundingProgram = $clearingProcessBundle->getFundingProgram();
    $grunddatenJsonSchema = new AVK1GrunddatenSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      TRUE
    );

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'dokumente' => new AVK1DokumenteJsonSchema(),
        'sachbericht' => new AVK1SachberichtJsonSchema(),
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
      new AVK1GrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties'),
      new AVK1SachberichtCategory('#/properties/reportData/properties/sachbericht/properties'),
      new AVK1DokumenteCategory('#/properties/reportData/properties/dokumente/properties'),
    ]);

    return new ReportForm($jsonSchema, $uiSchema);
  }

}
