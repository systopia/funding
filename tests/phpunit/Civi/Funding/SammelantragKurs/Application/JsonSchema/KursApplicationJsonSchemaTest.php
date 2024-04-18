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

namespace Civi\Funding\SammelantragKurs\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemDataCollector;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemDataCollector;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\SammelantragKurs\Application\JsonSchema\KursApplicationJsonSchema
 */
final class KursApplicationJsonSchemaTest extends TestCase {

  use AssertFormTrait;

  use AssertValidationResultTrait;

  public function testJsonSchema(): void {
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new KursApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      ['action' => $actionSchema],
      ['required' => ['action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('action'));

    $programmtage = 3;
    $teilnehmerGesamt = 5;
    $referenten = 2;

    // Finanzierung
    $eigenmittel = 9.09;
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    // Use a little bit less than the allowed maximum.
    $teilnehmerkosten = $programmtage * $teilnehmerGesamt * 40 - 0.1;
    $fahrtkosten = $teilnehmerGesamt * 60 - 0.1;
    $honorarkosten = $programmtage * $referenten * 305 - 0.1;

    $data = (object) [
      'action' => 'submitAction1',
      'grunddaten' => (object) [
        'titel' => 'Test',
        'kurzbeschreibungDerInhalte' => 'foo bar',
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
        'teilnehmer' => (object) [
          'gesamt' => $teilnehmerGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeTaetig' => 1,
          'referenten' => $referenten,
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
        'teilnehmerkosten' => $teilnehmerkosten,
        'fahrtkosten' => $fahrtkosten,
        'honorarkosten' => $honorarkosten,
      ],
      'beschreibung' => (object) [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 22,
        'veranstaltungsort' => 'Veranstaltungsort',
      ],
    ];

    $validator = OpisApplicationValidatorFactory::getValidator();
    $costItemDataCollector = new CostItemDataCollector();
    $resourcesItemDataCollector = new ResourcesItemDataCollector();
    $result = $validator->validate(
      $data,
      \json_encode($jsonSchema),
      [
        'costItemDataCollector' => $costItemDataCollector,
        'resourcesItemDataCollector' => $resourcesItemDataCollector,
      ]
    );
    static::assertValidationValid($result);
    static::assertCount(3, $costItemDataCollector->getCostItemsData());

    static::assertSame($oeffentlicheMittelGesamt, $data->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $data->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $data->finanzierung->mittelGesamt);
    static::assertCount(7, $resourcesItemDataCollector->getResourcesItemsData());

    $beantragterZuschuss = round($teilnehmerkosten + $fahrtkosten + $honorarkosten, 2);
    static::assertSame($beantragterZuschuss, $data->zuschuss->gesamt);
    static::assertSame($programmtage, $data->grunddaten->programmtage);

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $data);
  }

  public function testJsonSchemaDefaults(): void {
    $actionSchema = new JsonSchemaString();
    $jsonSchema = new KursApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-26'),
      ['action' => $actionSchema],
      ['required' => ['action']],
    );

    $required = $jsonSchema->getKeywordValue('required');
    static::assertIsArray($required);
    static::assertContains('action', $required);
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertSame($actionSchema, $properties->getKeywordValue('action'));

    $programmtage = 3;
    $teilnehmerGesamt = 5;
    $referenten = 2;

    // Finanzierung
    $eigenmittel = 9.09;
    $teilnehmerBeitrage = 10.1;
    $mittelEuropa = 30.3;
    $mittelBundeslaender = 40.4;
    $mittelStaedteUndKreise = 50.5;
    $oeffentlicheMittelGesamt = round($mittelEuropa + $mittelBundeslaender + $mittelStaedteUndKreise, 2);
    $sonstigesMittel1 = 60.6;
    $sonstigesMittel2 = 77.7;
    $sonstigeMittelGesamt = round($sonstigesMittel1 + $sonstigesMittel2, 2);
    $fremdmittelGesamt = round($teilnehmerBeitrage + $oeffentlicheMittelGesamt + $sonstigeMittelGesamt, 2);
    $mittelGesamt = round($eigenmittel + $fremdmittelGesamt, 2);

    $teilnehmerkosten = $programmtage * $teilnehmerGesamt * 40;
    $fahrtkosten = $teilnehmerGesamt * 60;
    $honorarkosten = $programmtage * $referenten * 305;

    $data = (object) [
      'action' => 'submitAction1',
      'grunddaten' => (object) [
        'titel' => 'Test',
        'kurzbeschreibungDerInhalte' => 'foo bar',
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
        'teilnehmer' => (object) [
          'gesamt' => $teilnehmerGesamt,
          'weiblich' => 4,
          'divers' => 3,
          'unter27' => 2,
          'inJugendhilfeTaetig' => 1,
          'referenten' => $referenten,
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
      'zuschuss' => (object) [],
      'beschreibung' => (object) [
        'ziele' => [
          'persoenlichkeitsbildung',
          'internationaleBegegnungen',
        ],
        'bildungsanteil' => 22,
        'veranstaltungsort' => 'Veranstaltungsort',
      ],
    ];

    $validator = OpisApplicationValidatorFactory::getValidator();
    $costItemDataCollector = new CostItemDataCollector();
    $resourcesItemDataCollector = new ResourcesItemDataCollector();
    $result = $validator->validate(
      $data,
      \json_encode($jsonSchema),
      [
        'costItemDataCollector' => $costItemDataCollector,
        'resourcesItemDataCollector' => $resourcesItemDataCollector,
      ]
    );
    static::assertValidationValid($result);
    static::assertCount(3, $costItemDataCollector->getCostItemsData());

    static::assertSame($oeffentlicheMittelGesamt, $data->finanzierung->oeffentlicheMittelGesamt);
    static::assertSame($sonstigeMittelGesamt, $data->finanzierung->sonstigeMittelGesamt);
    static::assertSame($mittelGesamt, $data->finanzierung->mittelGesamt);
    static::assertCount(7, $resourcesItemDataCollector->getResourcesItemsData());

    $beantragterZuschuss = (float) $teilnehmerkosten + $fahrtkosten + $honorarkosten;
    static::assertSame($beantragterZuschuss, $data->zuschuss->gesamt);
    static::assertSame($programmtage, $data->grunddaten->programmtage);

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $data);
  }

  public function testNotAllowedDates(): void {
    $jsonSchema = new KursApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
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
    $jsonSchema = new KursApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
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
