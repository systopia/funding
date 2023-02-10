<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingNewCasePermissions.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:b21786300a141481b362042cbfc2a6b1)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the FundingNewCasePermissions entity.
 */
class CRM_Funding_DAO_FundingNewCasePermissions extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_new_case_permissions';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingNewCasePermissions ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to FundingProgram
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $funding_program_id;

  /**
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $type;

  /**
   * @var string
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $properties;

  /**
   * Permissions as JSON array
   *
   * @var string
   *   (SQL type: varchar(512))
   *   Note that values will be retrieved from the database as a string.
   */
  public $permissions;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_new_case_permissions';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Funding New Case Permissionses') : E::ts('Funding New Case Permissions');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'funding_program_id', 'civicrm_funding_program', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique FundingNewCasePermissions ID'),
          'required' => TRUE,
          'where' => 'civicrm_funding_new_case_permissions.id',
          'table_name' => 'civicrm_funding_new_case_permissions',
          'entity' => 'FundingNewCasePermissions',
          'bao' => 'CRM_Funding_DAO_FundingNewCasePermissions',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'funding_program_id' => [
          'name' => 'funding_program_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Funding Program ID'),
          'description' => E::ts('FK to FundingProgram'),
          'required' => TRUE,
          'where' => 'civicrm_funding_new_case_permissions.funding_program_id',
          'table_name' => 'civicrm_funding_new_case_permissions',
          'entity' => 'FundingNewCasePermissions',
          'bao' => 'CRM_Funding_DAO_FundingNewCasePermissions',
          'localizable' => 0,
          'FKClassName' => 'CRM_Funding_DAO_FundingProgram',
          'html' => [
            'type' => 'EntityRef',
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_funding_program',
            'keyColumn' => 'id',
            'labelColumn' => 'title',
            'prefetch' => 'false',
          ],
          'add' => NULL,
        ],
        'type' => [
          'name' => 'type',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Type'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_funding_new_case_permissions.type',
          'table_name' => 'civicrm_funding_new_case_permissions',
          'entity' => 'FundingNewCasePermissions',
          'bao' => 'CRM_Funding_DAO_FundingNewCasePermissions',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'properties' => [
          'name' => 'properties',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Properties'),
          'required' => TRUE,
          'where' => 'civicrm_funding_new_case_permissions.properties',
          'table_name' => 'civicrm_funding_new_case_permissions',
          'entity' => 'FundingNewCasePermissions',
          'bao' => 'CRM_Funding_DAO_FundingNewCasePermissions',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_JSON,
          'add' => NULL,
        ],
        'permissions' => [
          'name' => 'permissions',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Permissions'),
          'description' => E::ts('Permissions as JSON array'),
          'required' => TRUE,
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_funding_new_case_permissions.permissions',
          'table_name' => 'civicrm_funding_new_case_permissions',
          'entity' => 'FundingNewCasePermissions',
          'bao' => 'CRM_Funding_DAO_FundingNewCasePermissions',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_JSON,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_new_case_permissions', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_new_case_permissions', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}