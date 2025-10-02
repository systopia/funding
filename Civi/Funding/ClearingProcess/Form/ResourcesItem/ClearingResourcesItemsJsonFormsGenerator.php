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

namespace Civi\Funding\ClearingProcess\Form\ResourcesItem;

use Civi\Core\Format;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\ClearingProcess\Form\AbstractClearingItemsJsonFormsGenerator;
use Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor;
use Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator;
use CRM_Funding_ExtensionUtil as E;

/**
 * @extends AbstractClearingItemsJsonFormsGenerator<\Civi\Funding\Entity\ApplicationResourcesItemEntity
 * >
 */
final class ClearingResourcesItemsJsonFormsGenerator extends AbstractClearingItemsJsonFormsGenerator {

  public function __construct(
    ClearingActionsDeterminer $actionsDeterminer,
    ClearableResourcesItemsLoader $clearableItemsLoader,
    ClearingGroupExtractor $clearingGroupExtractor,
    ClearingResourcesItemManager $clearingResourcesItemManager,
    Format $format,
    ItemDetailsFormElementGenerator $itemDetailsFormElementGenerator
  ) {
    parent::__construct(
      $actionsDeterminer,
      $clearableItemsLoader,
      $clearingGroupExtractor,
      $clearingResourcesItemManager,
      $format,
      $itemDetailsFormElementGenerator
    );
  }

  protected function getPaymentPartyLabel(): string {
    return E::ts('Payer');
  }

  protected function getPropertyKeyword(): string {
    return 'resourcesItems';
  }

  protected function getTitle(): string {
    return E::ts('Resources');
  }

}
