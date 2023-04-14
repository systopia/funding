<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'PayoutProcess',
    'class' => 'CRM_Funding_DAO_PayoutProcess',
    'table' => 'civicrm_payout_process',
  ],
];
