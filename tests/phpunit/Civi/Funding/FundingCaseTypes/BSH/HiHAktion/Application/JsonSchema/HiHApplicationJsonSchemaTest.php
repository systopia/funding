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

    $possibleRecipients = [
      2 => 'Organization 2',
    ];

    $this->jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-07-08'),
      new \DateTime('2024-07-09'),
      $possibleRecipients,
      TRUE
    );

    $this->validData = [
      'fragenZumProjekt' => [
        'name' => 'Test',
        'ansprechpartner' => [
          'anrede' => 'Frau',
          'titel' => 'Dr.',
          'vorname' => 'Erika',
          'nachname' => 'Mustermann',
          'telefonnummer' => '0123456789',
          'email' => 'mustermann@example.org',
        ],
        'adresseNichtIdentischMitOrganisation' => TRUE,
        'abweichendeAnschrift' => [
          'projekttraeger' => 'Projektträger',
          'strasse' => 'Musterstr. 11',
          'plz' => '47110',
          'ort' => 'Musterort',
        ],
      ],
      'informationenZumProjekt' => [
        'kurzbeschreibung' => 'Kurzbeschreibung',
        'wirktGegenEinsamkeit' => 'Wirkt',
        'ziel' => 'Ziel',
        'status' => 'laeuftSchon',
        'statusBeginn' => '2024-07-01',
        'foerderungAb' => '2024-07-08',
        'foerderungBis' => '2024-07-09',
        'beabsichtigteTeilnehmendenzahl' => 123,
        'zielgruppe' => [
          'kinder',
          'jugendliche',
        ],
        'zielgruppeErreichen' => 'Zielgruppe erreichen',
        'zielgruppeHerausforderungen' => ['fluchterfahrung'],
        'zielgruppeHerausforderungenSonstige' => 'Sonstige Herausforderung',
        'zielgruppeHerausforderungenErlaeuterung' => 'Erläuterung Herausforderungen',
        'projektformat' => ['regelmaessigeGruppe'],
        'projektformatSonstiges' => 'Sonstiges Projektformat',
        'projektformatErlaeuterung' => 'Erläuterung Projektformat',
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
        'personalkostenKeine' => FALSE,
        'personalkosten' => [
          [
            'posten' => 'Personalkosten 1',
            'wochenstunden' => 10,
            'bruttoMonatlich' => 1000.1,
            'anzahlMonate' => 2,
          ],
          [
            'posten' => 'Personalkosten 2',
            'wochenstunden' => 20,
            'bruttoMonatlich' => 2000.2,
            'anzahlMonate' => 3,
          ],
        ],
        'honorareKeine' => FALSE,
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
        'sachkostenKeine' => FALSE,
        'sachkosten' => [
          'materialien' => 1.1,
          'ehrenamtspauschalen' => 2.2,
          'verpflegung' => 3.3,
          'fahrtkosten' => 4.4,
          'oeffentlichkeitsarbeit' => 5.5,
          'investitionen' => 6.6,
          'mieten' => 7.7,
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
        'personalkostenKommentar' => 'PersonalkostenKommentar',
        'honorareKommentar' => 'HonorareKommentar',
        'sachkostenKommentar' => 'SachkostenKommentar',
        'personalkostenBewilligt' => 11.11,
        'honorareBewilligt' => 22.22,
        'sachkostenBewilligt' => 33.33,
      ],
      'finanzierung' => [
        'grundsaetzlich' => 'Finanzierung grundsätzlich',
        'gesamtesProjektHiH' => TRUE,
        'wichtigstePositionenBeiTeilbetrag' => 'Position A',
        'andereKosten' => '',
        'finanzierungZusaetzlicheKosten' => '',
      ],
      'formales' => [
        'antidiskriminierung' => TRUE,
        'kinderschutz' => TRUE,
        'datenschutz' => TRUE,
      ],
    ];
  }

  public function test(): void {
    $possibleRecipients = [
      2 => 'Organization 2',
    ];

    $properties = $this->jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertEquals(new JsonSchemaRecipient($possibleRecipients), $properties->getKeywordValue('empfaenger'));

    $result = $this->validator->validate($this->jsonSchema, $this->validData);
    static::assertSame([], $result->getLeafErrorMessages());
    static::assertCount(16, $result->getCostItemsData());

    $resultData = JsonConverter::toStdClass($result->getData());
    static::assertSame(8000.8, $resultData->kosten->personalkostenSumme);
    static::assertSame(355.53, $resultData->kosten->honorareSumme);
    static::assertSame(12.1, $resultData->kosten->sachkosten->sonstigeSumme);
    static::assertSame(42.9, $resultData->kosten->sachkosten->summe);
    static::assertSame(8000.8 + 355.53 + 42.9, $resultData->kosten->gesamtkosten);

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());
    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'Kurzbeschreibung',
      'recipient_contact_id' => 2,
      'start_date' => '2024-07-08',
      'end_date' => '2024-07-09',
      'amount_requested' => 8000.8 + 355.53 + 42.9,
      '_zielgruppe' => [
        'kinder',
        'jugendliche',
      ],
      '_zielgruppeHerausforderungen' => ['fluchterfahrung'],
      'bsh_funding_application_extra.amount_approved_personalkosten' => 11.11,
      'bsh_funding_application_extra.amount_approved_honorare' => 22.22,
      'bsh_funding_application_extra.amount_approved_sachkosten' => 33.33,
    ], $mappedData);
  }

  public function testAbweichendeAdresseEmpty(): void {
    $data = $this->validData;
    $data['fragenZumProjekt']['adresseNichtIdentischMitOrganisation'] = TRUE;
    $data['fragenZumProjekt']['abweichendeAnschrift']['strasse'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['plz'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['ort'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['telefonnummer'] = '';
    $data['fragenZumProjekt']['abweichendeAnschrift']['email'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 10);
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

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/statusBeginn' => ['Dieser Wert ist erforderlich.'],
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

  public function testZielgruppeHerausforderungenSonstige(): void {
    $data = $this->validData;
    $data['informationenZumProjekt']['zielgruppeHerausforderungen'] = ['sonstige'];
    $data['informationenZumProjekt']['zielgruppeHerausforderungenSonstige'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/informationenZumProjekt/zielgruppeHerausforderungenSonstige' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testFinanzierungGesamtesProjektHiHFalse(): void {
    $data = $this->validData;
    $data['finanzierung']['gesamtesProjektHiH'] = FALSE;
    $data['finanzierung']['andereKosten'] = '';
    $data['finanzierung']['finanzierungZusaetzlicheKosten'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 3);
    static::assertEquals([
      '/finanzierung/andereKosten' => ['Dieser Wert ist erforderlich.'],
      '/finanzierung/finanzierungZusaetzlicheKosten' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testPersonalkostenKommentarRequired(): void {
    $data = $this->validData;
    $data['kosten']['personalkostenKommentar'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/kosten/personalkostenKommentar' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());

    $data['kosten']['personalkosten'] = [];
    $result = $this->validator->validate($this->jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
  }

  public function testHonorarkostenKommentarRequired(): void {
    $data = $this->validData;
    $data['kosten']['honorareKommentar'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/kosten/honorareKommentar' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());

    $data['kosten']['honorare'] = [];
    $result = $this->validator->validate($this->jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
  }

  public function testSachkostenKommentarRequired(): void {
    $data = $this->validData;
    $data['kosten']['sachkostenKommentar'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/kosten/sachkostenKommentar' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());

    $data['kosten']['sachkosten'] = [
      'materialien' => 0,
      'ehrenamtspauschalen' => 0,
      'verpflegung' => 0,
      'fahrtkosten' => 0,
      'oeffentlichkeitsarbeit' => 0,
      'investitionen' => 0,
      'mieten' => 0,
      'sonstige' => [],
    ];
    $result = $this->validator->validate($this->jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());
  }

  public function testAnsprechpartnerAnredeEmpty(): void {
    $data = $this->validData;
    $data['fragenZumProjekt']['ansprechpartner']['anrede'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/fragenZumProjekt/ansprechpartner/anrede' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testAnsprechpartnerVornameEmpty(): void {
    $data = $this->validData;
    $data['fragenZumProjekt']['ansprechpartner']['vorname'] = '';

    $result = $this->validator->validate($this->jsonSchema, $data, 2);
    static::assertEquals([
      '/fragenZumProjekt/ansprechpartner/vorname' => ['Dieser Wert ist erforderlich.'],
    ], $result->getLeafErrorMessages());
  }

  public function testNotAllowedDates(): void {
    $jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-07-08'),
      new \DateTime('2024-07-09'),
      [2 => 'Organization 2'],
      FALSE
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
      [2 => 'Organization 2'],
      TRUE
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
