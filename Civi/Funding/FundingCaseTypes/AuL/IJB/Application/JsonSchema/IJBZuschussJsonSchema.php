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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class IJBZuschussJsonSchema extends JsonSchemaObject {

  public const TEILNEHMER_FESTBETRAG_FACHKRAEFTE = 40;

  public const TEILNEHMER_FESTBETRAG_JUGENDLICHE = 24;

  public const FAHRTKOSTEN_FESTBETRAG_AUSLAND_EUROPA = 0.12;

  public const FAHRTKOSTEN_FESTBETRAG_NICHT_EUROPA = 0.08;

  public const ZUSCHLAG_FESTBETRAG_FACHKRAEFTE = 50;

  public const ZUSCHLAG_FESTBETRAG_JUGENDLICHE = 30;

  public const ZUSCHLAG_MAX_FACHKRAEFTE = 500;

  public const ZUSCHLAG_MAX_JUGENDLICHE = 300;

  public const HONORARKOSTEN_FESTBETRAG = 305;

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(bool $report = FALSE) {
    $properties = [
      'teilnehmerkostenMax' => new JsonSchemaCalculate(
        'number',
        'begegnungsland == "deutschland" ? (
            artDerMassnahme == "fachkraefteprogramm"
            ? round(teilnehmertage * festbetragFachkraefte, 2)
            : round(teilnehmertage * festbetragJugendliche, 2)
          ) : 0',
        [
          'begegnungsland' => new JsonSchemaDataPointer('2/grunddaten/begegnungsland', ''),
          'artDerMassnahme' => new JsonSchemaDataPointer('2/grunddaten/artDerMassnahme', ''),
          'teilnehmertage' => new JsonSchemaDataPointer('2/teilnehmer/teilnehmertage'),
          'festbetragFachkraefte' => self::TEILNEHMER_FESTBETRAG_FACHKRAEFTE,
          'festbetragJugendliche' => self::TEILNEHMER_FESTBETRAG_JUGENDLICHE,
        ],
      ),
      'teilnehmerkosten' => new JsonSchemaMoney([
        'default' => 0,
        'maximum' => new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
      ], TRUE),
      'honorarkostenMax' => new JsonSchemaCalculate(
        'number',
        'begegnungsland == "deutschland" ? round(programmtage * festbetrag, 2) : 0',
        [
          'begegnungsland' => new JsonSchemaDataPointer('2/grunddaten/begegnungsland', ''),
          'programmtage' => new JsonSchemaDataPointer(
            sprintf('2/grunddaten/%s', $report ? 'programmtageMitHonorar' : 'programmtage'),
            0
          ),
          'festbetrag' => self::HONORARKOSTEN_FESTBETRAG,
        ],
      ),
      'honorarkosten' => new JsonSchemaMoney([
        'default' => 0,
        'maximum' => new JsonSchemaDataPointer('1/honorarkostenMax'),
      ], TRUE),
      // For foreign events we don't know whether it's inside or outside of
      // Europe. Thus, we have two properties.
      'fahrtkostenAuslandEuropaMax' => new JsonSchemaCalculate(
        'number',
        'floor(teilnehmerDeutschland * fahrtstreckeInKm * festbetrag)',
        [
          'teilnehmerDeutschland' => new JsonSchemaDataPointer(
            sprintf('2/teilnehmer/deutschland/%s', $report ? 'mitFahrtkosten' : 'gesamt'),
            0
          ),
          'fahrtstreckeInKm' => new JsonSchemaDataPointer('2/grunddaten/fahrtstreckeInKm', 0),
          'festbetrag' => self::FAHRTKOSTEN_FESTBETRAG_AUSLAND_EUROPA,
        ],
      ),
      'fahrtkostenNichtEuropaMax' => new JsonSchemaCalculate(
        'number',
        'floor(teilnehmerDeutschland * fahrtstreckeInKm * festbetrag)',
        [
          'teilnehmerDeutschland' => new JsonSchemaDataPointer(
            sprintf('2/teilnehmer/deutschland/%s', $report ? 'mitFahrtkosten' : 'gesamt'),
            0
          ),
          'fahrtstreckeInKm' => new JsonSchemaDataPointer('2/grunddaten/fahrtstreckeInKm', 0),
          'festbetrag' => self::FAHRTKOSTEN_FESTBETRAG_NICHT_EUROPA,
        ],
      ),
      'fahrtkostenMax' => new JsonSchemaCalculate(
        'number',
        'begegnungsland == "partnerland" ? max(fahrtkostenAuslandEuropaMax, fahrtkostenNichtEuropaMax) : 0',
        [
          'begegnungsland' => new JsonSchemaDataPointer('2/grunddaten/begegnungsland', ''),
          'fahrtkostenAuslandEuropaMax' => new JsonSchemaDataPointer('1/fahrtkostenAuslandEuropaMax'),
          'fahrtkostenNichtEuropaMax' => new JsonSchemaDataPointer('1/fahrtkostenNichtEuropaMax'),
        ]
      ),
      'fahrtkosten' => new JsonSchemaMoney([
        'default' => 0,
        'maximum' => new JsonSchemaDataPointer('1/fahrtkostenMax'),
      ], TRUE),
      'zuschlagMax' => new JsonSchemaCalculate(
        'number',
        'begegnungsland == "partnerland" ? (
          artDerMassnahme == "fachkraefteprogramm"
            ? min(round(teilnehmerDeutschlandGesamt * festbetragFachkraefte, 2), zuschlagMaxFachkraefte)
            : min(round(teilnehmerDeutschlandGesamt * festbetragJugendliche, 2), zuschlagMaxJugendliche)
          ) : 0',
        [
          'begegnungsland' => new JsonSchemaDataPointer('2/grunddaten/begegnungsland', ''),
          'artDerMassnahme' => new JsonSchemaDataPointer('2/grunddaten/artDerMassnahme', ''),
          'teilnehmerDeutschlandGesamt' => new JsonSchemaDataPointer('2/teilnehmer/deutschland/gesamt'),
          'festbetragFachkraefte' => self::ZUSCHLAG_FESTBETRAG_FACHKRAEFTE,
          'zuschlagMaxFachkraefte' => self::ZUSCHLAG_MAX_FACHKRAEFTE,
          'festbetragJugendliche' => self::ZUSCHLAG_FESTBETRAG_JUGENDLICHE,
          'zuschlagMaxJugendliche' => self::ZUSCHLAG_MAX_JUGENDLICHE,
        ]
      ),
      'zuschlag' => new JsonSchemaMoney([
        'default' => 0,
        'maximum' => new JsonSchemaDataPointer('1/zuschlagMax'),
      ], TRUE),
      'gesamtMax' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerkostenMax + honorarkostenMax + fahrtkostenMax + zuschlagMax, 2)',
        [
          'teilnehmerkostenMax' => new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
          'honorarkostenMax' => new JsonSchemaDataPointer('1/honorarkostenMax'),
          'fahrtkostenMax' => new JsonSchemaDataPointer('1/fahrtkostenMax'),
          'zuschlagMax' => new JsonSchemaDataPointer('1/zuschlagMax'),
        ],
      ),
      'gesamt' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerkosten + fahrtkosten + zuschlag + honorarkosten, 2)',
        [
          'teilnehmerkosten' => new JsonSchemaDataPointer(
            '1/teilnehmerkosten',
            new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
          ),
          'honorarkosten' => new JsonSchemaDataPointer(
            '1/honorarkosten',
            new JsonSchemaDataPointer('1/honorarkostenMax'),
          ),
          'fahrtkosten' => new JsonSchemaDataPointer(
            '1/fahrtkosten',
            new JsonSchemaCalculate(
              'number',
              'begegnungsland == "partnerland"
                ? min(fahrtkostenAuslandEuropaMax, fahrtkostenNichtEuropaMax)
                : 0',
              [
                'begegnungsland' => new JsonSchemaDataPointer('2/grunddaten/begegnungsland', ''),
                'fahrtkostenAuslandEuropaMax' => new JsonSchemaDataPointer('1/fahrtkostenAuslandEuropaMax'),
                'fahrtkostenNichtEuropaMax' => new JsonSchemaDataPointer('1/fahrtkostenNichtEuropaMax'),
              ],
            ),
          ),
          'zuschlag' => new JsonSchemaDataPointer(
            '1/zuschlag',
            new JsonSchemaDataPointer('1/zuschlagMax'),
          ),
        ], NULL, ['$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']])],
      ),
      'finanzierungGesamt' => new JsonSchemaCalculate(
        'number',
          'round(mittelGesamt + zuschussGesamt, 2)',
        [
          'mittelGesamt' => new JsonSchemaDataPointer('2/finanzierung/mittelGesamt'),
          'zuschussGesamt' => new JsonSchemaDataPointer('1/gesamt'),
        ]
      ),
      'kostenGesamt' => new JsonSchemaCalculate('number', 'kostenGesamt', [
        'kostenGesamt' => new JsonSchemaDataPointer('2/kosten/kostenGesamt'),
      ]),
      'finanzierungKostenDifferenz' => new JsonSchemaCalculate(
        'number',
        'round(finanzierungGesamt - kostenGesamt, 2)',
        [
          'finanzierungGesamt' => new JsonSchemaDataPointer('1/finanzierungGesamt'),
          'kostenGesamt' => new JsonSchemaDataPointer('1/kostenGesamt'),
        ],
        NULL,
        [
          '$validations' => JsonSchema::convertToJsonSchemaArray([
            [
              'keyword' => 'const',
              'value' => 0,
              'message' => 'Die Finanzierung ist nicht ausgeglichen.',
            ],
          ]),
        ]
      ),
    ];

    parent::__construct($properties, ['required' => ['teilnehmerkosten', 'fahrtkosten', 'honorarkosten', 'zuschlag']]);
  }

}
