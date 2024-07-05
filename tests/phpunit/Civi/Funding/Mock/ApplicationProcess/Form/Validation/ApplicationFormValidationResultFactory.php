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

namespace Civi\Funding\Mock\ApplicationProcess\Form\Validation;

use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

final class ApplicationFormValidationResultFactory {

  public const ACTION = ValidatedApplicationDataMock::ACTION;

  public const TITLE = ValidatedApplicationDataMock::TITLE;

  public const SHORT_DESCRIPTION = ValidatedApplicationDataMock::SHORT_DESCRIPTION;

  public const RECIPIENT_CONTACT_ID = ValidatedApplicationDataMock::RECIPIENT_CONTACT_ID;

  public const START_DATE = ValidatedApplicationDataMock::START_DATE;

  public const END_DATE = ValidatedApplicationDataMock::END_DATE;

  public const AMOUNT_REQUESTED = ValidatedApplicationDataMock::AMOUNT_REQUESTED;

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<string, mixed> $formData
   * @phpstan-param array<string, mixed> $mappedData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   * phpcs:enable
   */
  public static function createValid(
    array $formData = [],
    array $mappedData = [],
    array $costItemsData = [],
    array $resourcesItemsData = [],
    bool $readOnly = FALSE
  ): ApplicationFormValidationResult {
    $formData = $formData + [
      '_action' => self::ACTION,
    ];

    $mappedData += [
      'title' => self::TITLE,
      'short_description' => self::SHORT_DESCRIPTION,
      'recipient_contact_id' => self::RECIPIENT_CONTACT_ID,
      'start_date' => self::START_DATE,
      'end_date' => self::END_DATE,
      'amount_requested' => self::AMOUNT_REQUESTED,
    ];

    $validationResult = new ApplicationFormValidationResult(
      [],
      $formData,
      $costItemsData,
      $resourcesItemsData,
      new TaggedDataContainer(),
      $readOnly
    );

    foreach ($mappedData as $fieldName => $value) {
      $validationResult->setMappedValue($fieldName, $value);
    }

    return $validationResult;
  }

  /**
   * @phpstan-param array<string, non-empty-list<string>> $errorMessages
   * @phpstan-param array<string, mixed> $formData
   */
  public static function createInvalid(array $errorMessages, array $formData = []): ApplicationFormValidationResult {
    $formData = $formData + [
      '_action' => self::ACTION,
    ];

    return new ApplicationFormValidationResult(
      $errorMessages,
      $formData,
      [],
      [],
      new TaggedDataContainer(),
      TRUE
    );
  }

}
