<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Drawdown Controlling'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/drawdown/controlling',
  'permission' => [
    'access Funding',
    'administer Funding',
  ],
  'permission_operator' => 'OR',
  'search_displays' => [
    'Drawdowns_by_Recipient_and_Funding_Program.Table',
  ],
];
