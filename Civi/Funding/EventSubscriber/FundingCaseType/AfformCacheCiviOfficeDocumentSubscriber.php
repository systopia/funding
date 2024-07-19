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

namespace Civi\Funding\EventSubscriber\FundingCaseType;

use Civi\Core\AssetBuilder;
use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AfformCacheCiviOfficeDocumentSubscriber implements EventSubscriberInterface {

  private AssetBuilder $assetBuilder;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_civicrm_getAssetUrl' => 'onGetAssetUrl',
    ];
  }

  public function __construct(AssetBuilder $assetBuilder) {
    $this->assetBuilder = $assetBuilder;
  }

  public function onGetAssetUrl(GenericHookEvent $event): void {
    if ($this->assetBuilder->isCacheEnabled()
      && $this->doesAssetContainAvailableCiviOfficeDocuments($event->asset, $event->params)
    ) {
      // Asset contains the available CiviOffice documents to add to a funding
      // case type as application template. The assets are cached, but the
      // available documents might have changed meanwhile. Therefore, a
      // (re-)built has to be forced for the available options in the select
      // field to be up-to-date.
      $this->assetBuilder->build($event->asset, $event->params, TRUE);
    }
  }

  /**
   * @phpstan-param array{modules: string} $params
   */
  private function doesAssetContainAvailableCiviOfficeDocuments(string $assetName, array $params): bool {
    return 'angular-modules.json' === $assetName && str_contains($params['modules'], 'afformApplicationTemplates');
  }

}
