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

use Civi\Funding\Form\FundingFormFile;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationFormFilesFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationFormFilesFactory
 */
final class AVK1ApplicationFilesFactoryTest extends TestCase {

  private AVK1ApplicationFormFilesFactory $filesFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->filesFactory = new AVK1ApplicationFormFilesFactory();
  }

  public function testAddIdentifiers(): void {
    $requestData = [
      'projektunterlagen' => [
        [
          'datei' => 'https://example.org/test1.txt',
          'beschreibung' => 'test1',
        ],
        [
          '_identifier' => 'projektunterlage/test2',
          'datei' => 'https://example.org/test2.txt',
          'beschreibung' => 'test1',
        ],
      ],
    ];

    $result = $this->filesFactory->addIdentifiers($requestData);
    // @phpstan-ignore-next-line
    static::assertNotEmpty($result['projektunterlagen'][0]['_identifier']);
    // @phpstan-ignore-next-line
    static::assertSame('projektunterlage/test2', $result['projektunterlagen'][1]['_identifier']);
  }

  public function testCreateFormFiles(): void {
    $requestData = [
      'projektunterlagen' => [
        [
          '_identifier' => 'projektunterlage/test',
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'test',
        ],
      ],
    ];

    static::assertEquals(
      [
        FundingFormFile::new(
        'https://example.org/test.txt',
        'projektunterlage/test',
          ['beschreibung' => 'test']
        ),
      ],
      $this->filesFactory->createFormFiles($requestData)
    );
  }

  public function testGetSupportedFundingCaseTypes(): void {
    static::assertSame(['AVK1SonstigeAktivitaet'], AVK1ApplicationFormFilesFactory::getSupportedFundingCaseTypes());
  }

}
