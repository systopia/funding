<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingCaseTypeProgram.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:fb7fbff1db8b55572a4132ab808b2c28)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the FundingCaseTypeProgram entity.
 */
class CRM_Funding_DAO_FundingCaseTypeProgram extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_case_type_program';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingCaseTypeProgram ID
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
   * FK to FundingCaseType
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $funding_case_type_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_case_type_program';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Funding Case Type Programs') : E::ts('Funding Case Type Program');
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
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'funding_case_type_id', 'civicrm_funding_case_type', 'id');
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
          'description' => E::ts('Unique FundingCaseTypeProgram ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case_type_program.id',
          'table_name' => 'civicrm_funding_case_type_program',
          'entity' => 'FundingCaseTypeProgram',
          'bao' => 'CRM_Funding_DAO_FundingCaseTypeProgram',
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
          'title' => E::ts('Funding Program'),
          'description' => E::ts('FK to FundingProgram'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case_type_program.funding_program_id',
          'table_name' => 'civicrm_funding_case_type_program',
          'entity' => 'FundingCaseTypeProgram',
          'bao' => 'CRM_Funding_DAO_FundingCaseTypeProgram',
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
        'funding_case_type_id' => [
          'name' => 'funding_case_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Funding Case Type'),
          'description' => E::ts('FK to FundingCaseType'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case_type_program.funding_case_type_id',
          'table_name' => 'civicrm_funding_case_type_program',
          'entity' => 'FundingCaseTypeProgram',
          'bao' => 'CRM_Funding_DAO_FundingCaseTypeProgram',
          'localizable' => 0,
          'FKClassName' => 'CRM_Funding_DAO_FundingCaseType',
          'html' => [
            'type' => 'EntityRef',
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_funding_case_type',
            'keyColumn' => 'id',
            'labelColumn' => 'title',
            'prefetch' => 'false',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_case_type_program', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_case_type_program', $prefix, []);
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
