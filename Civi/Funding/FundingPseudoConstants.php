<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingCase;
use Civi\Api4\FundingCaseType;
use Civi\Funding\Event\FundingCase\GetPossibleFundingCaseStatusEvent;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type optionT array{
 *   id: int|string,
 *   name: string,
 *   label: string,
 *   abbr: ?string,
 *   description: ?string,
 *   icon: ?string,
 *   color: ?string,
 * }
 */
final class FundingPseudoConstants {

  /**
   * @phpstan-param array{values: array<int|string, mixed>} $params
   *
   * @phpstan-return list<optionT>
   *
   * @throws \CRM_Core_Exception
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public static function getApplicationProcessStatus(string $fieldName, array $params): array {
  // phpcs:enable
    $fundingCaseTypeName = NULL;
    if ([] !== $params['values']) {
      $values = $params['values'];
      if (is_int($values['fundingCaseTypeId'] ?? NULL)) {
        $fundingCaseTypeName = FundingCaseType::get(FALSE)
          ->addSelect('name')
          ->addWhere('id', '=', $values['fundingCaseTypeId'])
          ->execute()->single()['name'];
      }
      elseif (is_int($values['fundingCaseId'] ?? NULL)) {
        $fundingCaseTypeName = FundingCase::get(FALSE)
          ->addSelect('funding_case_type_id.name')
          ->addWhere('id', '=', $values['fundingCaseId'])
          ->execute()->single()['funding_case_type_id.name'];
      }
      elseif (is_int($values['id'] ?? NULL)) {
        $fundingCaseTypeName = FundingApplicationProcess::get(FALSE)
          ->addSelect('funding_case_id.funding_case_type_id.name')
          ->addWhere('id', '=', $values['id'])
          ->execute()->single()['funding_case_id.funding_case_type_id.name'];
      }
    }

    /** @var \Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface $metaDataProvider */
    $metaDataProvider = \Civi::service(FundingCaseTypeMetaDataProviderInterface::class);
    if (NULL === $fundingCaseTypeName) {
      $statuses = [];
      foreach ($metaDataProvider->getAll() as $metaData) {
        $statuses += $metaData->getApplicationProcessStatuses();
      }
    }
    else {
      $statuses = $metaDataProvider->get($fundingCaseTypeName)->getApplicationProcessStatuses();
    }

    $options = [];
    foreach ($statuses as $status) {
      $options[] = [
        'id' => $status->getName(),
        'name' => $status->getName(),
        'label' => $status->getLabel(),
        'abbr' => NULL,
        'description' => NULL,
        'icon' => $status->getIcon(),
        'color' => $status->getIconColor(),
      ];
    }

    return $options;
  }

  /**
   * @phpstan-return array<string, string>
   */
  public static function getClearingItemStatus(): array {
    return [
      'new' => E::ts('New'),
      'accepted' => E::ts('Accepted'),
      'rejected' => E::ts('Rejected'),
    ];
  }

  /**
   * @phpstan-return list<optionT>
   */
  public static function getClearingProcessStatus(): array {
    return [
      [
        'id' => 'not-started',
        'name' => 'not-started',
        'label' => E::ts('Not started'),
        'icon' => 'fa-minus',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'draft',
        'name' => 'draft',
        'label' => E::ts('Draft'),
        'icon' => 'fa-spinner',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'review-requested',
        'name' => 'review-requested',
        'label' => E::ts('Review requested'),
        'icon' => 'fa-circle-o',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'review',
        'name' => 'review',
        'label' => E::ts('In review'),
        'icon' => 'fa-eye',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'rework',
        'name' => 'rework',
        'label' => E::ts('In rework'),
        'icon' => 'fa-reply',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'rework-review-requested',
        'name' => 'rework-review-requested',
        'label' => E::ts('Rework review requested'),
        'icon' => 'fa-circle-o',
        'color' => NULL,
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'accepted',
        'name' => 'accepted',
        'label' => E::ts('Accepted'),
        'icon' => 'fa-check-circle-o',
        'color' => '#56ab41',
        'abbr' => NULL,
        'description' => NULL,
      ],
      [
        'id' => 'rejected',
        'name' => 'rejected',
        'label' => E::ts('Rejected'),
        'icon' => 'fa-times-circle-o',
        'color' => '#d65050',
        'abbr' => NULL,
        'description' => NULL,
      ],
    ];
  }

  /**
   * @phpstan-return array<string, string>
   */
  public static function getDrawdownStatus(): array {
    return [
      'new' => E::ts('New'),
      'accepted' => E::ts('Accepted'),
    ];
  }

  /**
   * @phpstan-return array<string, string>
   */
  public static function getPayoutProcessStatus(): array {
    return [
      'open' => E::ts('Open'),
      'closed' => E::ts('Closed'),
    ];
  }

  /**
   * @phpstan-return list<optionT>
   */
  public static function getFundingCaseStatus(): array {
    $options = [
      FundingCaseStatus::OPEN => E::ts('Open'),
      FundingCaseStatus::ONGOING => E::ts('Ongoing'),
      FundingCaseStatus::REJECTED => E::ts('Rejected'),
      FundingCaseStatus::WITHDRAWN => E::ts('Withdrawn'),
      FundingCaseStatus::CLEARED => E::ts('Cleared'),
    ];

    $event = new GetPossibleFundingCaseStatusEvent($options);
    \Civi::dispatcher()->dispatch(GetPossibleFundingCaseStatusEvent::class, $event);

    return $event->getOptions();
  }

  /**
   * @return array<string, string>
   */
  public static function getFundingProgramRelationshipTypes(): array {
    return [
      'adoptable' => E::ts('Applications adoptable'),
    ];
  }

  /**
   * @return array<string, string>
   */
  public static function getRelationshipTypeDirections(): array {
    return [
      'a_b' => E::ts('Relationship from a to b'),
      'b_a' => E::ts('Relationship from b to a'),
    ];
  }

}
