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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Report;

use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema\IJBGrunddatenJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema\IJBTeilnehmerJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema\IJBZuschussJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema\IJBGrunddatenUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema\IJBTeilnehmerUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\JsonSchema\IJBDokumenteJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\JsonSchema\IJBFoerderungJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\JsonSchema\IJBSachberichtJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema\IJBDokumenteCategory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema\IJBFoerderungGroup;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema\IJBSachberichtCategory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema\IJBZuschussGroup;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class IJBReportFormFactory implements ReportFormFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    $fundingProgram = $clearingProcessBundle->getFundingProgram();
    $grunddatenJsonSchema = new IJBGrunddatenJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      TRUE
    );
    $teilnehmerJsonSchema = new IJBTeilnehmerJsonSchema(TRUE);
    $zuschussJsonSchema = new IJBZuschussJsonSchema(TRUE);
    $zuschussJsonSchema['required'] = [];
    $zuschussJsonSchema['properties'] = new JsonSchema(array_filter(
      // @phpstan-ignore-next-line
      $zuschussJsonSchema['properties']->getKeywords(),
      fn ($propertyName) => str_ends_with((string) $propertyName, 'Max'),
      ARRAY_FILTER_USE_KEY
    ));

    // In draft fields may be empty.
    $sachberichtDraftJsonSchema = new IJBSachberichtJsonSchema();
    $sachberichtJsonSchema = $sachberichtDraftJsonSchema->withValidations();
    $dokumenteJsonSchema = new IJBDokumenteJsonSchema();
    $foerderungJsonSchema = new IJBFoerderungJsonSchema();

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'teilnehmer' => $teilnehmerJsonSchema,
        'zuschuss' => $zuschussJsonSchema,
        'sachbericht' => $sachberichtDraftJsonSchema,
        'dokumente' => $dokumenteJsonSchema,
        'foerderung' => $foerderungJsonSchema,
      ]),
    ], [
      'if' => JsonSchema::fromArray([
        'properties' => [
          '_action' => ['not' => ['const' => 'save']],
        ],
      ]),
      'then' => new JsonSchemaObject([
        'reportData' => new JsonSchemaObject([
          'grunddaten' => $grunddatenJsonSchema,
          'teilnehmer' => $teilnehmerJsonSchema->withAllFieldsRequired(),
          'zuschuss' => $zuschussJsonSchema,
          'sachbericht' => $sachberichtJsonSchema,
          'dokumente' => $dokumenteJsonSchema,
          'foerderung' => $foerderungJsonSchema->withAllFieldsRequired(),
        ],
        ['required' => ['grunddaten', 'teilnehmer', 'zuschuss', 'sachbericht', 'dokumente', 'foerderung']]),
      ]),
    ]);

    $uiSchema = new JsonFormsCategorization([
      new IJBGrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties', TRUE),
      (new IJBTeilnehmerUiSchema('#/properties/reportData/properties/teilnehmer/properties', TRUE))
        ->withRequiredLabels($teilnehmerJsonSchema),
      new IJBSachberichtCategory('#/properties/reportData/properties/sachbericht/properties'),
      new IJBDokumenteCategory('#/properties/reportData/properties/dokumente/properties'),
    ]);

    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();
    $zuschussUiSchema = new IJBZuschussGroup(
      $currency,
      '#/properties/reportData/properties/zuschuss/properties',
      '#/properties/reportData/properties/grunddaten/properties'
    );
    $foerderungUiSchema = new IJBFoerderungGroup(
      '#/properties/reportData/properties/foerderung/properties',
      $currency,
    );

    return new ReportForm($jsonSchema, $uiSchema, $zuschussUiSchema, $foerderungUiSchema);
  }

}
