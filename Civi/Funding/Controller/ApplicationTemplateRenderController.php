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
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type applicationCiviOfficeTemplateT from \Civi\Api4\FundingApplicationCiviOfficeTemplate
 */
final class ApplicationTemplateRenderController implements PageControllerInterface {

  private Api4Interface $api4;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private CiviOfficeRenderer $renderer;

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    CiviOfficeRenderer $renderer
  ) {
    $this->api4 = $api4;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->renderer = $renderer;
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function handle(Request $request): Response {
    $applicationProcessId = $request->query->get('applicationProcessId');
    if (!is_numeric($applicationProcessId)) {
      throw new BadRequestHttpException('Invalid application process ID');
    }

    $templateId = $request->query->get('templateId');
    if (!is_numeric($templateId)) {
      throw new BadRequestHttpException('Invalid template ID');
    }

    return $this->render((int) $applicationProcessId, (int) $templateId);
  }

  /**
   * @throws \CRM_Core_Exception
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  private function render(int $applicationProcessId, int $templateId): Response {
    $applicationProcess = $this->applicationProcessManager->get($applicationProcessId);
    if (NULL === $applicationProcess) {
      throw new NotFoundHttpException('Application process not found');
    }

    $fundingCase = $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
    Assert::notNull($fundingCase);

    /** @phpstan-var applicationCiviOfficeTemplateT|null $template */
    $template = $this->api4->getEntity(FundingApplicationCiviOfficeTemplate::getEntityName(), $templateId);
    if (NULL === $template) {
      throw new NotFoundHttpException('Template not found');
    }

    if ($fundingCase->getFundingCaseTypeId() !== $template['case_type_id']) {
      throw new BadRequestHttpException('Funding case type of application process and template do not match');
    }

    $renderedFile = $this->renderer->render(
      $template['document_uri'],
      FundingApplicationProcess::getEntityName(),
      $applicationProcess->getId()
    );

    $headers = [
      'Content-Type' => $this->renderer->getMimeType(),
    ];
    $filename = $template['label'] . '.' . pathinfo($renderedFile, PATHINFO_EXTENSION);

    return (new BinaryFileResponse(
      $renderedFile,
      Response::HTTP_OK,
      $headers,
      FALSE,
    ))->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename)
      ->deleteFileAfterSend();
  }

}
