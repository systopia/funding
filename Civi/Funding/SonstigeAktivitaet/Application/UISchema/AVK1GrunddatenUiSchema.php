<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\SonstigeAktivitaet\Application\UISchema;

use Civi\Funding\SonstigeAktivitaet\Application\JsonSchema\AVK1GrunddatenSchema;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class AVK1GrunddatenUiSchema extends JsonFormsCategory {

  private string $scopePrefix;

  public function __construct(string $scopePrefix) {
    $this->scopePrefix = $scopePrefix;

    $elements = [
      new JsonFormsMarkup(<<<EOD
<p>Bei Sonstigen Aktivitäten kann die Summe einer Kostenkategorie (Einzelansätze,
nicht die einzelne Positionen) um bis zu 20 % überschritten werden, solange die
Überschreitung durch entsprechende Einsparungen bei anderen Einzelansätzen
ausgeglichen werden kann. Die bewilligte Gesamtsumme kann nicht überschritten
werden.</p>
EOD
      ),
      new JsonFormsControl(
        "$scopePrefix/internerBezeichner",
        'Interner Bezeichner'
      ),
      new JsonFormsControl(
        "$scopePrefix/titel",
        'Titel'
      ),
      new JsonFormsControl(
        "$scopePrefix/kurzbeschreibungDesInhalts",
        'Kurzbeschreibung des Inhalts',
        NULL,
        [
          'multi' => TRUE,
          'placeholder' => 'Maximal 500 Zeichen',
        ]
      ),
      new JsonFormsArray(
        "$scopePrefix/zeitraeume",
        'Zeiträume',
        NULL,
        [
          new JsonFormsControl('#/properties/beginn', 'Beginn'),
          new JsonFormsControl('#/properties/ende', 'Ende'),
        ],
        [
          'addButtonLabel' => 'Zeitraum hinzufügen',
          'removeButtonLabel' => 'Zeitraum entfernen',
        ]
      ),
      new JsonFormsGroup('Teilnehmer*innen', [
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/gesamt",
          'Gesamtanzahl der Teilnehmer*innen'
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/weiblich",
          'davon weiblich'
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/divers",
          'davon divers'
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/unter27",
          'davon U27'
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/inJugendhilfeEhrenamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/inJugendhilfeHauptamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnehmer/properties/referenten",
          'davon Referent*innen'
        ),
      ], 'Wie viele Teilnehmer*innen werden für die Veranstaltung erwartet?'),
    ];

    parent::__construct(
      'Grunddaten',
      $elements,
    );
  }

  /**
   * Adds an asterisk to every non-required field. In report all fields are
   * required.
   */
  public function withRequiredLabels(AVK1GrunddatenSchema $grunddatenJsonSchema): self {
    $clone = clone $this;
    $clone->modifyLabels($clone, $grunddatenJsonSchema);

    return $clone;
  }

  private function modifyLabels(JsonFormsElement $element, AVK1GrunddatenSchema $grunddatenJsonSchema): void {
    if ('Control' === $element['type']) {
      // @phpstan-ignore-next-line
      $relativeScope = 'properties' . substr($element['scope'], strlen($this->scopePrefix));
      $schemaPath = explode('/', $relativeScope);
      $propertyName = array_pop($schemaPath);
      array_pop($schemaPath);
      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $objectSchema */
      $objectSchema = $grunddatenJsonSchema->getKeywordValueAt($schemaPath);
      // @phpstan-ignore-next-line
      if (!in_array($propertyName, $objectSchema['required'] ?? [], TRUE) && !$element->hasKeyword('$calculate')) {
        // @phpstan-ignore-next-line
        $element['label'] .= '&nbsp;*';
      }
    }
    else {
      /** @phpstan-var list<JsonFormsElement> $elements */
      $elements = $element['elements'] ?? [];
      foreach ($elements as $subElement) {
        $this->modifyLabels($subElement, $grunddatenJsonSchema);
      }
    }
  }

}
