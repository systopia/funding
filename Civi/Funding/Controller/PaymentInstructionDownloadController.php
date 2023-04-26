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

use Civi\Funding\FileTypeIds;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PaymentInstructionDownloadController implements PageControllerInterface {

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
    if (NULL === $this->drawdownManager->get($drawdownId)) {
      throw new AccessDeniedHttpException();
    }

    $attachment = $this->attachmentManager->getLastByFileType(
      'civicrm_funding_drawdown',
      $drawdownId,
      FileTypeIds::PAYMENT_INSTRUCTION,
    );

    if (NULL === $attachment) {
      throw new NotFoundHttpException('Payment instruction does not exist');
    }

    $headers = [
      'Content-Type' => $attachment->getMimeType(),
    ];
    $filename = E::ts('payment-instruction') . '.' . pathinfo($attachment->getPath(), PATHINFO_EXTENSION);

    return (new BinaryFileResponse(
      $attachment->getPath(),
      Response::HTTP_OK,
      $headers,
      FALSE,
    ))->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
  }

}
