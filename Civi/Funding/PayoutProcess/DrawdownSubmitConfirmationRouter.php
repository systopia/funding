<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\PayoutProcess;

use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\Util\UrlGenerator;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

/**
 * @codeCoverageIgnore
 */
final class DrawdownSubmitConfirmationRouter {

  private FundingAttachmentManagerInterface $attachmentManager;

  private RequestContextInterface $requestContext;

  private UrlGenerator $urlGenerator;

  public function __construct(
    FundingAttachmentManagerInterface $attachmentManager,
    RequestContextInterface $requestContext,
    UrlGenerator $urlGenerator
  ) {
    $this->attachmentManager = $attachmentManager;
    $this->requestContext = $requestContext;
    $this->urlGenerator = $urlGenerator;
  }

  public function generate(int $drawdownId): ?string {
    if (!$this->attachmentManager->has(
      'civicrm_funding_drawdown',
      $drawdownId,
      FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION
    )) {
      return NULL;
    }

    if ($this->requestContext->isRemote()) {
      $path = 'civicrm/funding/remote/drawdown-submit-confirmation/download';
    }
    else {
      $path = 'civicrm/funding/drawdown-submit-confirmation/download';
    }

    return $this->urlGenerator->generate($path, ['drawdownId' => (string) $drawdownId]);
  }

}
