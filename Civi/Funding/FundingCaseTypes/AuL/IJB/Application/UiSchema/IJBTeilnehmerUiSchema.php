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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema\IJBTeilnehmerJsonSchema;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBTeilnehmerUiSchema extends JsonFormsCategory {

  private string $scopePrefix;

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(string $scopePrefix, bool $report = FALSE) {
    $this->scopePrefix = $scopePrefix;

    $teilnehmerDeutschlandElements = [
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/gesamt",
        'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
      ),
      new JsonFormsControl("$scopePrefix/deutschland/properties/weiblich", 'davon weiblich'),
      new JsonFormsControl("$scopePrefix/deutschland/properties/divers", 'davon divers'),
      new JsonFormsControl("$scopePrefix/deutschland/properties/unter27", 'davon U27'),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/inJugendhilfeEhrenamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/inJugendhilfeHauptamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/referenten",
        'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
      ),
    ];
    if ($report) {
      $teilnehmerDeutschlandElements[] = new JsonFormsControl(
        "$scopePrefix/deutschland/properties/mitFahrtkosten",
        'davon Personen, bei denen tatsächlich Fahrtkosten angefallen sind',
      );
    }

    parent::__construct('Teilnehmer*innen', [
      new JsonFormsGroup('Teilnehmer*innen aus Deutschland', $teilnehmerDeutschlandElements),
      new JsonFormsGroup('Teilnehmer*innen aus dem Partnerland', [
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/gesamt",
          'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
        ),
        new JsonFormsControl("$scopePrefix/partnerland/properties/weiblich", 'davon weiblich'),
        new JsonFormsControl("$scopePrefix/partnerland/properties/divers", 'davon divers'),
        new JsonFormsControl("$scopePrefix/partnerland/properties/unter27", 'davon U27'),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/inJugendhilfeEhrenamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/inJugendhilfeHauptamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/referenten",
          'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
        ),
      ]),
      new JsonFormsControl("$scopePrefix/teilnehmertage", 'Teilnehmendentage'),
    ]);
  }

  /**
   * Adds an asterisk to every non-required field. In report all fields are
   * required.
   */
  public function withRequiredLabels(IJBTeilnehmerJsonSchema $teilnehmerSchema): self {
    $clone = clone $this;
    $clone->modifyLabels($clone, $teilnehmerSchema);

    return $clone;
  }

  private function modifyLabels(JsonFormsElement $element, IJBTeilnehmerJsonSchema $teilnehmerJsonSchema): void {
    if ('Control' === $element['type']) {
      // @phpstan-ignore-next-line
      $relativeScope = 'properties' . substr($element['scope'], strlen($this->scopePrefix));
      $schemaPath = explode('/', $relativeScope);
      $propertyName = array_pop($schemaPath);
      array_pop($schemaPath);
      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $objectSchema */
      $objectSchema = $teilnehmerJsonSchema->getKeywordValueAt($schemaPath);
      // @phpstan-ignore-next-line
      if (!in_array($propertyName, $objectSchema['required'] ?? [], TRUE)) {
        // @phpstan-ignore-next-line
        $element['label'] .= '&nbsp;*';
      }
    }
    else {
      /** @phpstan-var list<JsonFormsElement> $elements */
      $elements = $element['elements'] ?? [];
      foreach ($elements as $subElement) {
        $this->modifyLabels($subElement, $teilnehmerJsonSchema);
      }
    }
  }

}
