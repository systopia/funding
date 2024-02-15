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

namespace Civi\Funding\SonstigeAktivitaet\Data;

use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FormDataFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FormDataFactory
 */
final class AVK1FormDataFactoryTest extends TestCase {

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FinanzierungFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $finanzierungFactoryMock;

  private AVK1FormDataFactory $formDataFactory;

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $projektunterlagenFactoryMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->finanzierungFactoryMock = $this->createMock(AVK1FinanzierungFactory::class);
    $this->projektunterlagenFactoryMock = $this->createMock(AVK1ProjektunterlagenFactory::class);
    $this->formDataFactory = new AVK1FormDataFactory(
      $this->finanzierungFactoryMock,
      $this->projektunterlagenFactoryMock,
    );
  }

  public function testCreateFormData(): void {
    $fundingCase = FundingCaseFactory::createFundingCase();
    $startDate = date('Y-m-d', time() - 86400);
    $endDate = date('Y-m-d', time());
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'start_date' => $startDate,
      'end_date' => $endDate,
      'request_data' => [
        'grunddaten' => [
          'zeitraeume' => [
            ['beginn' => $startDate, 'ende' => $endDate],
          ],
        ],
        'teilnehmer' => ['gesamt' => 100],
        'beschreibung' => ['veranstaltungsort' => 'dort'],
      ],
    ]);

    $this->finanzierungFactoryMock->method('createFinanzierung')->with($applicationProcess)
      ->willReturn(['bar' => 1.23]);
    $this->projektunterlagenFactoryMock->method('createProjektunterlagen')->with($applicationProcess)
      ->willReturn(['baz' => 'abc']);

    $data = $this->formDataFactory->createFormData($applicationProcess, $fundingCase);
    static::assertEquals([
      'grunddaten' => [
        'titel' => $applicationProcess->getTitle(),
        'kurzbeschreibungDesInhalts' => $applicationProcess->getShortDescription(),
        'zeitraeume' => [
          ['beginn' => $startDate, 'ende' => $endDate],
        ],
      ],
      'empfaenger' => $fundingCase->getRecipientContactId(),
      'finanzierung' => ['bar' => 1.23],
      'teilnehmer' => ['gesamt' => 100],
      'beschreibung' => ['veranstaltungsort' => 'dort'],
      'projektunterlagen' => ['baz' => 'abc'],
    ], $data);
  }

}
