<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'requires' => [
    'crmFunding',
  ],
  'title' => E::ts('Funding Amount Approved Change Requests'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/amount-approved-change-request/list',
  'permission' => [
    'access Funding',
    'administer Funding',
  ],
  'permission_operator' => 'OR',
  'search_displays' => [
    'funding_amount_approved_change_requests.table',
  ],
];
