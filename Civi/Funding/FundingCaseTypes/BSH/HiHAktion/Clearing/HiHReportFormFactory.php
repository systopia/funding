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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing;

use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\JsonSchema\HiHReportDataJsonSchema;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class HiHReportFormFactory implements ReportFormFactoryInterface {

  use HiHSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    $scopePrefix = '#/properties/reportData/properties';
    $scopePrefixAnsprechperson = "$scopePrefix/ansprechperson/properties";
    $scopePrefixTeilnehmende = "$scopePrefix/teilnehmende/properties";

    return new ReportForm(
      new HiHReportDataJsonSchema(),
      new JsonFormsCategorization([
        new JsonFormsCategory('Allgemeines', [
          new JsonFormsGroup('Angaben zum Projekt', [
            new JsonFormsControl("$scopePrefix/titel", 'Projekttitel'),
            new JsonFormsControl("$scopePrefix/antragsnummer", 'Antragsnummer'),
            new JsonFormsGroup('Projektlaufzeit', [
              new JsonFormsControl("$scopePrefix/laufzeitVon", 'von'),
              new JsonFormsControl("$scopePrefix/laufzeitBis", 'bis'),
              new JsonFormsControl("$scopePrefix/empfaenger", 'Empfänger'),
              new JsonFormsControl("$scopePrefix/projekttraeger", 'Projektträger'),
            ]),
          ]),
          new JsonFormsGroup('Ansprechperson bei Rückfragen', [
            new JsonFormsControl("$scopePrefixAnsprechperson/name", 'Name'),
            new JsonFormsControl("$scopePrefixAnsprechperson/vorname", 'Vorname'),
            new JsonFormsControl("$scopePrefixAnsprechperson/email", 'Mailadresse'),
            new JsonFormsControl("$scopePrefixAnsprechperson/telefonnummer", 'Telefonnummer'),
          ]),
        ]),

        new JsonFormsCategory('Sachbericht', [

          new JsonFormsGroup('Umsetzung des Projektes', [
            new JsonFormsControl(
              "$scopePrefix/umsetzung",
              'Was wurde im Projekt gemacht und wie ist die Umsetzung gelungen? (2.500 bis 3.000 Zeichen)',
              NULL,
              ['multi' => TRUE],
            ),
            new JsonFormsControl(
              "$scopePrefix/zieleErreicht",
              'Wurden die Projektziele erreicht?',
              NULL,
              ['format' => 'radio'],
            ),
            new JsonFormsControl(
              "$scopePrefix/probleme",
              <<<EOD
Wenn Nein: Was lief anders als geplant, welche Probleme gab es bei der
Projektumsetzung und wie wurden sie gelöst?
EOD
          ,
              NULL,
              ['multi' => TRUE],
              [
                'rule' => new JsonFormsRule(
                  'SHOW', "$scopePrefix/zieleErreicht", JsonSchema::fromArray(['const' => FALSE])
                ),
              ]
            ),
            new JsonFormsControl(
              "$scopePrefix/erfolgsmoment",
              <<<EOD
Bitte erzählen Sie von einem Erfolgsmoment oder einer besonderen Beobachtung aus
dem Berichtszeitraum, die zeigen, wie sich das Projekt gegen Einsamkeit
ausgewirkt hat.
EOD,
              NULL,
              ['multi' => TRUE],
            ),
          ]),

          new JsonFormsGroup('Zielgruppe und Teilnehmende', [
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alterUnter6",
              'Wie viele Teilnehmer:innen waren Kinder unter 6 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alter6bis12",
              'Wie viele Teilnehmer:innen waren Kinder zwischen 6 und 12 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alter13bis19",
              'Wie viele Teilnehmer:innen waren Jugendliche zwischen 13 und 19 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alter20bis29",
              'Wie viele Teilnehmer:innen waren junge Erwachsene zwischen 20 und 29 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alter30bis49",
              'Wie viele Teilnehmer:innen waren Erwachsene zwischen 30 und 49 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alter50bis66",
              'Wie viele Teilnehmer:innen waren Erwachsene zwischen 50 und 66 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/alterAb67",
              'Wie viele Teilnehmer:innen waren Senior:innen ab 67 Jahren?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/gesamt",
              'Wie viele Teilnehmer:innen wurden mit dem Projekt erreicht?'
            ),
            new JsonFormsControl(
              "$scopePrefixTeilnehmende/kommentar",
              'Was Sie uns noch zu den erreichten Teilnehmenden sagen möchten:',
              NULL,
              ['multi' => TRUE],
            ),
          ]),

          new JsonFormsGroup('Was Sie uns sonst noch sagen möchten', [
            new JsonFormsControl(
              "$scopePrefix/sonstiges",
              <<<EOD
Falls es Thematiken gab, die Sie bisher in diesem Formular noch nicht abbilden
konnten, nutzen Sie sehr gerne das folgende Feld:
EOD
            ),
          ]),

          new JsonFormsGroup('Fotos und Bildmaterialien', [
            new JsonFormsArray(
              "$scopePrefix/bilder",
              <<<EOD
Fotos/Flyer helfen einen besseren Einblick in das durchgeführte Projekt zu
bekommen und können hier hochgeladen werden.
EOD,
              'Bis zu drei Dateien möglich. 5 MB Limit pro Datei. Erlaubte Dateitypen: jpg, jpeg, gif, png, pdf',
              [new JsonFormsControl('#/', '', NULL, ['format' => 'file'])],
              [
                'addButtonLabel' => 'Datei hinzufügen',
                'removeButtonLabel' => 'Datei entfernen',
              ]
            ),
          ]),
          new JsonFormsGroup('Einräumung von Nutzungsrechten an Bildmaterialien', [
            new JsonFormsControl(
              "$scopePrefix/nutzungsrechteBildmaterial",
              <<<EOD
Der Projektträger räumt dem Verein Stiften für alle e.V. ein zeitlich,
räumlich und inhaltlich unbeschränktes Nutzungsrecht an den im
Rahmen des Verwendungsnachweises hochgeladenen
Bildmaterialien (z. B. Fotos, Flyer, Grafiken) ein. Dies umfasst
insbesondere das Recht zur Vervielfältigung, Verbreitung,
öffentlichen Zugänglichmachung sowie zur Veröffentlichung in
digitalen und analogen Medien, z. B. auf der Website des Vereins, in
sozialen Netzwerken oder Printveröffentlichungen im Rahmen der
Öffentlichkeitsarbeit.
Der Projektträger versichert, dass er zur Einräumung dieser Rechte
berechtigt ist und dass keine Rechte Dritter entgegenstehen. Für
erkennbare abgebildete Personen liegt eine entsprechende
Einwilligung zur Veröffentlichung vor, bei Minderjährigen
einschließlich der Einwilligung der Erziehungsberechtigten.
EOD
            ),
          ]),

          new JsonFormsGroup('Erklärungen zum Verwendungsnachweis', [
            new JsonFormsControl(
              "$scopePrefix/autorisiert",
            'Ich bin autorisiert, den Verwendungsnachweis im Namen des anfangs genannten Projektträgers einzureichen.'
            ),
            new JsonFormsControl(
              "$scopePrefix/korrekt",
              'Ich bestätige, dass alle Informationen in diesem Verwendungsnachweis korrekt sind.',
            ),
            new JsonFormsControl(
              "$scopePrefix/doppelfinanzierungAusgeschlossen",
              <<<EOD
Ich versichere, dass wir zur Finanzierung der benannten Ausgaben
keine bzw. nur die im Verwendungsnachweis angegebenen
Einnahmen von dritter Seite erhalten haben und dass damit eine
Doppelfinanzierung ausgeschlossen ist.
EOD

            ),
            new JsonFormsControl(
              "$scopePrefix/verantwortlichePerson",
              'Vor- und Nachname der verantwortlichen Person'
            ),
          ]),

          new JsonFormsGroup('Datenschutzbestimmungen', [
            new JsonFormsMarkup(<<<EOD
Der Träger ist damit einverstanden, dass die hier eingetragenen
(personenbezogenen) Daten sowie hochgeladenen Dokumente in der
Datenbank der BürgerStiftung Hamburg verarbeitet und gespeichert werden
dürfen und zum Zweck der Begutachtung, Dokumentation,
Berichterstattung und Veröffentlichung an den NDR, sowie zur
Begutachtung, Dokumentation und Weiterleitung von Fördermitteln an die
zuständige Bürgerstiftung und den Verein Stiften für alle e.V. weitergeleitet
werden dürfen. Weitere Infos finden Sie in unseren
<a href="https://buergerstiftung-hamburg.de/service/datenschutz/" target="_blank">Hinweisen zum Datenschutz</a>.
Hinweis: Diese Einwilligung können Sie jederzeit mit Wirkung für die Zukunft
widerrufen, indem Sie uns einen formlosen Widerruf per E-Mail schicken.
EOD
            ),
            new JsonFormsControl(
              "$scopePrefix/datenschutzbestimmungen",
              'Ich habe die Hinweise zum Datenschutz gelesen und stimme zu.'
            ),
          ]),

        ]),
      ])
    );
  }

}
