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

namespace Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler;

use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplateRenderUriAction;
use Civi\Funding\Util\UrlGenerator;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

/**
 * @codeCoverageIgnore
 */
final class GetTemplateRenderUriActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private UrlGenerator $urlGenerator;

  public function __construct(UrlGenerator $urlGenerator) {
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @phpstan-return array{renderUri: string}
   */
  public function getTemplateRenderUri(GetTemplateRenderUriAction $action): array {
    $renderUri = $this->urlGenerator->generate(
      'civicrm/funding/remote/application/render',
      [
        'applicationProcessId' => (string) $action->getApplicationProcessId(),
        'templateId' => (string) $action->getTemplateId(),
      ]
    );

    return ['renderUri' => $renderUri];
  }

}
