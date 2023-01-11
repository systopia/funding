<?php
// phpcs:disable
use CRM_Funding_ExtensionUtil as E;
// phpcs:enable

class CRM_Funding_BAO_FundingNewCasePermissions extends CRM_Funding_DAO_FundingNewCasePermissions {

  /**
   * Create a new FundingNewCasePermissions based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Funding_DAO_FundingNewCasePermissions|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Funding_DAO_FundingNewCasePermissions';
    $entityName = 'FundingNewCasePermissions';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  */

}
