<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'requires' => [
    'crmFunding',
  ],
  'title' => E::ts('Drawdowns'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/drawdown/list',
  'permission' => [
    'access Funding',
    'administer Funding',
  ],
  'permission_operator' => 'OR',
  'search_displays' => [
    'FundingDrawdownsAll.Table',
  ],
];
