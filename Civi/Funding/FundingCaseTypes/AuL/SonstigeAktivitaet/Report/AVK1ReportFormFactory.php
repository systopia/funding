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
    // In draft report data fields may be empty.
    $reportDataDraftSchema = new AVK1SachberichtJsonSchema();
    $reportDataSchema = $reportDataDraftSchema->withValidations();
    $dokumenteJsonSchema = new AVK1DokumenteJsonSchema();

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'dokumente' => $dokumenteJsonSchema,
        'sachbericht' => $reportDataDraftSchema,
      ]),
    ], [
      'if' => JsonSchema::fromArray([
        'properties' => [
          '_action' => ['not' => ['const' => 'save']],
        ],
      ]),
      'then' => new JsonSchemaObject([
        'reportData' => new JsonSchemaObject([
          'grunddaten' => $grunddatenJsonSchema->withAllFieldsRequired(),
          'sachbericht' => $reportDataSchema,
          'dokumente' => $dokumenteJsonSchema,
        ], ['required' => ['grunddaten', 'sachbericht', 'dokumente']]),
      ]),
    ]);

    $uiSchema = new JsonFormsCategorization([
      (new AVK1GrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties'))
        ->withRequiredLabels($grunddatenJsonSchema),
      new AVK1SachberichtCategory('#/properties/reportData/properties/sachbericht/properties'),
      new AVK1DokumenteCategory('#/properties/reportData/properties/dokumente/properties'),
    ]);

    return new ReportForm($jsonSchema, $uiSchema);
  }

}
