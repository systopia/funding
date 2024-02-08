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

use CRM_Funding_ExtensionUtil as E;

// phpcs:disable Generic.Files.LineLength.TooLong
return [
  [
    'name' => 'SavedSearch_FundingTasksMy',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_tasks_my',
        'label' => E::ts('My Funding Tasks'),
        'form_values' => NULL,
        'mapping_id' => NULL,
        'search_custom_id' => NULL,
        'api_entity' => 'Activity',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'subject',
            'Activity_EntityActivity_FundingApplicationProcess_01.title',
            'Activity_EntityActivity_FundingApplicationProcess_01.id',
            'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
            'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.modification_date',
          ],
          'orderBy' => [],
          'where' => [
            [
              'activity_type_id:name',
              'IN',
              [
                'funding_application_task_internal',
              ],
            ],
            [
              'status_id:name',
              'NOT IN',
              [
                'Completed',
                'Cancelled',
                'Not Required',
              ],
            ],
          ],
          'groupBy' => [],
          'join' => [
            [
              'Contact AS Activity_ActivityContact_Contact_01',
              'INNER',
              'ActivityContact',
              [
                'id',
                '=',
                'Activity_ActivityContact_Contact_01.activity_id',
              ],
              [
                'Activity_ActivityContact_Contact_01.record_type_id:name',
                '=',
                '"Activity Assignees"',
              ],
              [
                'Activity_ActivityContact_Contact_01.id',
                '=',
                '"user_contact_id"',
              ],
            ],
            [
              'FundingApplicationProcess AS Activity_EntityActivity_FundingApplicationProcess_01',
              'INNER',
              'EntityActivity',
              [
                'id',
                '=',
                'Activity_EntityActivity_FundingApplicationProcess_01.activity_id',
              ],
            ],
            [
              'FundingCase AS Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01',
              'INNER',
              [
                'Activity_EntityActivity_FundingApplicationProcess_01.funding_case_id',
                '=',
                'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.id',
              ],
            ],
          ],
          'having' => [],
        ],
        'expires_date' => NULL,
        'description' => E::ts('Funding tasks of logged in user'),
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_FundingTasksMy.table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_tasks_my',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [
            [
              'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.modification_date',
              'DESC',
            ],
          ],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'subject',
              'dataType' => 'String',
              'label' => E::ts('Subject'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'Activity_EntityActivity_FundingApplicationProcess_01.title',
              'dataType' => 'String',
              'label' => E::ts('Application'),
              'sortable' => TRUE,
              'link' => [
                'path' => 'civicrm/a#/funding/application/[Activity_EntityActivity_FundingApplicationProcess_01.id]',
                'entity' => '',
                'action' => '',
                'join' => '',
                'target' => '',
              ],
              'title' => E::ts('Open application'),
            ],
            [
              'type' => 'field',
              'key' => 'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.modification_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modification Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'Activity_EntityActivity_FundingApplicationProcess_01_FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'table',
            'table-striped',
          ],
          'headerCount' => TRUE,
        ],
        'acl_bypass' => FALSE,
      ],
    ],
  ],
];
