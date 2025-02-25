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

namespace Civi\Funding\Controller;

use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use CRM_Funding_ExtensionUtil as E;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tomsgu\PdfMerger\PdfCollection;
use Tomsgu\PdfMerger\PdfFile;
use Tomsgu\PdfMerger\PdfMerger;

final class DrawdownDocumentDownloadController implements PageControllerInterface {

  private FundingAttachmentManagerInterface $attachmentManager;

  private DrawdownManager $drawdownManager;

  public function __construct(FundingAttachmentManagerInterface $attachmentManager, DrawdownManager $drawdownManager) {
    $this->attachmentManager = $attachmentManager;
    $this->drawdownManager = $drawdownManager;
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function handle(Request $request): Response {
    if ($request->query->has('drawdownIds')) {
      $drawdownIds = (string) $request->query->get('drawdownIds');

      if (preg_match('/^[1-9][0-9]*(,[1-9][0-9]*)*$/', $drawdownIds) !== 1) {
        throw new BadRequestHttpException('Invalid drawdown IDs');
      }

      $drawdownIds = array_map(fn (string $id) => (int) $id, explode(',', $drawdownIds));
      /** @phpstan-var list<int> $drawdownIds */

      return $this->downloadMultiple($drawdownIds);
    }

    $drawdownId = $request->query->get('drawdownId');

    if (!is_numeric($drawdownId)) {
      throw new BadRequestHttpException('Invalid drawdown ID');
    }

    return $this->download((int) $drawdownId);
  }

  /**
   * @throws \CRM_Core_Exception
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  private function download(int $drawdownId): Response {
    $drawdown = $this->drawdownManager->get($drawdownId);
    if (NULL === $drawdown) {
      throw new AccessDeniedHttpException();
    }

    $attachment = $this->attachmentManager->getLastByFileType(
      'civicrm_funding_drawdown',
      $drawdownId,
      $drawdown->getAmount() < 0 ? FileTypeNames::PAYBACK_CLAIM : FileTypeNames::PAYMENT_INSTRUCTION,
    );

    if (NULL === $attachment) {
      throw new NotFoundHttpException("Drawdown document (ID: $drawdownId) does not exist");
    }

    $headers = [
      'Content-Type' => $attachment->getMimeType(),
    ];
    $filename = $drawdown->getAmount() < 0 ? E::ts('payback-claim') : E::ts('payment-instruction');
    $filename .= '.' . pathinfo($attachment->getPath(), PATHINFO_EXTENSION);

    return (new BinaryFileResponse(
      $attachment->getPath(),
      Response::HTTP_OK,
      $headers,
      FALSE,
    ))->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename)
      ->setMaxAge(300);
  }

  /**
   * @phpstan-param list<int> $drawdownIds
   *
   * @throws \CRM_Core_Exception
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  private function downloadMultiple(array $drawdownIds): Response {
    $pdfCollection = new PdfCollection();
    foreach ($drawdownIds as $drawdownId) {
      $drawdown = $this->drawdownManager->get($drawdownId);
      if (NULL === $drawdown) {
        throw new AccessDeniedHttpException();
      }

      $attachment = $this->attachmentManager->getLastByFileType(
        'civicrm_funding_drawdown',
        $drawdownId,
        $drawdown->getAmount() < 0 ? FileTypeNames::PAYBACK_CLAIM : FileTypeNames::PAYMENT_INSTRUCTION,
      );

      if (NULL === $attachment) {
        throw new NotFoundHttpException("Drawdown document (ID: $drawdownId) does not exist");
      }

      $pdfCollection->addPdf($attachment->getPath());
    }

    $filename = E::ts('payment-instructions') . '.pdf';
    $headers = [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename),
    ];

    $merger = new PdfMerger(new Fpdi());
    return (new Response(
      $merger->merge($pdfCollection, $filename, PdfMerger::MODE_STRING, PdfFile::ORIENTATION_AUTO_DETECT),
      200,
      $headers
    ))->setMaxAge(300);
  }

}
