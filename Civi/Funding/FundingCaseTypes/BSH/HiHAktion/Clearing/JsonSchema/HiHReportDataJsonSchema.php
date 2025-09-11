<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class HiHReportDataJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'reportData' => new JsonSchemaObject([
        'titel' => new JsonSchemaString(['readOnly' => TRUE]),
        'antragsnummer' => new JsonSchemaString(['readOnly' => TRUE]),
        'laufzeitVon' => new JsonSchemaDate(['readOnly' => TRUE]),
        'laufzeitBis' => new JsonSchemaDate(['readOnly' => TRUE]),
        'empfaenger' => new JsonSchemaString(['readOnly' => TRUE]),
        'projekttraeger' => new JsonSchemaString(['readOnly' => TRUE]),

        'ansprechperson' => new JsonSchemaObject([
          'name' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
          'vorname' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
          'email' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255, 'format' => 'email']),
          'telefonnummer' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
        ], ['required' => ['name', 'vorname', 'email', 'telefonnummer']]),

        'umsetzung' => new JsonSchemaString(['maxLength' => 3000]),
        'zieleErreicht' => new JsonSchemaBoolean([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
            'ja' => TRUE,
            'nein' => FALSE,
          ]),
        ]),
        'probleme' => new JsonSchemaString([
          'default' => '',
          'minLength' => 1,
          '$limitValidation' => JsonSchema::fromArray([
            'condition' => [
              'evaluate' => [
                'expression' => 'action === "save" || zieleErreicht',
                'variables' => [
                  'action' => new JsonSchemaDataPointer('/_action', ''),
                  'zieleErreicht' => new JsonSchemaDataPointer('1/zieleErreicht', FALSE),
                ],
              ],
            ],
          ]),
        ]),
        'erfolgsmoment' => new JsonSchemaString(['minLength' => 1]),

        'teilnehmende' => new JsonSchemaObject([
          'alterUnter6' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alter6bis12' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alter13bis19' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alter20bis29' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alter30bis49' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alter50bis66' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'alterAb67' => new JsonSchemaInteger(['minimum' => 0], TRUE),
          'gesamt' => new JsonSchemaCalculate(
            'integer',
            'alterUnter6 + alter6bis12 + alter13bis19 + alter20bis29 + alter30bis49 + alter50bis66 + alterAb67',
            [
              'alterUnter6' => new JsonSchemaDataPointer('1/alterUnter6', 0),
              'alter6bis12' => new JsonSchemaDataPointer('1/alter6bis12', 0),
              'alter13bis19' => new JsonSchemaDataPointer('1/alter13bis19', 0),
              'alter20bis29' => new JsonSchemaDataPointer('1/alter20bis29', 0),
              'alter30bis49' => new JsonSchemaDataPointer('1/alter30bis49', 0),
              'alter50bis66' => new JsonSchemaDataPointer('1/alter50bis66', 0),
              'alterAb67' => new JsonSchemaDataPointer('1/alterAb67', 0),
            ],
            0,
            [
              'minimum' => 1,
              '$limitValidation' => JsonSchema::fromArray([
                'rules' => [
                 ['value' => ['const' => 0]],
                ],
              ]),
            ]
          ),
          'kommentar' => new JsonSchemaString(),
        ], ['required' => ['gesamt']]),

        'sonstiges' => new JsonSchemaString(),

        'bilder' => new JsonSchemaArray(
          new JsonSchemaString([
            'format' => 'uri',
            '$tag' => 'externalFile',
            'minLength' => 1,
          ]),
          ['maxItems' => 3]
        ),
        'nutzungsrechteBildmaterial' => new JsonSchemaBoolean(),

        'autorisiert' => new JsonSchemaBoolean(['const' => TRUE, 'default' => FALSE]),
        'korrekt' => new JsonSchemaBoolean(['const' => TRUE, 'default' => FALSE]),
        'doppelfinanzierungAusgeschlossen' => new JsonSchemaBoolean(['const' => TRUE, 'default' => FALSE]),
        'verantwortlichePerson' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),

        'datenschutzbestimmungen' => new JsonSchemaBoolean(['const' => TRUE, 'default' => FALSE]),

        'personalkostenKommentar' => new JsonSchemaString(),
        'honorareKommentar' => new JsonSchemaString(),
        'sachkostenKommentar' => new JsonSchemaString(),
      ], [
        'required' => [
          'ansprechperson',
          'umsetzung',
          'zieleErreicht',
          'probleme',
          'erfolgsmoment',
          'teilnehmende',
          'nutzungsrechteBildmaterial',
          'autorisiert',
          'korrekt',
          'doppelfinanzierungAusgeschlossen',
          'verantwortlichePerson',
          'datenschutzbestimmungen',
        ],
        '$limitValidation' => JsonSchema::fromArray([
          'condition' => [
            'evaluate' => [
              'expression' => 'action === "save"',
              'variables' => ['action' => new JsonSchemaDataPointer('/_action', '')],
            ],
          ],
        ]),
      ]),
    ]);
  }

}
