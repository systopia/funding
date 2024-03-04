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

namespace Civi\Funding\Controller;

use Civi\Api4\FundingApplicationCiviOfficeTemplate;
use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRenderer;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Util\TestFileUtil;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Civi\Funding\Controller\ApplicationTemplateRenderController
 *
 * @phpstan-import-type applicationCiviOfficeTemplateT from \Civi\Api4\FundingApplicationCiviOfficeTemplate
 */
final class ApplicationTemplateRenderControllerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private ApplicationTemplateRenderController $controller;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRenderer&\PHPUnit\Framework\MockObject\MockObject
   */
  private $rendererMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->rendererMock = $this->createMock(CiviOfficeRenderer::class);
    $this->controller = new ApplicationTemplateRenderController(
      $this->api4Mock,
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      $this->rendererMock
    );
  }

  public function test(): void {
    $request = new Request(['applicationProcessId' => 2, 'templateId' => 3]);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['id' => 2]);
    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn($applicationProcess);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->api4Mock->method('getEntity')
      ->with(FundingApplicationCiviOfficeTemplate::getEntityName(), 3)
      ->willReturn($this->createTemplate($fundingCase->getFundingCaseTypeId()));

    $tempDir = TestFileUtil::createTempDir(basename(__FILE__, '.php'));
    $resultFile = $tempDir . '/result.txt';
    file_put_contents($resultFile, 'test');

    $this->rendererMock->method('render')
      ->with('local::abc', FundingApplicationProcess::getEntityName(), 2)
      ->willReturn($resultFile);

    $this->rendererMock->method('getMimeType')
      ->willReturn('text/plain');

    $response = $this->controller->handle($request);
    static::assertInstanceOf(BinaryFileResponse::class, $response);
    /** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $response */
    static::assertSame($resultFile, $response->getFile()->getRealPath());
    static::assertSame(
      HeaderUtils::makeDisposition('inline', 'Test.txt'),
      $response->headers->get('Content-Disposition')
    );
    static::assertSame('text/plain', $response->headers->get('Content-Type'));
  }

  public function testApplicationProcessNotFound(): void {
    $request = new Request(['applicationProcessId' => 2, 'templateId' => 3]);

    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Application process not found');
    $this->controller->handle($request);
  }

  public function testTemplateNotFound(): void {
    $request = new Request(['applicationProcessId' => 2, 'templateId' => 3]);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['id' => 2]);
    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn($applicationProcess);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->api4Mock->method('getEntity')
      ->with(FundingApplicationCiviOfficeTemplate::getEntityName(), 3)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Template not found');
    $this->controller->handle($request);
  }

  public function testFundingCaseTypeIdMismatch(): void {
    $request = new Request(['applicationProcessId' => 2, 'templateId' => 3]);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['id' => 2]);
    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn($applicationProcess);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->api4Mock->method('getEntity')
      ->with(FundingApplicationCiviOfficeTemplate::getEntityName(), 3)
      ->willReturn($this->createTemplate($fundingCase->getFundingCaseTypeId() + 1));

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Funding case type of application process and template do not match');
    $this->controller->handle($request);
  }

  /**
   * @phpstan-return applicationCiviOfficeTemplateT
   */
  private function createTemplate(int $fundingCaseTypeId): array {
    return [
      'id' => 3,
      'case_type_id' => $fundingCaseTypeId,
      'document_uri' => 'local::abc',
      'label' => 'Test',
    ];
  }

}
