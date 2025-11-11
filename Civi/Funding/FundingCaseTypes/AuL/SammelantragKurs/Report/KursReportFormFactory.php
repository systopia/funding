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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report;

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\JsonSchema\KursBeschreibungJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\JsonSchema\KursGrunddatenJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\JsonSchema\KursZuschussJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\UiSchema\KursBeschreibungUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\UiSchema\KursGrunddatenUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\JsonSchema\KursDokumenteJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\JsonSchema\KursFoerderungJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\UiSchema\KursDokumenteCategory;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\UiSchema\KursFoerderungGroup;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\UiSchema\KursZuschussGroup;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
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

    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'grunddaten' => $grunddatenJsonSchema,
        'zuschuss' => $zuschussJsonSchema,
        'beschreibung' => new KursBeschreibungJsonSchema(),
        'dokumente' => new KursDokumenteJsonSchema(),
        'foerderung' => new KursFoerderungJsonSchema(),
      ], [
        'required' => ['grunddaten', 'zuschuss', 'beschreibung', 'dokumente', 'foerderung'],
      ]),
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
      new KursGrunddatenUiSchema('#/properties/reportData/properties/grunddaten/properties', TRUE),
      new KursBeschreibungUiSchema('#/properties/reportData/properties/beschreibung/properties'),
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

    return new ReportForm($jsonSchema, $uiSchema, $zuschussUiSchema, $receiptsAppendUiSchema);
  }

}
