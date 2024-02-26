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

use Civi\Funding\ClearingProcess\ReportFormFactoryInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1ReportFormFactory implements ReportFormFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
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
      ]),
    ]);

    $uiSchema = new JsonFormsGroup('Sachbericht', [
      new JsonFormsControl(
        '#/properties/reportData/properties/durchgefuehrt',
        'Die Maßnahme wurde durchgeführt',
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
        'Welche thematischen Schwerpunkte hatte die Veranstaltung?',
        NULL,
        ['multi' => TRUE],
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/methoden',
        'Inwiefern und mit welchen Methoden wurden die  inhaltlichen Ziele erreicht?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/zielgruppe',
        'Welche Zielgruppe wurde mit der Veranstaltung erreicht (Zusammensetzung, Alter)?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sonstiges',
        'Besondere Vorkommnisse, Schlussfolgerungen oder sonstige Hinweise',
        NULL,
        ['multi' => TRUE]
      ),
    ]);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

}
