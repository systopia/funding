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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\Form\JsonFormsForm;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class ReportForm extends JsonFormsForm implements ReportFormInterface {

  private ?JsonFormsElement $receiptsAppendUiSchema;

  private ?JsonFormsElement $receiptsPrependUiSchema;

  private ?JsonFormsElement $postReceiptsUiSchema;

  /**
   * @return static
   */
  public static function newEmpty(): JsonFormsForm {
    return new self(new JsonSchema([]), new JsonFormsGroup('', []));
  }

  public function __construct(
    JsonSchema $jsonSchema,
    JsonFormsElement $uiSchema,
    JsonFormsElement $receiptsPrependUiSchema = NULL,
    JsonFormsElement $receiptsAppendUiSchema = NULL,
    JsonFormsElement $postReceiptsUiSchema = NULL
  ) {
    parent::__construct($jsonSchema, $uiSchema);
    $this->receiptsPrependUiSchema = $receiptsPrependUiSchema;
    $this->receiptsAppendUiSchema = $receiptsAppendUiSchema;
    $this->postReceiptsUiSchema = $postReceiptsUiSchema;
  }

  /**
   * @inheritDoc
   */
  public function getReceiptsAppendUiSchema(): ?JsonFormsElement {
    return $this->receiptsAppendUiSchema;
  }

  /**
   * @inheritDoc
   */
  public function getReceiptsPrependUiSchema(): ?JsonFormsElement {
    return $this->receiptsPrependUiSchema;
  }

  /**
   * @inheritDoc
   */
  public function getPostReceiptsUiSchema(): ?JsonFormsElement {
    return $this->postReceiptsUiSchema;
  }

}
