<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\Generic\FinancePlanItem;

use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractGetFieldsAction extends DAOGetFieldsAction {

  private ?FundingCaseTypeMetaDataProviderInterface $metaDataProvider = NULL;

  public function __construct(string $entityName, ?FundingCaseTypeMetaDataProviderInterface $metaDataProvider = NULL) {
    parent::__construct($entityName, 'getFields');
    $this->metaDataProvider = $metaDataProvider;
  }

  /**
   * @phpstan-return list<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), [
      [
        'name' => 'type_label',
        'title' => E::ts('Type Label'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer('type'),
        'output_formatters' => [
          function (string &$value, array $row, array $field): void {
            if (!isset($row['funding_case_type'])) {
              throw new \RuntimeException(sprintf(
                'The field "type_label" of entity "%s" depends on the field "funding_case_type"',
                $this->getEntityName()
              ));
            }

            $value = $this->getTypeLabel($this->getMetaDataProvider()->get($row['funding_case_type']), $value);
          },
        ],
      ],
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer(
          'application_process_id.funding_case_id.funding_program_id.currency'
        ),
      ],
      [
        'name' => 'funding_case_type',
        'title' => E::ts('Funding Case Type'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer(
          'application_process_id.funding_case_id.funding_case_type_id.name'
        ),
      ],
    ]);
  }

  abstract protected function getTypeLabel(FundingCaseTypeMetaDataInterface $metaData, string $type): string;

  private function getMetaDataProvider(): FundingCaseTypeMetaDataProviderInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->metaDataProvider ??= \Civi::service(FundingCaseTypeMetaDataProviderInterface::class);
  }

}
