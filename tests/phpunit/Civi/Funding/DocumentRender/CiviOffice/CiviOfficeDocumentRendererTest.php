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

namespace Civi\Funding\DocumentRender\CiviOffice;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\RequestTestUtil;
use Civi\RemoteTools\Api3\Api3;
use CRM_Funding_ExtensionUtil as E;

/**
 * @covers \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeDocumentRenderer
 *
 * @group headless
 */
final class CiviOfficeDocumentRendererTest extends AbstractFundingHeadlessTestCase {

  private CiviOfficeDocumentRenderer $documentRenderer;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
  }

  protected function setUp(): void {
    if (!UnoconvLocalTestConfigurator::isAvailable()) {
      static::markTestSkipped(<<<EOT
unoconv not found. Provide unoconv in PATH or set the path to the executable in the environment variable "UNOCONV".
EOT
      );
    }
    parent::setUp();
    /** @var \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder $contextDataHolder */
    $contextDataHolder = \Civi::service(CiviOfficeContextDataHolder::class);
    $this->documentRenderer = new CiviOfficeDocumentRenderer(
      new Api3(), $contextDataHolder,
    );
  }

  public function testGetMimeType(): void {
    static::assertSame('application/pdf', $this->documentRenderer->getMimeType());
  }

  public function testRender(): void {
    $creationContact = ContactFixture::addIndividual();
    $recipientContact = ContactFixture::addOrganization(['display_name' => 'Some Org']);
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    FundingCaseContactRelationFixture::addContact($creationContact['id'], $fundingCase->getId(), ['view']);

    UnoconvLocalTestConfigurator::configure();

    RequestTestUtil::mockInternalRequest($creationContact['id']);
    $filename = $this->documentRenderer->render(
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      'TransferContract',
      $fundingCase->getId(),
      [
        'fundingCase' => $fundingCase,
        'eligibleApplicationProcesses' => [$applicationProcess],
        'fundingCaseType' => $fundingCaseType,
        'fundingProgram' => $fundingProgram,
      ],
    );
    static::assertFileExists($filename);
    static::assertSame('application/pdf', mime_content_type($filename));
    unlink($filename);
  }

}
