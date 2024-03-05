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

namespace Civi\Funding\IJB\Report;

use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class IJBReportFormFactory implements ReportFormFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

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
        'sprache' => new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf([
            'partnersprache' => 'in der Partnersprache',
            'deutsch' => 'auf Deutsch',
            'andere' => 'auf:',
          ]),
        ]),
        'andereSprache' => new JsonSchemaString(['maxLength' => 50]),
        'verstaendigungBewertung' => new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf([
            'gut' => 'gut',
            'zufriedenstellend' => 'zufriedenstellend',
            'schlecht' => 'schlecht (bitte Begründung angeben)',
          ]),
        ]),
        'verstaendigungFreitext' => new JsonSchemaString(),
        'sprachlicheUnterstuetzung' => new JsonSchemaBoolean([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf(['TRUE' => 'ja', 'FALSE' => 'nein']),
        ]),
        'sprachlicheUnterstuetzungArt' => new JsonSchemaString(),
        'sprachlicheUnterstuetzungProgrammpunkte' => new JsonSchemaString(),
        'sprachlicheUnterstuetzungErfahrungen' => new JsonSchemaString(),
      ]),
    ]);

    $uiSchema = new JsonFormsGroup('Sachbericht', [
      new JsonFormsGroup('Infotext', [], <<<'EOD'
Mit der Beantwortung der nachfolgenden Fragen gebt Ihr uns und dem BMFSFJ die
Möglichkeit, einen Einblick in Eure Maßnahme zu gewinnen. Eure Erfahrungen
helfen uns, den internationalen Jugendaustausch weiterzuentwickeln, bewährte
Methoden oder Programmteile weiterzuempfehlen sowie möglicherweise häufiger
vorkommende Herausforderungen zu erkennen und bei der Behebung zu helfen.
Vor diesem Hintergrund bitten wir Euch darum, die Fragen aufmerksam zu
beantworten. Nutzt dafür gerne so viel Platz, wie Ihr benötigt.
EOD
      ),
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

      new JsonFormsGroup('Sprachliche Verständigung', [
        new JsonFormsControl(
          '#/properties/reportData/properties/sprache',
          'Die sprachliche Verständigung während der Maßnahme erfolgte:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl('#/properties/reportData/properties/andereSprache', '', NULL, ['multi' => TRUE], [
          'rule' => new JsonFormsRule(
            'ENABLE',
            '#/properties/reportData/properties/sprache',
            JsonSchema::fromArray(['const' => 'andere'])
          ),
        ]),
        new JsonFormsControl(
          '#/properties/reportData/properties/verstaendigungBewertung',
          'Die sprachliche Verständigung während der Maßnahme war:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl(
          '#/properties/reportData/properties/verstaendigungFreitext',
          'Anmerkungen',
          NULL,
          ['multi' => TRUE]
        ),
      ]),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzung',
        'Wurde während der Maßnahme sprachliche Unterstützung ' .
        '(Sprachanimation, Sprachmittlung, Dolmetschung) in Anspruch genommen?',
        NULL,
        ['format' => 'radio'],
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungArt',
        'Art der Unterstützung',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => TRUE])
          ),
        ]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungProgrammpunkte',
        'Bei welchen Programmpunkten?',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => TRUE])
          ),
        ]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungErfahrungen',
        'Welche Erfahrungen habt Ihr damit gemacht?',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => TRUE])
          ),
        ]
      ),

    ]);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

}
