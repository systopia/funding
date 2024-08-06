<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\Util\JsonConverter;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema\HiHApplicationJsonSchema
 */
final class HiHApplicationJsonSchemaTest extends TestCase {

  use AssertFormTrait;

  private HiHApplicationJsonSchema $jsonSchema;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $validData;

  private ApplicationSchemaValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->validator = new ApplicationSchemaValidator(
      new NullTranslator(),
      OpisApplicationValidatorFactory::getValidator()
    );
  }

  public function test(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];

    $this->jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-07-08'),
      new \DateTime('2024-07-09'),
      $possibleRecipients
    );

    $personalkostenSumme = 8000.8;


    $this->validData = [
      'fragenZumProjekt' => [
        'name' => 'Test',
        'ansprechpartner' => [
          'anrede' => 'Frau',
          'titel' => 'Dr.',
          'vorname' => 'Erika',
          'nachname' => 'Mustermann',
        ],
        'adresseIdentischMitOrganisation' => FALSE,
        'abweichendeAnschrift' => [
          'strasse' => 'Musterstr. 11',
          'plz' => '47110',
          'ort' => 'Musterort',
        ],
        'telefonnummer' => '0123456789',
        'email' => 'mustermann@example.org',
      ],
      'informationenZumProjekt' => [
        'kurzbeschreibung' => 'Kurzbeschreibung',
        'wirktGegenEinsamkeit' => 'Wirkt',
        'kern' => 'Kern',
        'status' => 'laeuftSchon',
        'statusBeginn' => '2024-07-01',
        'foerderungAb' => '2024-07-08',
        'foerderungBis' => '2024-07-09',
        'haeufigkeit' => 'Jährlich',
        'beabsichtigteTeilnehmendenzahl' => 123,
        'zielgruppe' => [
          'kinder',
          'jugendliche',
        ],
        'zielgruppeSonstiges' => 'Sonstige Zielgruppe',
        'zielgruppeErreichen' => 'Zielgruppe erreichen',
        'projektformat' => [
          'regelmaessigeGruppe',
        ],
        'projektformatSonstiges' => 'Sonstiges Projektformat',
        'dateien' => [
          [
            'datei' => 'https://example.org/test.txt',
            'beschreibung' => 'Test',
          ],
        ],
        'sonstiges' => 'Sonstiges',
      ],
      'empfaenger' => 2,
      'kosten' => [
        'personalkosten' => [
          [
            'posten' => 'Personalkosten 1',
            'bruttoMonatlich' => 1000.1,
            'anzahlMonate' => 2,
          ],
          [
            'posten' => 'Personalkosten 2',
            'bruttoMonatlich' => 2000.2,
            'anzahlMonate' => 3,
          ],
        ],
        'honorare' => [
          [
            'posten' => 'Honorar 1',
            'berechnungsgrundlage' => 'stundensatz',
            'verguetung' => 11.1,
            'dauer' => 2,
          ],
          [
            'posten' => 'Honorar 2',
            'berechnungsgrundlage' => 'tagessatz',
            'verguetung' => 111.11,
            'dauer' => 3,
          ],
        ],
        'sachkosten' => [
          'materialien' => 1.1,
          'ehrenamtspauschalen' => 2.2,
          'verpflegung' => 3.3,
          'fahrtkosten' => 4.4,
          'oeffentlichkeitsarbeit' => 5.5,
          'investitionen' => 6.6,
          'mieten' => 7.7,
          'verwaltungskosten' => [
            [
              'bezeichnung' => 'Verwaltungskosten 1',
              'summe' => 1.1,
            ],
            [
              'bezeichnung' => 'Verwaltungskosten 2',
              'summe' => 8.8,
            ],
          ],
          'sonstige' => [
            [
              'bezeichnung' => 'Sonstige 1',
              'summe' => 2.2,
            ],
            [
              'bezeichnung' => 'Sonstige 2',
              'summe' => 9.9,
            ],
          ],
        ],
      ],
      'einnahmen' => [
        'antragssumme' => 8000.8 + 355.53 + 52.8 - 100.1 - 200.2,
        'andereFoerdermittel' => 100.1,
        'eigenmittel' => 200.2,
      ],
      'rechtliches' => [
        'kinderschutzklausel' => TRUE,
        'datenschutz' => TRUE,
      ],
    ];
  }

  public function test(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];

    $properties = $this->jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $result = $this->validator->validate($this->jsonSchema, $this->validData);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(15, $result->getCostItemsData());
    static::assertCount(3, $result->getResourcesItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    static::assertSame(8000.8, $resultData->kosten->personalkostenSumme);
    static::assertSame(355.53, $resultData->kosten->honorareSumme);
    static::assertSame(9.9, $resultData->kosten->sachkosten->verwaltungskostenSumme);
    static::assertSame(12.1, $resultData->kosten->sachkosten->sonstigeSumme);
    static::assertSame(52.8, $resultData->kosten->sachkosten->summe);
    static::assertSame(8000.8 + 355.53 + 52.8, $resultData->kosten->gesamtkosten);

    static::assertSame(8000.8 + 355.53 + 52.8, $resultData->einnahmen->gesamteinnahmen);
    static::assertSame(0.0, $resultData->einnahmen->einnahmenKostenDifferenz);

    $resultData->informationenZumProjekt->statusSonstiges = 'Status Sonstiges';
    static::assertAllPropertiesSet($this->jsonSchema->toStdClass(), $resultData);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'Kurzbeschreibung',
      'recipient_contact_id' => 2,
      'start_date' => '2024-07-08',
      'end_date' => '2024-07-09',
      'amount_requested' => 0.0,
    ], $mappedData);
  }

  public function testNichtAusgeglichen(): void {
    $data = $this->validData;
    $data['kosten']['sachkosten']['materialien'] += 0.1;

    $result = $this->validator->validate($this->jsonSchema, $data, 4);
    static::assertEquals([
      '/einnahmen/einnahmenKostenDifferenz' => ['Die Finanzierung ist nicht ausgeglichen.'],
    ], $result->getLeafErrorMessages());

    static::assertSame(-0.1, $result->getData()['einnahmen']['einnahmenKostenDifferenz']);
  }

  public function testAbweichendeAdresseEmpty(): void {
    $data = $this->validData;
    $data['fragenZumProjekt']['adresseIdentischMitOrganisation'] = FALSE;
    $data['fragenZumProjekt']['abweichendeAnschrift']['strasse'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['plz'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['ort'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 4);
    static::assertEquals([
      '/fragenZumProjekt/abweichendeAnschrift/strasse' => ['Dieser Wert ist erforderlich.'],
      '/fragenZumProjekt/abweichendeAnschrift/plz' => ['Dieser Wert ist erforderlich.'],
      '/fragenZumProjekt/abweichendeAnschrift/ort' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testStatusLaeuftSchon(): void {
    $data = $this->validData;
    $data['informationenZumProjekt']['status'] = 'laeuftSchon';
    $data['informationenZumProjekt']['statusBeginn'] = NULL;
    $data['informationenZumProjekt']['statusSonstiges'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/statusBeginn' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testStatusSonstiges(): void {
    $data = $this->validData;
    $data['informationenZumProjekt']['status'] = 'sonstiges';
    $data['informationenZumProjekt']['statusBeginn'] = NULL;
    $data['informationenZumProjekt']['statusSonstiges'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/statusSonstiges' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testZielgruppeSonstiges(): void {
    $data = $this->validData;
    $data['informationenZumProjekt']['zielgruppe'] = ['sonstiges'];
    $data['informationenZumProjekt']['zielgruppeSonstiges'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/zielgruppeSonstiges' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testProjektformatSonstiges(): void {
    $data = $this->validData;
    $data['informationenZumProjekt']['projektformat'] = ['sonstiges'];
    $data['informationenZumProjekt']['projektformatSonstiges'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/projektformatSonstiges' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testNotAllowedDates(): void {
    $jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-07-08'),
      new \DateTime('2024-07-09'),
      []
    );

    $data = (object) [
      'informationenZumProjekt' => (object) [
        'foerderungAb' => '2024-07-07',
        'foerderungBis' => '2024-07-10',
      ],
    ];

    $validator = OpisValidatorFactory::getValidator();
    $validator->setMaxErrors(20);
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    $foerderungAbErrors = $errorCollector->getErrorsAt('/informationenZumProjekt/foerderungAb');
    static::assertCount(1, $foerderungAbErrors);
    static::assertSame('minDate', $foerderungAbErrors[0]->keyword());
    $foerderungBisErrors = $errorCollector->getErrorsAt('/informationenZumProjekt/foerderungBis');
    static::assertCount(1, $foerderungBisErrors);
    static::assertSame('maxDate', $foerderungBisErrors[0]->keyword());
  }

  public function testEndeBeforeBeginn(): void {
    $jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-07-08'),
      new \DateTime('2024-07-09'),
      []
    );

    $data = (object) [
      'informationenZumProjekt' => (object) [
        'foerderungAb' => '2024-07-09',
        'foerderungBis' => '2024-07-08',
      ],
    ];

    $validator = OpisValidatorFactory::getValidator();
    $errorCollector = new ErrorCollector();
    $validator->validate($data, \json_encode($jsonSchema), ['errorCollector' => $errorCollector]);

    static::assertFalse($errorCollector->hasErrorAt('/informationenZumProjekt/foerderungAb'));
    $foerderungBisErrors = $errorCollector->getErrorsAt('/informationenZumProjekt/foerderungBis');
    static::assertCount(1, $foerderungBisErrors);
    static::assertSame('minDate', $foerderungBisErrors[0]->keyword());
  }

}
