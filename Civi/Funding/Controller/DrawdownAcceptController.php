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

use Civi\Api4\FundingDrawdown;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Util\UrlGenerator;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

final class DrawdownAcceptController implements PageControllerInterface {

  private Api4Interface $api4;

  private DrawdownManager $drawdownManager;

  private PayoutProcessManager $payoutProcessManager;

  private UrlGenerator $urlGenerator;

  public function __construct(
    Api4Interface $api4,
    DrawdownManager $drawdownManager,
    PayoutProcessManager $payoutProcessManager,
    UrlGenerator $urlGenerator
  ) {
    $this->api4 = $api4;
    $this->drawdownManager = $drawdownManager;
    $this->payoutProcessManager = $payoutProcessManager;
    $this->urlGenerator = $urlGenerator;
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

    return $this->accept((int) $drawdownId);
  }

  /**
   * @throws \CRM_Core_Exception
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  private function accept(int $drawdownId): Response {
    $drawdown = $this->drawdownManager->get($drawdownId);
    if (NULL === $drawdown) {
      throw new AccessDeniedHttpException();
    }

    $payoutProcess = $this->payoutProcessManager->get($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcess, sprintf('Payout process with ID "%d" not found', $drawdown->getPayoutProcessId()));

    $this->api4->execute(FundingDrawdown::getEntityName(), 'accept', [
      'id' => $drawdownId,
    ]);

    return new RedirectResponse($this->urlGenerator->generate(
      'civicrm/a#/funding/case/' . $payoutProcess->getFundingCaseId()
    ));
  }

}
