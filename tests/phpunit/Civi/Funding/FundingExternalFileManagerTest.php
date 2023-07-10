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

namespace Civi\Funding;

use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\FundingExternalFileManager
 *
 * @group headless
 */
final class FundingExternalFileManagerTest extends AbstractFundingHeadlessTestCase {

  /**
   * @var \Civi\Funding\FundingExternalFileManager
   */
  private FundingExternalFileManager $externalFileManager;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManager = new FundingExternalFileManager(new Api4());
  }

  public function testAddFile(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $externalFile1 = $this->externalFileManager->addFile(
      'https://example.org/test1.txt',
      '00112233-4455-6677-8899-aabbccddeeff',
      'civicrm_funding_program',
      $fundingProgram->getId(),
    );

    static::assertGreaterThan(0, $externalFile1->getId());
    static::assertSame('https://example.org/test1.txt', $externalFile1->getSource());
    static::assertSame('00112233-4455-6677-8899-aabbccddeeff', $externalFile1->getIdentifier());
    static::assertStringStartsWith('http://', $externalFile1->getUri());

    static::assertEquals(
      $externalFile1,
      $this->externalFileManager->getFile(
        '00112233-4455-6677-8899-aabbccddeeff',
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    static::assertNull(
      $this->externalFileManager->getFile(
        '99112233-4455-6677-8899-aabbccddeeff',
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    static::assertNull(
      $this->externalFileManager->getFile(
        '00112233-4455-6677-8899-aabbccddeeff',
        'civicrm_funding_program',
        $fundingProgram->getId() + 1
      )
    );

    static::assertEquals(
      [$externalFile1],
      $this->externalFileManager->getFiles(
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    $this->externalFileManager->updateCustomData($externalFile1, ['foo' => 'bar']);
    static::assertSame(['foo' => 'bar'], $externalFile1->getCustomData());
    static::assertEquals(
      $externalFile1,
      $this->externalFileManager->getFile(
        '00112233-4455-6677-8899-aabbccddeeff',
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    $externalFile2 = $this->externalFileManager->addFile(
      'https://example.org/test2.txt',
      '10112233-4455-6677-8899-aabbccddeeff',
      'civicrm_funding_program',
      $fundingProgram->getId(),
    );
    static::assertEquals(
      [$externalFile1, $externalFile2],
      $this->externalFileManager->getFiles(
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    $this->externalFileManager->deleteFile($externalFile1);
    static::assertNull(
      $this->externalFileManager->getFile(
        '00112233-4455-6677-8899-aabbccddeeff',
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    $this->externalFileManager->deleteFiles(
      'civicrm_funding_program',
      $fundingProgram->getId(),
      ['10112233-4455-6677-8899-aabbccddeeff'],
    );
    static::assertEquals(
      [$externalFile2],
      $this->externalFileManager->getFiles(
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );

    $this->externalFileManager->deleteFiles(
      'civicrm_funding_program',
      $fundingProgram->getId(),
      ['99112233-4455-6677-8899-aabbccddeeff'],
    );
    static::assertEquals(
      [],
      $this->externalFileManager->getFiles(
        'civicrm_funding_program',
        $fundingProgram->getId()
      )
    );
  }

}
