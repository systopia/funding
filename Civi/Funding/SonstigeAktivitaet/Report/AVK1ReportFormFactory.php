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

namespace Civi\Funding\SonstigeAktivitaet\Report;

use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1ReportFormFactory implements ReportFormFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    // In draft report data fields may be empty.
    $reportDataDraftSchema = new JsonSchemaObject([
      'durchgefuehrt' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'geplant' => 'entsprechend dem geplanten Programm',
          'geaendert' => 'mit folgenden wesentlichen Änderungen (kurze Begründung für die Änderung):',
        ]),
      ]),
      'aenderungen' => new JsonSchemaString(),
      'thematischeSchwerpunkte' => new JsonSchemaString(),
      'methoden' => new JsonSchemaString(),
      'zielgruppe' => new JsonSchemaString(),
      'sonstiges' => new JsonSchemaString(),
    ]);

    $reportDataSchema = $reportDataDraftSchema->clone();
    $requiredStrings = [
      'durchgefuehrt',
      'thematischeSchwerpunkte',
      'methoden',
      'zielgruppe',
      'sonstiges',
    ];
    $reportDataSchema['required'] = $requiredStrings;
    foreach ($requiredStrings as $property) {
      // @phpstan-ignore-next-line
      $reportDataSchema['properties'][$property]['minLength'] ??= 1;
    }

    $this->addValidation($reportDataSchema, 'aenderungen', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || durchgefuehrt === "geplant"',
        'variables' => [
          'durchgefuehrt' => new JsonSchemaDataPointer('1/durchgefuehrt'),
        ],
      ],
      'message' => 'Bitte Begründung für die Änderungen angeben.',
    ]));

    $jsonSchema = new JsonSchemaObject([
      'reportData' => $reportDataDraftSchema,
    ], [
      'if' => JsonSchema::fromArray([
        'properties' => [
          '_action' => ['not' => ['const' => 'save']],
        ],
      ]),
      'then' => new JsonSchemaObject([
        'reportData' => $reportDataSchema,
      ]),
    ]);

    $uiSchema = new JsonFormsGroup('Sachbericht', [
      new JsonFormsControl(
        '#/properties/reportData/properties/durchgefuehrt',
        'Die Maßnahme wurde durchgeführt&nbsp;*',
        NULL,
        ['format' => 'radio']
      ),
      new JsonFormsControl('#/properties/reportData/properties/aenderungen', '', NULL, ['multi' => TRUE], [
        'rule' => new JsonFormsRule(
          'ENABLE',
          '#/properties/reportData/properties/durchgefuehrt',
          JsonSchema::fromArray(['const' => 'geaendert'])
        ),
      ]),
      new JsonFormsControl(
        '#/properties/reportData/properties/thematischeSchwerpunkte',
        'Welche thematischen Schwerpunkte hatte die Veranstaltung?&nbsp;*',
        NULL,
        ['multi' => TRUE],
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/methoden',
        'Inwiefern und mit welchen Methoden wurden die  inhaltlichen Ziele erreicht?&nbsp;*',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/zielgruppe',
        'Welche Zielgruppe wurde mit der Veranstaltung erreicht (Zusammensetzung, Alter)?&nbsp;*',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sonstiges',
        'Besondere Vorkommnisse, Schlussfolgerungen oder sonstige Hinweise&nbsp;*',
        NULL,
        ['multi' => TRUE]
      ),
    ]);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

  private function addValidation(JsonSchemaObject $reportDataSchema, string $property, JsonSchema $validation): void {
    $validations = $reportDataSchema['properties'][$property]['$validations'] ?? [];
    $validations[] = $validation;
    // @phpstan-ignore-next-line
    $reportDataSchema['properties'][$property]['$validations'] = $validations;
  }

}
