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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Data;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory
 */
final class AVK1ProjektunterlagenFactoryTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private AVK1ProjektunterlagenFactory $projektunterlagenFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->projektunterlagenFactory = new AVK1ProjektunterlagenFactory($this->externalFileManagerMock);
  }

  public function testCreateProjektunterlagen(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $identifier1 = 'FundingApplicationProcess.' . $applicationProcess->getId() . ':projektunterlage/123';
    $identifier2 = 'FundingApplicationProcess.' . $applicationProcess->getId() . ':foo';
    $this->externalFileManagerMock->method('getFiles')->with($applicationProcess->getId())
      ->willReturn([
        'projektunterlage/123' => ExternalFileFactory::create([
          'identifier' => $identifier1,
          'custom_data' => ['beschreibung' => 'test'],
          'uri' => 'https://example.net/test.txt',
        ]),
        'foo' => ExternalFileFactory::create(['identifier' => $identifier2]),
      ]);

    static::assertEquals(
      [
        [
          '_identifier' => 'projektunterlage/123',
          'datei' => 'https://example.net/test.txt',
          'beschreibung' => 'test',
        ],
      ],
      $this->projektunterlagenFactory->createProjektunterlagen($applicationProcess)
    );
  }

}
