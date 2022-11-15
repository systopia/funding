<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\Form\SonstigeAktivitaet\AVK1FormDataFactory
 */
final class AVK1FormDataFactoryTest extends TestCase {

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $finanzierungFactoryMock;

  private AVK1FormDataFactory $formDataFactory;

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $kostenFactoryMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->finanzierungFactoryMock = $this->createMock(AVK1FinanzierungFactory::class);
    $this->kostenFactoryMock = $this->createMock(AVK1KostenFactory::class);
    $this->formDataFactory = new AVK1FormDataFactory(
      $this->finanzierungFactoryMock,
      $this->kostenFactoryMock
    );
  }

  public function testCreateFormData(): void {
    $fundingCase = FundingCaseFactory::createFundingCase();
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'start_date' => date('Y-m-d', time() - 86400),
      'end_date' => date('Y-m-d', time()),
    ]);

    $this->kostenFactoryMock->method('createKosten')->with($applicationProcess)
      ->willReturn(['foo' => 12.3]);
    $this->finanzierungFactoryMock->method('createFinanzierung')->with($applicationProcess)
      ->willReturn(['bar' => 1.23]);

    $data = $this->formDataFactory->createFormData($applicationProcess, $fundingCase);
    static::assertEquals([
      'titel' => $applicationProcess->getTitle(),
      'kurzbezeichnungDesInhalts' => $applicationProcess->getShortDescription(),
      'empfaenger' => $fundingCase->getRecipientContactId(),
      'beginn' => date('Y-m-d', time() - 86400),
      'ende' => date('Y-m-d', time()),
      'kosten' => ['foo' => 12.3],
      'finanzierung' => ['bar' => 1.23],
    ], $data);
  }

}
