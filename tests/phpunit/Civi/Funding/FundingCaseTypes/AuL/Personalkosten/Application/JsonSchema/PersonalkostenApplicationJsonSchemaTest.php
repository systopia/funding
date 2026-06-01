<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\Util\JsonConverter;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema\PersonalkostenApplicationJsonSchema
 */
final class PersonalkostenApplicationJsonSchemaTest extends TestCase {

  use AssertFormTrait;

  private ApplicationSchemaValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->validator = new ApplicationSchemaValidator(
      new NullTranslator(),
      OpisApplicationValidatorFactory::getValidator()
    );
  }

  public function test(): void {
    $foerderquote = 10;
    $sachkostenpauschale = 123.45;

    $jsonSchema = new PersonalkostenApplicationJsonSchema(
      $foerderquote,
      $sachkostenpauschale,
      new \DateTime('2026-05-04'),
      new \DateTime('2026-05-05'),
      [123 => 'A recipient'],
      []
    );

    $personalkostenBeantragt = 1000.1;
    $beantragterZuschuss = round($foerderquote * $personalkostenBeantragt / 100 + $sachkostenpauschale, 2);

    $data = [
      'empfaenger' => 123,
      'internerBezeichner' => 'interne id',
      'name' => 'Bar',
      'vorname' => 'Foo',
      'tarifUndEingruppierung' => 'abc',
      'beginn' => '2026-05-04',
      'ende' => '2026-05-05',
      'personalkostenTatsaechlich' => 1234.56,
      'personalkostenBeantragt' => 1000.1,
      'sachkostenpauschale' => $sachkostenpauschale,
      'dokumente' => [
        ['datei' => 'https://example.org/file.txt', 'beschreibung' => 'test'],
      ],
    ];

    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(2, $result->getCostItemsData());
    static::assertCount(0, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    static::assertSame('Personalkostenförderung Foo Bar', $resultData->titel);
    static::assertSame('Personalkostenförderung Foo Bar', $resultData->kurzbeschreibung);
    static::assertSame($sachkostenpauschale, $resultData->sachkostenpauschale);
    static::assertSame($foerderquote, $resultData->foerderquote);
    static::assertSame($beantragterZuschuss, $resultData->beantragterZuschuss);

    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'recipient_contact_id' => 123,
      'title' => 'Personalkostenförderung Foo Bar',
      'short_description' => 'Personalkostenförderung Foo Bar',
      'funding_application_process_extra.internal_identifier' => 'interne id',
      'start_date' => '2026-05-04',
      'end_date' => '2026-05-05',
      'amount_requested' => $beantragterZuschuss,
    ], $mappedData);
  }

  public function testLimitedValidation(): void {
    $foerderquote = 10;
    $sachkostenpauschale = 123.45;

    $jsonSchema = new PersonalkostenApplicationJsonSchema(
      $foerderquote,
      $sachkostenpauschale,
      new \DateTime('2026-05-04'),
      new \DateTime('2026-05-05'),
      [123 => 'A recipient'],
      ['limited_validation_acton']
    );

    $data = [
      '_action' => 'limited_validation_acton',
    ];
    $result = $this->validator->validate($jsonSchema, $data, 10);
    static::assertSame(
      'The required properties (name, vorname, empfaenger) are missing',
      $result->getLeafErrorMessages()['/'][0]
    );

    $data = [
      'name' => 'Bar',
      'vorname' => 'Foo',
      'empfaenger' => 123,
      '_action' => 'limited_validation_acton',
    ];
    $result = $this->validator->validate($jsonSchema, $data);
    static::assertCount(0, $result->getCostItemsData());
    static::assertCount(0, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    static::assertSame('Personalkostenförderung Foo Bar', $resultData->titel);
    static::assertSame('Personalkostenförderung Foo Bar', $resultData->kurzbeschreibung);
    static::assertSame($sachkostenpauschale, $resultData->beantragterZuschuss);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'recipient_contact_id' => 123,
      'title' => 'Personalkostenförderung Foo Bar',
      'short_description' => 'Personalkostenförderung Foo Bar',
      'amount_requested' => $sachkostenpauschale,
    ], $mappedData);
  }

  public function testNotAllowedDates(): void {
    $jsonSchema = new PersonalkostenApplicationJsonSchema(
      10,
      100.1,
      new \DateTime('2026-05-04'),
      new \DateTime('2026-05-05'),
      [],
      []
    );

    $data = (object) [
      'beginn' => '2026-05-03',
      'ende' => '2026-05-06',
    ];

    $validator = OpisValidatorFactory::getValidator();
    $validator->setMaxErrors(20);
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    $beginnErrors = $errorCollector->getErrorsAt('/beginn');
    static::assertCount(1, $beginnErrors);
    static::assertSame('minDate', $beginnErrors[0]->keyword());
    $endeErrors = $errorCollector->getErrorsAt('/ende');
    static::assertCount(1, $endeErrors);
    static::assertSame('maxDate', $endeErrors[0]->keyword());
  }

  public function testEndeBeforeBeginn(): void {
    $jsonSchema = new PersonalkostenApplicationJsonSchema(
      10,
      100.1,
      new \DateTime('2026-05-04'),
      new \DateTime('2026-05-05'),
      [],
      []
    );

    $data = (object) [
      'beginn' => '2026-05-05',
      'ende' => '2026-05-04',
    ];

    $validator = OpisValidatorFactory::getValidator();
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    static::assertFalse($errorCollector->hasErrorAt('/beginn'));
    $endeErrors = $errorCollector->getErrorsAt('/ende');
    static::assertCount(1, $endeErrors);
    static::assertSame('minDate', $endeErrors[0]->keyword());
  }

}
