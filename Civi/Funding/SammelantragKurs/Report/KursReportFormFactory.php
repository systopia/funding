<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\SammelantragKurs\Report;

use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\SammelantragKurs\Application\JsonSchema\KursGrunddatenJsonSchema;
use Civi\Funding\SammelantragKurs\Application\JsonSchema\KursZuschussJsonSchema;
use Civi\Funding\SammelantragKurs\Application\UiSchema\KursGrunddatenUiSchema;
use Civi\Funding\SammelantragKurs\Report\JsonSchema\KursDokumenteJsonSchema;
use Civi\Funding\SammelantragKurs\Report\JsonSchema\KursFoerderungJsonSchema;
use Civi\Funding\SammelantragKurs\Report\JsonSchema\KursZusammenfassungJsonSchema;
use Civi\Funding\SammelantragKurs\Report\UiSchema\KursDokumenteCategory;
use Civi\Funding\SammelantragKurs\Report\UiSchema\KursFoerderungGroup;
use Civi\Funding\SammelantragKurs\Report\UiSchema\KursZusammenFassungCategory;
use Civi\Funding\SammelantragKurs\Report\UiSchema\KursZuschussGroup;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursReportFormFactory implements ReportFormFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    $fundingProgram = $clearingProcessBundle->getFundingProgram();
    $grunddatenJsonSchema = new KursGrunddatenJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      TRUE
    );

    $zuschussJsonSchema = new KursZuschussJsonSchema(TRUE);
    $zuschussJsonSchema['required'] = [];
    $zuschussJsonSchema['properties'] = new JsonSchema(array_filter(
      // @phpstan-ignore-next-line
      $zuschussJsonSchema['properties']->getKeywords(),
      fn ($propertyName) => str_ends_with((string) $propertyName, 'Max'),
      ARRAY_FILTER_USE_KEY
    ));

    $dokumenteJsonSchema = new KursDokumenteJsonSchema();
    $foerderungJsonSchema = new KursFoerderungJsonSchema();
    $zusammenfassungJsonSchema = new KursZusammenfassungJsonSchema();

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'zuschuss' => $zuschussJsonSchema,
        'dokumente' => $dokumenteJsonSchema,
        'foerderung' => $foerderungJsonSchema,
        'zusammenfassung' => $zusammenfassungJsonSchema,
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
          'zuschuss' => $zuschussJsonSchema,
          'dokumente' => $dokumenteJsonSchema,
          'foerderung' => $foerderungJsonSchema->withAllFieldsRequired(),
          'zusammenfassung' => $zusammenfassungJsonSchema,
        ],
        ['required' => ['grunddaten', 'zuschuss', 'dokumente', 'foerderung']]),
      ]),
    ]);

    $uiSchema = new JsonFormsCategorization([
      (new KursGrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties', TRUE))
        ->withRequiredLabels($grunddatenJsonSchema),
      new KursDokumenteCategory('#/properties/reportData/properties/dokumente/properties'),
    ]);

    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();
    $zuschussUiSchema = new KursZuschussGroup(
      $currency,
      '#/properties/reportData/properties/zuschuss/properties'
    );
    $maxZuschussUiSchema = new JsonFormsGroup('Maximal möglicher Gesamtzuschuss in ' . $currency, [
      new JsonFormsControl('#/properties/reportData/properties/zuschuss/properties/gesamtMax', ''),
    ]);
    $foerderungUiSchema = new KursFoerderungGroup(
      '#/properties/reportData/properties/foerderung/properties',
      $currency,
    );

    $receiptsAppendUiSchema = new JsonFormsGroup('', [$maxZuschussUiSchema, $foerderungUiSchema]);
    $zusammenfassungUiSchema = new KursZusammenFassungCategory('#/properties/reportData/properties', $currency);

    return new ReportForm($jsonSchema, $uiSchema, $zuschussUiSchema, $receiptsAppendUiSchema, $zusammenfassungUiSchema);
  }

}
