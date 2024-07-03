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

namespace Civi\Funding\IJB\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\Util\JsonConverter;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\IJB\Application\JsonSchema\IJBApplicationJsonSchema
 */
final class IJBApplicationJsonSchemaTest extends TestCase {

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

  public function testFachkraefteprogrammDeutschland(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      $possibleRecipients,
      ['_action' => $actionSchema],
      ['required' => ['_action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('_action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('_action'));
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $fahrtstreckeInKm = 100;
    $teilnehmerDeutschlandGesamt = 10;
    $teilnehmerPartnerlandGesamt = 11;
    $teilnehmerGesamt = $teilnehmerDeutschlandGesamt + $teilnehmerPartnerlandGesamt;

    // Kosten
    $unterkunftUndVerpflegung = 11.11;
    $honorarDauer1 = 10;
    $honorarVerguetung1 = 22.22;
    $honorarDauer2 = 11;
    $honorarVerguetung2 = 22.23;
    $honorareGesamt = round($honorarDauer1 * $honorarVerguetung1 + $honorarDauer2 * $honorarVerguetung2, 2);
    $fahrtkostenFlug = 333.33;
    $fahrtkostenAnTeilnehmerErstattet = 555.55;
    $fahrtkostenGesamt = round($fahrtkostenFlug + $fahrtkostenAnTeilnehmerErstattet, 2);
    $programmkosten = 111.11;
    $kostenArbeitsmaterial = 222.22;
    $programmfahrtkosten = 444.44;
    $programmkostenGesamt = round($programmkosten + $kostenArbeitsmaterial + $programmfahrtkosten, 2);
    $sonstigeKosten1 = 12.34;
    $sonstigeKosten2 = 12.35;
    $sonstigeKostenGesamt = round($sonstigeKosten1 + $sonstigeKosten2, 2);
    $sonstigeAusgabe1 = 56.78;
    $sonstigeAusgabe2 = 56.79;
    $sonstigeAusgabenGesamt = round($sonstigeAusgabe1 + $sonstigeAusgabe2, 2);
    // Zuschlagsrelevante Kosten
    $kostenProgrammabsprachen = 11.11;
    $kostenVorbereitungsmaterial = 12.12;
    $kostenVeroeffentlichungen = 22.22;
    $kostenZuschlagHonorare = 33.33;
    $fahrtkostenUndVerpflegung = 44.44;
    $reisekosten = 55.55;
    $mietkosten = 66.66;
    // Zuschlagsrelevante Kosten gibt es nur für Maßnahmen im Ausland.
    $zuschlagsrelevanteKostenGesamt = 0;

    $kostenGesamt = round($unterkunftUndVerpflegung + $honorareGesamt + $fahrtkostenGesamt
      + $programmkostenGesamt + $sonstigeKostenGesamt + $sonstigeAusgabenGesamt + $zuschlagsrelevanteKostenGesamt, 2);

    // Mittel
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);

    // Zuschuss
    $zuschussTeilnehmerkosten = 12.34;
    $zuschussHonorarkosten = 23.45;
    $zuschussFahrtkosten = 0.0;
    $zuschussZuschlag = 0.0;
    $zuschussGesamt = round($zuschussTeilnehmerkosten + $zuschussHonorarkosten
      + $zuschussFahrtkosten + $zuschussZuschlag, 2);

    // Finanzierung muss ausgeglichen sein.
    $eigenmittel = round($kostenGesamt - $zuschussGesamt - $fremdmittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    $data = [
      '_action' => 'submitAction1',
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-26',
          ],
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
        ],
        'artDerMassnahme' => 'fachkraefteprogramm',
        'begegnungsland' => 'deutschland',
        'stadt' => 'Stadt',
        'land' => 'Land',
        'fahrtstreckeInKm' => $fahrtstreckeInKm,
      ],
      'teilnehmer' => [
        'deutschland' => [
          'gesamt' => $teilnehmerDeutschlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
        'partnerland' => [
          'gesamt' => $teilnehmerPartnerlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
      ],
      'empfaenger' => 2,
      'partnerorganisation' => [
        'name' => 'abc',
        'adresse' => 'def',
        'land' => 'ghi',
        'email' => 'test@example.org',
        'telefon' => '00123456789',
        'kontaktperson' => 'jkl',
        'fortsetzungsmassnahme' => TRUE,
        'konzeptionellNeu' => FALSE,
        'austauschSeit' => '06.2000',
        'bisherigeBegegnungenInDeutschland' => 'Hier 2001',
        'bisherigeBegegnungenImPartnerland' => 'Dort 2002',
      ],
      'kosten' => [
        'unterkunftUndVerpflegung' => $unterkunftUndVerpflegung,
        'honorare' => [
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer1,
            'verguetung' => $honorarVerguetung1,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          [
            'berechnungsgrundlage' => 'tagessatz',
            'dauer' => $honorarDauer2,
            'verguetung' => $honorarVerguetung2,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'fahrtkosten' => [
          'flug' => $fahrtkostenFlug,
          'anTeilnehmerErstattet' => $fahrtkostenAnTeilnehmerErstattet,
        ],
        'programmkosten' => [
          'programmkosten' => $programmkosten,
          'arbeitsmaterial' => $kostenArbeitsmaterial,
          'fahrt' => $programmfahrtkosten,
        ],
        'sonstigeKosten' => [
          [
            'gegenstand' => 'Gegenstand 1',
            'betrag' => $sonstigeKosten1,
          ],
          [
            'gegenstand' => 'Gegenstand 2',
            'betrag' => $sonstigeKosten2,
          ],
        ],
        'sonstigeAusgaben' => [
          [
            'zweck' => 'Zweck 1',
            'betrag' => $sonstigeAusgabe1,
          ],
          [
            'zweck' => 'Zweck 2',
            'betrag' => $sonstigeAusgabe2,
          ],
        ],
        'zuschlagsrelevanteKosten' => [
          'programmabsprachen' => $kostenProgrammabsprachen,
          'vorbereitungsmaterial' => $kostenVorbereitungsmaterial,
          'veroeffentlichungen' => $kostenVeroeffentlichungen,
          'honorare' => $kostenZuschlagHonorare,
          'fahrtkostenUndVerpflegung' => $fahrtkostenUndVerpflegung,
          'reisekosten' => $reisekosten,
          'miete' => $mietkosten,
        ],
      ],
      'finanzierung' => [
        'teilnehmerbeitraege' => $teilnehmerBeitrage,
        'eigenmittel' => $eigenmittel,
        'oeffentlicheMittel' => [
          'europa' => $mittelEuropa,
          'bundeslaender' => $mittelBundeslaender,
          'staedteUndKreise' => $mittelStaedteUndKreise,
        ],
        'sonstigeMittel' => [
          [
            'quelle' => 'Quelle 1',
            'betrag' => $sonstigesMittel1,
          ],
          [
            'quelle' => 'Quelle 2',
            'betrag' => $sonstigesMittel2,
          ],
        ],
      ],
      'zuschuss' => [
        'teilnehmerkosten' => $zuschussTeilnehmerkosten,
        'honorarkosten' => $zuschussHonorarkosten,
        'fahrtkosten' => $zuschussFahrtkosten,
        'zuschlag' => $zuschussZuschlag,
      ],
      'beschreibung' => [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 12,
        'inhalt' => 'Inhalt',
        'erlaeuterungen' => 'Erläuterungen',
        'qualifikation' => 'Qualifikation',
      ],
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(19, $result->getCostItemsData());
    static::assertCount(7, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    $programmtage = 3;
    static::assertSame($programmtage, $resultData->grunddaten->programmtage);
    static::assertSame($teilnehmerGesamt, $resultData->teilnehmer->gesamt);
    static::assertSame($programmtage * $teilnehmerGesamt, $resultData->teilnehmer->teilnehmertage);

    static::assertSame($honorareGesamt, $resultData->kosten->honorareGesamt);
    static::assertSame($fahrtkostenGesamt, $resultData->kosten->fahrtkostenGesamt);
    static::assertSame($programmkostenGesamt, $resultData->kosten->programmkostenGesamt);
    static::assertSame($sonstigeKostenGesamt, $resultData->kosten->sonstigeKostenGesamt);
    static::assertSame($sonstigeAusgabenGesamt, $resultData->kosten->sonstigeAusgabenGesamt);
    static::assertSame($zuschlagsrelevanteKostenGesamt, $resultData->kosten->zuschlagsrelevanteKostenGesamt);
    static::assertSame($kostenGesamt, $resultData->kosten->kostenGesamt);

    static::assertSame($oeffentlicheMittelGesamt, $resultData->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $resultData->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $resultData->finanzierung->mittelGesamt);

    static::assertSame(
      round($teilnehmerGesamt * $programmtage * 40, 2),
      $resultData->zuschuss->teilnehmerkostenMax,
    );
    static::assertSame(
      round($programmtage * 305, 2),
      $resultData->zuschuss->honorarkostenMax,
    );
    static::assertSame(
      round($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.08, 2),
      $resultData->zuschuss->fahrtkostenAuslandEuropaMax
    );
    static::assertSame(
      round($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.12, 2),
      $resultData->zuschuss->fahrtkostenNichtEuropaMax
    );
    static::assertSame(0, $resultData->zuschuss->fahrtkostenMax);
    static::assertSame(0, $resultData->zuschuss->zuschlagMax);
    static::assertSame($zuschussGesamt, $resultData->zuschuss->gesamt);
    static::assertSame(
      round($mittelGesamt + $zuschussGesamt, 2),
      $resultData->zuschuss->finanzierungGesamt
    );

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'recipient_contact_id' => 2,
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-26',
      'amount_requested' => $zuschussGesamt,
    ], $mappedData);
  }

  public function testFachkraefteprogrammPartnerland(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      $possibleRecipients,
      ['_action' => $actionSchema],
      ['required' => ['_action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('_action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('_action'));
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $fahrtstreckeInKm = 555;
    $teilnehmerDeutschlandGesamt = 12;
    $teilnehmerPartnerlandGesamt = 11;
    $teilnehmerGesamt = $teilnehmerDeutschlandGesamt + $teilnehmerPartnerlandGesamt;

    // Kosten
    $unterkunftUndVerpflegung = 11.11;
    $honorarDauer1 = 10;
    $honorarVerguetung1 = 22.22;
    $honorarDauer2 = 11;
    $honorarVerguetung2 = 22.23;
    $honorareGesamt = round($honorarDauer1 * $honorarVerguetung1 + $honorarDauer2 * $honorarVerguetung2, 2);
    $fahrtkostenFlug = 333.33;
    $fahrtkostenAnTeilnehmerErstattet = 555.55;
    $fahrtkostenGesamt = round($fahrtkostenFlug + $fahrtkostenAnTeilnehmerErstattet, 2);
    $programmkosten = 111.11;
    $kostenArbeitsmaterial = 222.22;
    $programmfahrtkosten = 444.44;
    $programmkostenGesamt = round($programmkosten + $kostenArbeitsmaterial + $programmfahrtkosten, 2);
    $sonstigeKosten1 = 12.34;
    $sonstigeKosten2 = 12.35;
    $sonstigeKostenGesamt = round($sonstigeKosten1 + $sonstigeKosten2, 2);
    $sonstigeAusgabe1 = 56.78;
    $sonstigeAusgabe2 = 56.79;
    $sonstigeAusgabenGesamt = round($sonstigeAusgabe1 + $sonstigeAusgabe2, 2);
    // Zuschlagsrelevante Kosten
    $kostenProgrammabsprachen = 11.11;
    $kostenVeroeffentlichungen = 22.22;
    $kostenZuschlagHonorare = 33.33;
    $fahrtkostenUndVerpflegung = 44.44;
    $reisekosten = 55.55;
    $mietkosten = 66.66;
    $zuschlagsrelevanteKostenGesamt = round($kostenProgrammabsprachen + $kostenVeroeffentlichungen
      + $kostenZuschlagHonorare + $fahrtkostenUndVerpflegung + $reisekosten + $mietkosten, 2);

    $kostenGesamt = round($unterkunftUndVerpflegung + $honorareGesamt + $fahrtkostenGesamt
      + $programmkostenGesamt + $sonstigeKostenGesamt + $sonstigeAusgabenGesamt + $zuschlagsrelevanteKostenGesamt, 2);

    // Mittel
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);

    // Zuschuss
    $zuschussTeilnehmerkosten = 0;
    $zuschussHonorarkosten = 0;
    $zuschussFahrtkosten = 34.56;
    $zuschussZuschlag = 56.78;
    $zuschussGesamt = round($zuschussTeilnehmerkosten + $zuschussHonorarkosten
      + $zuschussFahrtkosten + $zuschussZuschlag, 2);

    // Finanzierung muss ausgeglichen sein.
    $eigenmittel = round($kostenGesamt - $zuschussGesamt - $fremdmittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    $data = [
      '_action' => 'submitAction1',
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
          [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-26',
          ],
        ],
        'artDerMassnahme' => 'fachkraefteprogramm',
        'begegnungsland' => 'partnerland',
        'stadt' => 'Stadt',
        'land' => 'Land',
        'fahrtstreckeInKm' => $fahrtstreckeInKm,
      ],
      'teilnehmer' => [
        'deutschland' => [
          'gesamt' => $teilnehmerDeutschlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
        'partnerland' => [
          'gesamt' => $teilnehmerPartnerlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
      ],
      'empfaenger' => 2,
      'partnerorganisation' => [
        'name' => 'abc',
        'adresse' => 'def',
        'land' => 'ghi',
        'email' => 'test@example.org',
        'telefon' => '00123456789',
        'kontaktperson' => 'jkl',
        'fortsetzungsmassnahme' => TRUE,
        'konzeptionellNeu' => FALSE,
        'austauschSeit' => '06.2000',
        'bisherigeBegegnungenInDeutschland' => 'Hier 2001',
        'bisherigeBegegnungenImPartnerland' => 'Dort 2002',
      ],
      'kosten' => [
        'unterkunftUndVerpflegung' => $unterkunftUndVerpflegung,
        'honorare' => [
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer1,
            'verguetung' => $honorarVerguetung1,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          [
            'berechnungsgrundlage' => 'tagessatz',
            'dauer' => $honorarDauer2,
            'verguetung' => $honorarVerguetung2,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'fahrtkosten' => [
          'flug' => $fahrtkostenFlug,
          'anTeilnehmerErstattet' => $fahrtkostenAnTeilnehmerErstattet,
        ],
        'programmkosten' => [
          'programmkosten' => $programmkosten,
          'arbeitsmaterial' => $kostenArbeitsmaterial,
          'fahrt' => $programmfahrtkosten,
        ],
        'sonstigeKosten' => [
          [
            'gegenstand' => 'Gegenstand 1',
            'betrag' => $sonstigeKosten1,
          ],
          [
            'gegenstand' => 'Gegenstand 2',
            'betrag' => $sonstigeKosten2,
          ],
        ],
        'sonstigeAusgaben' => [
          [
            'zweck' => 'Zweck 1',
            'betrag' => $sonstigeAusgabe1,
          ],
          [
            'zweck' => 'Zweck 2',
            'betrag' => $sonstigeAusgabe2,
          ],
        ],
        'zuschlagsrelevanteKosten' => [
          'programmabsprachen' => $kostenProgrammabsprachen,
          'veroeffentlichungen' => $kostenVeroeffentlichungen,
          'honorare' => $kostenZuschlagHonorare,
          'fahrtkostenUndVerpflegung' => $fahrtkostenUndVerpflegung,
          'reisekosten' => $reisekosten,
          'miete' => $mietkosten,
        ],
      ],
      'finanzierung' => [
        'teilnehmerbeitraege' => $teilnehmerBeitrage,
        'eigenmittel' => $eigenmittel,
        'oeffentlicheMittel' => [
          'europa' => $mittelEuropa,
          'bundeslaender' => $mittelBundeslaender,
          'staedteUndKreise' => $mittelStaedteUndKreise,
        ],
        'sonstigeMittel' => [
          [
            'quelle' => 'Quelle 1',
            'betrag' => $sonstigesMittel1,
          ],
          [
            'quelle' => 'Quelle 2',
            'betrag' => $sonstigesMittel2,
          ],
        ],
      ],
      'zuschuss' => [
        'teilnehmerkosten' => $zuschussTeilnehmerkosten,
        'honorarkosten' => $zuschussHonorarkosten,
        'fahrtkosten' => $zuschussFahrtkosten,
        'zuschlag' => $zuschussZuschlag,
      ],
      'beschreibung' => [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 12,
        'inhalt' => 'Inhalt',
        'erlaeuterungen' => 'Erläuterungen',
        'qualifikation' => 'Qualifikation',
      ],
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(18, $result->getCostItemsData());
    static::assertCount(7, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    $programmtage = 3;
    static::assertSame($programmtage, $resultData->grunddaten->programmtage);
    static::assertSame($teilnehmerGesamt, $resultData->teilnehmer->gesamt);
    static::assertSame($programmtage * $teilnehmerGesamt, $resultData->teilnehmer->teilnehmertage);

    static::assertSame($honorareGesamt, $resultData->kosten->honorareGesamt);
    static::assertSame($fahrtkostenGesamt, $resultData->kosten->fahrtkostenGesamt);
    static::assertSame($programmkostenGesamt, $resultData->kosten->programmkostenGesamt);
    static::assertSame($sonstigeKostenGesamt, $resultData->kosten->sonstigeKostenGesamt);
    static::assertSame($sonstigeAusgabenGesamt, $resultData->kosten->sonstigeAusgabenGesamt);
    static::assertSame($zuschlagsrelevanteKostenGesamt, $resultData->kosten->zuschlagsrelevanteKostenGesamt);
    static::assertSame($kostenGesamt, $resultData->kosten->kostenGesamt);

    static::assertSame($oeffentlicheMittelGesamt, $resultData->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $resultData->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $resultData->finanzierung->mittelGesamt);

    static::assertSame(0, $resultData->zuschuss->teilnehmerkostenMax);
    static::assertSame(0, $resultData->zuschuss->honorarkostenMax);
    static::assertSame(
      floor($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.08),
      $resultData->zuschuss->fahrtkostenAuslandEuropaMax
    );
    static::assertSame(
      floor($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.12),
      $resultData->zuschuss->fahrtkostenNichtEuropaMax
    );
    static::assertSame(500, $resultData->zuschuss->zuschlagMax);
    static::assertSame($zuschussGesamt, $resultData->zuschuss->gesamt);
    static::assertSame(
      round($mittelGesamt + $zuschussGesamt, 2),
      $resultData->zuschuss->finanzierungGesamt
    );

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'recipient_contact_id' => 2,
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-26',
      'amount_requested' => $zuschussGesamt,
    ], $mappedData);
  }

  public function testJugendbegegnungDeutschland(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      $possibleRecipients,
      ['_action' => $actionSchema],
      ['required' => ['_action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('_action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('_action'));
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $fahrtstreckeInKm = 100;
    $teilnehmerDeutschlandGesamt = 10;
    $teilnehmerPartnerlandGesamt = 11;
    $teilnehmerGesamt = $teilnehmerDeutschlandGesamt + $teilnehmerPartnerlandGesamt;

    // Kosten
    $unterkunftUndVerpflegung = 11.11;
    $honorarDauer1 = 10;
    $honorarVerguetung1 = 22.22;
    $honorarDauer2 = 11;
    $honorarVerguetung2 = 22.23;
    $honorareGesamt = round($honorarDauer1 * $honorarVerguetung1 + $honorarDauer2 * $honorarVerguetung2, 2);
    $fahrtkostenFlug = 333.33;
    $fahrtkostenAnTeilnehmerErstattet = 555.55;
    $fahrtkostenGesamt = round($fahrtkostenFlug + $fahrtkostenAnTeilnehmerErstattet, 2);
    $programmkosten = 111.11;
    $kostenArbeitsmaterial = 222.22;
    $programmfahrtkosten = 444.44;
    $programmkostenGesamt = round($programmkosten + $kostenArbeitsmaterial + $programmfahrtkosten, 2);
    $sonstigeKosten1 = 12.34;
    $sonstigeKosten2 = 12.35;
    $sonstigeKostenGesamt = round($sonstigeKosten1 + $sonstigeKosten2, 2);
    $sonstigeAusgabe1 = 56.78;
    $sonstigeAusgabe2 = 56.79;
    $sonstigeAusgabenGesamt = round($sonstigeAusgabe1 + $sonstigeAusgabe2, 2);
    // Zuschlagsrelevante Kosten
    $kostenProgrammabsprachen = 11.11;
    $kostenVeroeffentlichungen = 22.22;
    $kostenZuschlagHonorare = 33.33;
    $fahrtkostenUndVerpflegung = 44.44;
    $reisekosten = 55.55;
    $mietkosten = 66.66;
    // Zuschlagsrelevante Kosten gibt es nur für Maßnahmen im Ausland.
    $zuschlagsrelevanteKostenGesamt = 0;

    $kostenGesamt = round($unterkunftUndVerpflegung + $honorareGesamt + $fahrtkostenGesamt
      + $programmkostenGesamt + $sonstigeKostenGesamt + $sonstigeAusgabenGesamt + $zuschlagsrelevanteKostenGesamt, 2);

    // Mittel
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);

    // Zuschuss
    $zuschussTeilnehmerkosten = 12.34;
    $zuschussHonorarkosten = 23.45;
    $zuschussFahrtkosten = 0.0;
    $zuschussZuschlag = 0.0;
    $zuschussGesamt = round($zuschussTeilnehmerkosten + $zuschussHonorarkosten
      + $zuschussFahrtkosten + $zuschussZuschlag, 2);

    // Finanzierung muss ausgeglichen sein.
    $eigenmittel = round($kostenGesamt - $zuschussGesamt - $fremdmittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    $data = [
      '_action' => 'submitAction1',
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
          [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-26',
          ],
        ],
        'artDerMassnahme' => 'jugendbegegnung',
        'begegnungsland' => 'deutschland',
        'stadt' => 'Stadt',
        'land' => 'Land',
        'fahrtstreckeInKm' => $fahrtstreckeInKm,
      ],
      'teilnehmer' => [
        'deutschland' => [
          'gesamt' => $teilnehmerDeutschlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
        'partnerland' => [
          'gesamt' => $teilnehmerPartnerlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
      ],
      'empfaenger' => 2,
      'partnerorganisation' => [
        'name' => 'abc',
        'adresse' => 'def',
        'land' => 'ghi',
        'email' => 'test@example.org',
        'telefon' => '00123456789',
        'kontaktperson' => 'jkl',
        'fortsetzungsmassnahme' => TRUE,
        'konzeptionellNeu' => FALSE,
        'austauschSeit' => '06.2000',
        'bisherigeBegegnungenInDeutschland' => 'Hier 2001',
        'bisherigeBegegnungenImPartnerland' => 'Dort 2002',
      ],
      'kosten' => [
        'unterkunftUndVerpflegung' => $unterkunftUndVerpflegung,
        'honorare' => [
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer1,
            'verguetung' => $honorarVerguetung1,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer2,
            'verguetung' => $honorarVerguetung2,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'fahrtkosten' => [
          'flug' => $fahrtkostenFlug,
          'anTeilnehmerErstattet' => $fahrtkostenAnTeilnehmerErstattet,
        ],
        'programmkosten' => [
          'programmkosten' => $programmkosten,
          'arbeitsmaterial' => $kostenArbeitsmaterial,
          'fahrt' => $programmfahrtkosten,
        ],
        'sonstigeKosten' => [
          [
            'gegenstand' => 'Gegenstand 1',
            'betrag' => $sonstigeKosten1,
          ],
          [
            'gegenstand' => 'Gegenstand 2',
            'betrag' => $sonstigeKosten2,
          ],
        ],
        'sonstigeAusgaben' => [
          [
            'zweck' => 'Zweck 1',
            'betrag' => $sonstigeAusgabe1,
          ],
          [
            'zweck' => 'Zweck 2',
            'betrag' => $sonstigeAusgabe2,
          ],
        ],
        'zuschlagsrelevanteKosten' => [
          'programmabsprachen' => $kostenProgrammabsprachen,
          'veroeffentlichungen' => $kostenVeroeffentlichungen,
          'honorare' => $kostenZuschlagHonorare,
          'fahrtkostenUndVerpflegung' => $fahrtkostenUndVerpflegung,
          'reisekosten' => $reisekosten,
          'miete' => $mietkosten,
        ],
      ],
      'finanzierung' => [
        'teilnehmerbeitraege' => $teilnehmerBeitrage,
        'eigenmittel' => $eigenmittel,
        'oeffentlicheMittel' => [
          'europa' => $mittelEuropa,
          'bundeslaender' => $mittelBundeslaender,
          'staedteUndKreise' => $mittelStaedteUndKreise,
        ],
        'sonstigeMittel' => [
          [
            'quelle' => 'Quelle 1',
            'betrag' => $sonstigesMittel1,
          ],
          [
            'quelle' => 'Quelle 2',
            'betrag' => $sonstigesMittel2,
          ],
        ],
      ],
      'zuschuss' => [
        'teilnehmerkosten' => $zuschussTeilnehmerkosten,
        'honorarkosten' => $zuschussHonorarkosten,
        'fahrtkosten' => $zuschussFahrtkosten,
        'zuschlag' => $zuschussZuschlag,
      ],
      'beschreibung' => [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 12,
        'inhalt' => 'Inhalt',
        'erlaeuterungen' => 'Erläuterungen',
        'qualifikation' => 'Qualifikation',
      ],
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(18, $result->getCostItemsData());
    static::assertCount(7, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    $programmtage = 3;
    static::assertSame($programmtage, $resultData->grunddaten->programmtage);
    static::assertSame($teilnehmerGesamt, $resultData->teilnehmer->gesamt);
    static::assertSame($programmtage * $teilnehmerGesamt, $resultData->teilnehmer->teilnehmertage);

    static::assertSame($honorareGesamt, $resultData->kosten->honorareGesamt);
    static::assertSame($fahrtkostenGesamt, $resultData->kosten->fahrtkostenGesamt);
    static::assertSame($programmkostenGesamt, $resultData->kosten->programmkostenGesamt);
    static::assertSame($sonstigeKostenGesamt, $resultData->kosten->sonstigeKostenGesamt);
    static::assertSame($sonstigeAusgabenGesamt, $resultData->kosten->sonstigeAusgabenGesamt);
    static::assertSame($zuschlagsrelevanteKostenGesamt, $resultData->kosten->zuschlagsrelevanteKostenGesamt);
    static::assertSame($kostenGesamt, $resultData->kosten->kostenGesamt);

    static::assertSame($oeffentlicheMittelGesamt, $resultData->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $resultData->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $resultData->finanzierung->mittelGesamt);

    static::assertSame(
      round($teilnehmerGesamt * $programmtage * 24, 2),
      $resultData->zuschuss->teilnehmerkostenMax,
    );
    static::assertSame(
      round($programmtage * 305, 2),
      $resultData->zuschuss->honorarkostenMax,
    );
    static::assertSame(
      round($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.08, 2),
      $resultData->zuschuss->fahrtkostenAuslandEuropaMax
    );
    static::assertSame(
      round($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.12, 2),
      $resultData->zuschuss->fahrtkostenNichtEuropaMax
    );
    static::assertSame(0, $resultData->zuschuss->zuschlagMax);
    static::assertSame($zuschussGesamt, $resultData->zuschuss->gesamt);
    static::assertSame(
      round($mittelGesamt + $zuschussGesamt, 2),
      $resultData->zuschuss->finanzierungGesamt
    );

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'recipient_contact_id' => 2,
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-26',
      'amount_requested' => $zuschussGesamt,
    ], $mappedData);
  }

  public function testJugendbegegnungPartnerland(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      $possibleRecipients,
      ['_action' => $actionSchema],
      ['required' => ['_action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('_action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('_action'));
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $fahrtstreckeInKm = 555;
    $teilnehmerDeutschlandGesamt = 12;
    $teilnehmerPartnerlandGesamt = 11;
    $teilnehmerGesamt = $teilnehmerDeutschlandGesamt + $teilnehmerPartnerlandGesamt;

    // Kosten
    $unterkunftUndVerpflegung = 11.11;
    $honorarDauer1 = 10;
    $honorarVerguetung1 = 22.22;
    $honorarDauer2 = 11;
    $honorarVerguetung2 = 22.23;
    $honorareGesamt = round($honorarDauer1 * $honorarVerguetung1 + $honorarDauer2 * $honorarVerguetung2, 2);
    $fahrtkostenFlug = 333.33;
    $fahrtkostenAnTeilnehmerErstattet = 555.55;
    $fahrtkostenGesamt = round($fahrtkostenFlug + $fahrtkostenAnTeilnehmerErstattet, 2);
    $programmkosten = 111.11;
    $kostenArbeitsmaterial = 222.22;
    $programmfahrtkosten = 444.44;
    $programmkostenGesamt = round($programmkosten + $kostenArbeitsmaterial + $programmfahrtkosten, 2);
    $sonstigeKosten1 = 12.34;
    $sonstigeKosten2 = 12.35;
    $sonstigeKostenGesamt = round($sonstigeKosten1 + $sonstigeKosten2, 2);
    $sonstigeAusgabe1 = 56.78;
    $sonstigeAusgabe2 = 56.79;
    $sonstigeAusgabenGesamt = round($sonstigeAusgabe1 + $sonstigeAusgabe2, 2);
    // Zuschlagsrelevante Kosten
    $kostenProgrammabsprachen = 11.11;
    $kostenVeroeffentlichungen = 22.22;
    $kostenZuschlagHonorare = 33.33;
    $fahrtkostenUndVerpflegung = 44.44;
    $reisekosten = 55.55;
    $mietkosten = 66.66;
    $zuschlagsrelevanteKostenGesamt = round($kostenProgrammabsprachen + $kostenVeroeffentlichungen
      + $kostenZuschlagHonorare + $fahrtkostenUndVerpflegung + $reisekosten + $mietkosten, 2);

    $kostenGesamt = round($unterkunftUndVerpflegung + $honorareGesamt + $fahrtkostenGesamt
      + $programmkostenGesamt + $sonstigeKostenGesamt + $sonstigeAusgabenGesamt + $zuschlagsrelevanteKostenGesamt, 2);

    // Mittel
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);

    // Zuschuss
    $zuschussTeilnehmerkosten = 0;
    $zuschussHonorarkosten = 0;
    $zuschussFahrtkosten = 34.56;
    $zuschussZuschlag = 56.78;
    $zuschussGesamt = round($zuschussTeilnehmerkosten + $zuschussHonorarkosten
      + $zuschussFahrtkosten + $zuschussZuschlag, 2);

    // Finanzierung muss ausgeglichen sein.
    $eigenmittel = round($kostenGesamt - $zuschussGesamt - $fremdmittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    $data = [
      '_action' => 'submitAction1',
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
          [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-26',
          ],
        ],
        'artDerMassnahme' => 'jugendbegegnung',
        'begegnungsland' => 'partnerland',
        'stadt' => 'Stadt',
        'land' => 'Land',
        'fahrtstreckeInKm' => $fahrtstreckeInKm,
      ],
      'teilnehmer' => [
        'deutschland' => [
          'gesamt' => $teilnehmerDeutschlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
        'partnerland' => [
          'gesamt' => $teilnehmerPartnerlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
      ],
      'empfaenger' => 2,
      'partnerorganisation' => [
        'name' => 'abc',
        'adresse' => 'def',
        'land' => 'ghi',
        'email' => 'test@example.org',
        'telefon' => '00123456789',
        'kontaktperson' => 'jkl',
        'fortsetzungsmassnahme' => TRUE,
        'konzeptionellNeu' => FALSE,
        'austauschSeit' => '06.2000',
        'bisherigeBegegnungenInDeutschland' => 'Hier 2001',
        'bisherigeBegegnungenImPartnerland' => 'Dort 2002',
      ],
      'kosten' => [
        'unterkunftUndVerpflegung' => $unterkunftUndVerpflegung,
        'honorare' => [
          [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer1,
            'verguetung' => $honorarVerguetung1,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          [
            'berechnungsgrundlage' => 'tagessatz',
            'dauer' => $honorarDauer2,
            'verguetung' => $honorarVerguetung2,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'fahrtkosten' => [
          'flug' => $fahrtkostenFlug,
          'anTeilnehmerErstattet' => $fahrtkostenAnTeilnehmerErstattet,
        ],
        'programmkosten' => [
          'programmkosten' => $programmkosten,
          'arbeitsmaterial' => $kostenArbeitsmaterial,
          'fahrt' => $programmfahrtkosten,
        ],
        'sonstigeKosten' => [
          [
            'gegenstand' => 'Gegenstand 1',
            'betrag' => $sonstigeKosten1,
          ],
          [
            'gegenstand' => 'Gegenstand 2',
            'betrag' => $sonstigeKosten2,
          ],
        ],
        'sonstigeAusgaben' => [
          [
            'zweck' => 'Zweck 1',
            'betrag' => $sonstigeAusgabe1,
          ],
          [
            'zweck' => 'Zweck 2',
            'betrag' => $sonstigeAusgabe2,
          ],
        ],
        'zuschlagsrelevanteKosten' => [
          'programmabsprachen' => $kostenProgrammabsprachen,
          'veroeffentlichungen' => $kostenVeroeffentlichungen,
          'honorare' => $kostenZuschlagHonorare,
          'fahrtkostenUndVerpflegung' => $fahrtkostenUndVerpflegung,
          'reisekosten' => $reisekosten,
          'miete' => $mietkosten,
        ],
      ],
      'finanzierung' => [
        'teilnehmerbeitraege' => $teilnehmerBeitrage,
        'eigenmittel' => $eigenmittel,
        'oeffentlicheMittel' => [
          'europa' => $mittelEuropa,
          'bundeslaender' => $mittelBundeslaender,
          'staedteUndKreise' => $mittelStaedteUndKreise,
        ],
        'sonstigeMittel' => [
          [
            'quelle' => 'Quelle 1',
            'betrag' => $sonstigesMittel1,
          ],
          [
            'quelle' => 'Quelle 2',
            'betrag' => $sonstigesMittel2,
          ],
        ],
      ],
      'zuschuss' => [
        'teilnehmerkosten' => $zuschussTeilnehmerkosten,
        'honorarkosten' => $zuschussHonorarkosten,
        'fahrtkosten' => $zuschussFahrtkosten,
        'zuschlag' => $zuschussZuschlag,
      ],
      'beschreibung' => [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 12,
        'inhalt' => 'Inhalt',
        'erlaeuterungen' => 'Erläuterungen',
        'qualifikation' => 'Qualifikation',
      ],
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(18, $result->getCostItemsData());
    static::assertCount(7, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    $programmtage = 3;
    static::assertSame($programmtage, $resultData->grunddaten->programmtage);
    static::assertSame($teilnehmerGesamt, $resultData->teilnehmer->gesamt);
    static::assertSame($programmtage * $teilnehmerGesamt, $resultData->teilnehmer->teilnehmertage);

    static::assertSame($honorareGesamt, $resultData->kosten->honorareGesamt);
    static::assertSame($fahrtkostenGesamt, $resultData->kosten->fahrtkostenGesamt);
    static::assertSame($programmkostenGesamt, $resultData->kosten->programmkostenGesamt);
    static::assertSame($sonstigeKostenGesamt, $resultData->kosten->sonstigeKostenGesamt);
    static::assertSame($sonstigeAusgabenGesamt, $resultData->kosten->sonstigeAusgabenGesamt);
    static::assertSame($zuschlagsrelevanteKostenGesamt, $resultData->kosten->zuschlagsrelevanteKostenGesamt);
    static::assertSame($kostenGesamt, $resultData->kosten->kostenGesamt);

    static::assertSame($oeffentlicheMittelGesamt, $resultData->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $resultData->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $resultData->finanzierung->mittelGesamt);

    static::assertSame(0, $resultData->zuschuss->teilnehmerkostenMax);
    static::assertSame(0, $resultData->zuschuss->honorarkostenMax);
    static::assertSame(
      floor($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.08),
      $resultData->zuschuss->fahrtkostenAuslandEuropaMax
    );
    static::assertSame(
      floor($teilnehmerDeutschlandGesamt * $fahrtstreckeInKm * 0.12),
      $resultData->zuschuss->fahrtkostenNichtEuropaMax
    );
    static::assertSame(300, $resultData->zuschuss->zuschlagMax);
    static::assertSame($zuschussGesamt, $resultData->zuschuss->gesamt);
    static::assertSame(
      round($mittelGesamt + $zuschussGesamt, 2),
      $resultData->zuschuss->finanzierungGesamt
    );

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'recipient_contact_id' => 2,
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-26',
      'amount_requested' => $zuschussGesamt,
    ], $mappedData);
  }

  public function testFinanzierungNichtAusgeglichen(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      $possibleRecipients,
      ['_action' => $actionSchema],
      ['required' => ['_action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('_action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('_action'));
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $fahrtstreckeInKm = 100;
    $teilnehmerDeutschlandGesamt = 10;
    $teilnehmerPartnerlandGesamt = 11;

    // Kosten
    $unterkunftUndVerpflegung = 11.11;
    $honorarDauer1 = 10;
    $honorarVerguetung1 = 22.22;
    $honorarDauer2 = 11;
    $honorarVerguetung2 = 22.23;
    $honorareGesamt = round($honorarDauer1 * $honorarVerguetung1 + $honorarDauer2 * $honorarVerguetung2, 2);
    $fahrtkostenFlug = 333.33;
    $fahrtkostenAnTeilnehmerErstattet = 555.55;
    $fahrtkostenGesamt = round($fahrtkostenFlug + $fahrtkostenAnTeilnehmerErstattet, 2);
    $programmkosten = 111.11;
    $kostenArbeitsmaterial = 222.22;
    $programmfahrtkosten = 444.44;
    $programmkostenGesamt = round($programmkosten + $kostenArbeitsmaterial + $programmfahrtkosten, 2);
    $sonstigeKosten1 = 12.34;
    $sonstigeKosten2 = 12.35;
    $sonstigeKostenGesamt = round($sonstigeKosten1 + $sonstigeKosten2, 2);
    $sonstigeAusgabe1 = 56.78;
    $sonstigeAusgabe2 = 56.79;
    $sonstigeAusgabenGesamt = round($sonstigeAusgabe1 + $sonstigeAusgabe2, 2);
    // Zuschlagsrelevante Kosten
    $kostenProgrammabsprachen = 11.11;
    $kostenVeroeffentlichungen = 22.22;
    $kostenZuschlagHonorare = 33.33;
    $fahrtkostenUndVerpflegung = 44.44;
    $reisekosten = 55.55;
    $mietkosten = 66.66;
    $zuschlagsrelevanteKostenGesamt = round($kostenProgrammabsprachen + $kostenVeroeffentlichungen
      + $kostenZuschlagHonorare + $fahrtkostenUndVerpflegung + $reisekosten + $mietkosten, 2);

    $kostenGesamt = round($unterkunftUndVerpflegung + $honorareGesamt + $fahrtkostenGesamt
      + $programmkostenGesamt + $sonstigeKostenGesamt + $sonstigeAusgabenGesamt + $zuschlagsrelevanteKostenGesamt, 2);

    // Mittel
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);

    // Zuschuss
    $zuschussTeilnehmerkosten = 12.34;
    $zuschussHonorarkosten = 23.45;
    $zuschussFahrtkosten = 0.0;
    $zuschussZuschlag = 0.0;
    $zuschussGesamt = round($zuschussTeilnehmerkosten + $zuschussHonorarkosten
      + $zuschussFahrtkosten + $zuschussZuschlag, 2);

    // Finanzierung ist nicht ausgeglichen.
    $eigenmittel = round($kostenGesamt - $zuschussGesamt - $fremdmittelGesamt - 0.1, 2);

    $data = (object) [
      '_action' => 'submitAction1',
      'grunddaten' => (object) [
        'titel' => 'Test',
        'kurzbeschreibungDesInhalts' => 'foo bar',
        'zeitraeume' => [
          (object) [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
          (object) [
            'beginn' => '2022-08-25',
            'ende' => '2022-08-26',
          ],
        ],
        'artDerMassnahme' => 'fachkraefteprogramm',
        'begegnungsland' => 'deutschland',
        'stadt' => 'Stadt',
        'land' => 'Land',
        'fahrtstreckeInKm' => $fahrtstreckeInKm,
      ],
      'teilnehmer' => (object) [
        'deutschland' => (object) [
          'gesamt' => $teilnehmerDeutschlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
        'partnerland' => (object) [
          'gesamt' => $teilnehmerPartnerlandGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeEhrenamtlichTaetig' => 1,
          'inJugendhilfeHauptamtlichTaetig' => 0,
          'referenten' => 5,
        ],
      ],
      'empfaenger' => 2,
      'partnerorganisation' => (object) [
        'name' => 'abc',
        'adresse' => 'def',
        'land' => 'ghi',
        'email' => 'test@example.org',
        'telefon' => '00123456789',
        'kontaktperson' => 'jkl',
        'fortsetzungsmassnahme' => TRUE,
        'konzeptionellNeu' => FALSE,
        'austauschSeit' => '06.2000',
        'bisherigeBegegnungenInDeutschland' => 'Hier 2001',
        'bisherigeBegegnungenImPartnerland' => 'Dort 2002',
      ],
      'kosten' => (object) [
        'unterkunftUndVerpflegung' => $unterkunftUndVerpflegung,
        'honorare' => [
          (object) [
            'berechnungsgrundlage' => 'stundensatz',
            'dauer' => $honorarDauer1,
            'verguetung' => $honorarVerguetung1,
            'leistung' => 'Leistung 1',
            'qualifikation' => 'Qualifikation 1',
          ],
          (object) [
            'berechnungsgrundlage' => 'tagessatz',
            'dauer' => $honorarDauer2,
            'verguetung' => $honorarVerguetung2,
            'leistung' => 'Leistung 2',
            'qualifikation' => 'Qualifikation 2',
          ],
        ],
        'fahrtkosten' => (object) [
          'flug' => $fahrtkostenFlug,
          'anTeilnehmerErstattet' => $fahrtkostenAnTeilnehmerErstattet,
        ],
        'programmkosten' => (object) [
          'programmkosten' => $programmkosten,
          'arbeitsmaterial' => $kostenArbeitsmaterial,
          'fahrt' => $programmfahrtkosten,
        ],
        'sonstigeKosten' => [
          (object) [
            'gegenstand' => 'Gegenstand 1',
            'betrag' => $sonstigeKosten1,
          ],
          (object) [
            'gegenstand' => 'Gegenstand 2',
            'betrag' => $sonstigeKosten2,
          ],
        ],
        'sonstigeAusgaben' => [
          (object) [
            'zweck' => 'Zweck 1',
            'betrag' => $sonstigeAusgabe1,
          ],
          (object) [
            'zweck' => 'Zweck 2',
            'betrag' => $sonstigeAusgabe2,
          ],
        ],
        'zuschlagsrelevanteKosten' => (object) [
          'programmabsprachen' => $kostenProgrammabsprachen,
          'veroeffentlichungen' => $kostenVeroeffentlichungen,
          'honorare' => $kostenZuschlagHonorare,
          'fahrtkostenUndVerpflegung' => $fahrtkostenUndVerpflegung,
          'reisekosten' => $reisekosten,
          'miete' => $mietkosten,
        ],
      ],
      'finanzierung' => (object) [
        'teilnehmerbeitraege' => $teilnehmerBeitrage,
        'eigenmittel' => $eigenmittel,
        'oeffentlicheMittel' => (object) [
          'europa' => $mittelEuropa,
          'bundeslaender' => $mittelBundeslaender,
          'staedteUndKreise' => $mittelStaedteUndKreise,
        ],
        'sonstigeMittel' => [
          (object) [
            'quelle' => 'Quelle 1',
            'betrag' => $sonstigesMittel1,
          ],
          (object) [
            'quelle' => 'Quelle 2',
            'betrag' => $sonstigesMittel2,
          ],
        ],
      ],
      'zuschuss' => (object) [
        'teilnehmerkosten' => $zuschussTeilnehmerkosten,
        'honorarkosten' => $zuschussHonorarkosten,
        'fahrtkosten' => $zuschussFahrtkosten,
        'zuschlag' => $zuschussZuschlag,
      ],
      'beschreibung' => (object) [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 12,
        'inhalt' => 'Inhalt',
        'erlaeuterungen' => 'Erläuterungen',
        'qualifikation' => 'Qualifikation',
      ],
      'projektunterlagen' => [
        (object) [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $validator = OpisValidatorFactory::getValidator();
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    $errors = $errorCollector->getLeafErrorsAt(['zuschuss', 'finanzierungKostenDifferenz']);
    static::assertCount(1, $errors);
    static::assertSame('Die Finanzierung ist nicht ausgeglichen.', $errors[0]->message());
  }

  public function testNotAllowedDates(): void {
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      []
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
    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      []
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
