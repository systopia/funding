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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\Util\JsonConverter;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1JsonSchema
 */
class AVK1JsonSchemaTest extends TestCase {

  use AssertFormTrait;

  use AssertValidationResultTrait;

  private ApplicationSchemaValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->validator = new ApplicationSchemaValidator(
      new NullTranslator(),
      OpisApplicationValidatorFactory::getValidator()
    );
  }

  public function testJsonSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $jsonSchema = new AVK1JsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
    );

    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $data = [
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'internerBezeichner' => 'interne id',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-25',
          ],
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
        ],
        'teilnehmer' => [
          'gesamt' => 5,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 0,
        ],
      ],
      'empfaenger' => 2,
      'kosten' => [
        'honorare' => [
          [
            'berechnungsgrundlage' => 'tagessatz',
            'dauer' => 11.1,
            'verguetung' => 22.22,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => 9.9,
            'verguetung' => 10,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'unterkunftUndVerpflegung' => 222.22,
        'fahrtkosten' => [
          'intern' => 2.2,
          'anTeilnehmerErstattet' => 3.3,
        ],
        'sachkosten' => [
          'ausstattung' => [
            [
              'gegenstand' => 'Thing1',
              'betrag' => 5.5,
            ],
            [
              'gegenstand' => 'Thing2',
              'betrag' => 6.6,
            ],
          ],
        ],
        'sonstigeAusgaben' => [
          [
            'betrag' => 12.34,
            'zweck' => 'Sonstige Ausgaben 1',
          ],
          [
            'betrag' => 56.78,
            'zweck' => 'Sonstige Ausgaben 2',
          ],
        ],
        'versicherung' => ['teilnehmer' => 9.9],
      ],
      'finanzierung' => [
        'teilnehmerbeitraege' => 100.00,
        'eigenmittel' => 10.00,
        'oeffentlicheMittel' => [
          'europa' => 1.11,
          'bundeslaender' => 2.22,
          'staedteUndKreise' => 3.33,
        ],
        'sonstigeMittel' => [
          [
            'betrag' => 1.0,
            'quelle' => 'Quelle 1',
          ],
          [
            'betrag' => 2.0,
            'quelle' => 'Quelle 2',
          ],
        ],
      ],
      'beschreibung' => [
        'thematischeSchwerpunkte' => 'Schwerpunkte',
        'geplanterAblauf' => 'Ablauf',
        'beitragZuPolitischerJugendbildung' => 'Beitrag',
        'zielgruppe' => 'Zielgruppe',
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 22,
        'veranstaltungsort' => 'Veranstaltungsort',
        'partner' => 'Partner',
      ],
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
      'foo' => 'baz',
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(10, $result->getCostItemsData());
    static::assertCount(7, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    $unterkunftUndVerpflegung = 222.22;
    $honorar1 = round(11.1 * 22.22, 2);
    static::assertSame($honorar1, $resultData->kosten->honorare[0]->betrag);
    $honorar2 = round(9.9 * 10, 2);
    static::assertSame($honorar2, $resultData->kosten->honorare[1]->betrag);
    $honorareGesamt = $honorar1 + $honorar2;
    static::assertSame($honorareGesamt, $resultData->kosten->honorareGesamt);
    $fahrtkostenGesamt = 2.2 + 3.3;
    static::assertSame($fahrtkostenGesamt, $resultData->kosten->fahrtkostenGesamt);
    $sachkostenGesamt = 5.5 + 6.6;
    static::assertSame($sachkostenGesamt, $resultData->kosten->sachkostenGesamt);
    $sonstigeAusgabenGesamt = 12.34 + 56.78;
    static::assertSame($sonstigeAusgabenGesamt, $resultData->kosten->sonstigeAusgabenGesamt);
    $versicherungTeilnehmer = 9.9;
    $gesamtkosten = $unterkunftUndVerpflegung
      + $honorareGesamt
      + $fahrtkostenGesamt
      + $sachkostenGesamt
      + $sonstigeAusgabenGesamt
      + $versicherungTeilnehmer;
    static::assertSame($gesamtkosten, $resultData->kosten->gesamtkosten);

    $oeffentlicheMittelGesamt = 1.11 + 2.22 + 3.33;
    static::assertSame($oeffentlicheMittelGesamt, $resultData->finanzierung->oeffentlicheMittelGesamt);
    $sonstigeMittelGesamt = 1.0 + 2.0;
    static::assertSame($sonstigeMittelGesamt, $resultData->finanzierung->sonstigeMittelGesamt);
    $gesamtmittel = 100.00 + 10.00 + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt;
    static::assertSame($gesamtmittel, $resultData->finanzierung->gesamtmittel);

    static::assertSame($gesamtkosten - $gesamtmittel, $resultData->finanzierung->beantragterZuschuss);

    $resultData->foo = 'bar';
    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'funding_application_process_extra.internal_identifier' => 'interne id',
      'recipient_contact_id' => 2,
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-25',
      'amount_requested' => $gesamtkosten - $gesamtmittel,
    ], $mappedData);
  }

  public function testNotAllowedDates(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $jsonSchema = new AVK1JsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
    );

    $data = (object) [
      'grunddaten' => (object) [
        'zeitraeume' => [
          (object) [
            'beginn' => '2022-08-23',
            'ende' => '2022-08-26',
          ],
        ],
      ],
    ];

    $validator = OpisValidatorFactory::getValidator();
    $validator->setMaxErrors(20);
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    $beginnErrors = $errorCollector->getErrorsAt('/grunddaten/zeitraeume/0/beginn');
    static::assertCount(1, $beginnErrors);
    static::assertSame('minDate', $beginnErrors[0]->keyword());
    $endeErrors = $errorCollector->getErrorsAt('/grunddaten/zeitraeume/0/ende');
    static::assertCount(1, $endeErrors);
    static::assertSame('maxDate', $endeErrors[0]->keyword());
  }

  public function testEndeBeforeBeginn(): void {
    $jsonSchema = new AVK1JsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      [],
    );

    $data = (object) [
      'grunddaten' => (object) [
        'zeitraeume' => [
          (object) [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-24',
          ],
        ],
      ],
    ];

    $validator = OpisValidatorFactory::getValidator();
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    static::assertFalse($errorCollector->hasErrorAt('/grunddaten/zeitraeume/0/beginn'));
    $endeErrors = $errorCollector->getErrorsAt('/grunddaten/zeitraeume/0/ende');
    static::assertCount(1, $endeErrors);
    static::assertSame('minDate', $endeErrors[0]->keyword());
  }

}
