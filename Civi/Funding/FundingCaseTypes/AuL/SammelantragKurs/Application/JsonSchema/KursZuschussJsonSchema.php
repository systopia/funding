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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursZuschussJsonSchema extends JsonSchemaObject {

  public const TEILNEHMERFESTBETRAG = 40;

  public const FAHRTKOSTENFESTBETRAG = 60;

  public const HONORARKOSTENFESTBETRAG = 305;

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(bool $report = FALSE) {
    $properties = [
      'teilnehmerkostenMax' => new JsonSchemaCalculate(
        'number',
        'teilnehmertage * festbetrag',
        [
          'teilnehmertage' => new JsonSchemaDataPointer('2/grunddaten/teilnehmertage'),
          'festbetrag' => self::TEILNEHMERFESTBETRAG,
        ],
      ),
      'teilnehmerkosten' => new JsonSchemaMoney([
        '$default' => new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
        'maximum' => new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'teilnehmerkosten',
          'identifier' => 'teilnehmerkosten',
          'clearing' => [
            'itemLabel' => 'Unterkunft und Verpflegung',
          ],
        ]),
      ], TRUE),
      'fahrtkostenMax' => new JsonSchemaCalculate(
        'number',
        'teilnehmer * festbetrag',
        [
          'teilnehmer' => new JsonSchemaDataPointer(
            sprintf('2/grunddaten/teilnehmer/%s', $report ? 'mitFahrtkosten' : 'gesamt'),
            0
          ),
          'festbetrag' => self::FAHRTKOSTENFESTBETRAG,
        ],
      ),
      'fahrtkosten' => new JsonSchemaMoney([
        '$default' => new JsonSchemaDataPointer('1/fahrtkostenMax'),
        'maximum' => new JsonSchemaDataPointer('1/fahrtkostenMax'),
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'fahrtkosten',
          'identifier' => 'fahrtkosten',
          'clearing' => [
            'itemLabel' => 'Fahrtkosten',
          ],
        ]),
      ], TRUE),
      'honorarkostenMax' => new JsonSchemaCalculate(
        'number',
        'programmtage * referenten * festbetrag',
        [
          'programmtage' => new JsonSchemaDataPointer('2/grunddaten/programmtage'),
          'referenten' => new JsonSchemaDataPointer(
            sprintf('2/grunddaten/teilnehmer/%s', $report ? 'referentenMitHonorar' : 'referenten'),
            0
          ),
          'festbetrag' => self::HONORARKOSTENFESTBETRAG,
        ],
      ),
      'honorarkosten' => new JsonSchemaMoney([
        '$default' => new JsonSchemaDataPointer('1/honorarkostenMax'),
        'maximum' => new JsonSchemaDataPointer('1/honorarkostenMax'),
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'honorarkosten',
          'identifier' => 'honorarkosten',
          'clearing' => [
            'itemLabel' => 'Honorarkosten',
          ],
        ]),
      ], TRUE),
      // Not displayed, but used to have a cost item so receipts for previously
      // unknown costs can be submitted.
      'sonstigeAusgaben' => new JsonSchemaNumber([
        '$default' => 0,
        'const' => 0,
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'sonstigeAusgaben',
          'identifier' => 'sonstigeAusgaben',
          'clearing' => [
            'itemLabel' => 'Sonstige Ausgaben',
          ],
        ]),
      ]),
      'gesamtMax' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerkostenMax + fahrtkostenMax + honorarkostenMax, 2)',
        [
          'teilnehmerkostenMax' => new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
          'fahrtkostenMax' => new JsonSchemaDataPointer('1/fahrtkostenMax'),
          'honorarkostenMax' => new JsonSchemaDataPointer('1/honorarkostenMax'),
        ],
      ),
      'gesamt' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerkosten + fahrtkosten + honorarkosten, 2)',
        [
          'teilnehmerkosten' => new JsonSchemaDataPointer(
            '1/teilnehmerkosten',
            new JsonSchemaDataPointer('1/teilnehmerkostenMax'),
          ),
          'fahrtkosten' => new JsonSchemaDataPointer(
            '1/fahrtkosten',
            new JsonSchemaDataPointer('1/fahrtkostenMax'),
          ),
          'honorarkosten' => new JsonSchemaDataPointer(
            '1/honorarkosten',
            new JsonSchemaDataPointer('1/honorarkostenMax'),
          ),
        ], NULL, ['$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']])]
      ),
    ];

    parent::__construct($properties);
  }

}
