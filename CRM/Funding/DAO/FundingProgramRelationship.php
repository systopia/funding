<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingProgramRelationship.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:01dd5e43916be2f4cbc06cb7072c91dd)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the FundingProgramRelationship entity.
 */
class CRM_Funding_DAO_FundingProgramRelationship extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_program_relationship';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingProgramRelationship ID
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
  public $id_a;

  /**
   * FK to FundingProgram
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id_b;

  /**
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $type;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_program_relationship';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Funding Program Relationships') : E::ts('Funding Program Relationship');
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
          'description' => E::ts('Unique FundingProgramRelationship ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_program_relationship.id',
          'table_name' => 'civicrm_funding_program_relationship',
          'entity' => 'FundingProgramRelationship',
          'bao' => 'CRM_Funding_DAO_FundingProgramRelationship',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'id_a' => [
          'name' => 'id_a',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID A'),
          'description' => E::ts('FK to FundingProgram'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_program_relationship.id_a',
          'table_name' => 'civicrm_funding_program_relationship',
          'entity' => 'FundingProgramRelationship',
          'bao' => 'CRM_Funding_DAO_FundingProgramRelationship',
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
        'id_b' => [
          'name' => 'id_b',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID B'),
          'description' => E::ts('FK to FundingProgram'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_program_relationship.id_b',
          'table_name' => 'civicrm_funding_program_relationship',
          'entity' => 'FundingProgramRelationship',
          'bao' => 'CRM_Funding_DAO_FundingProgramRelationship',
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
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_program_relationship.type',
          'table_name' => 'civicrm_funding_program_relationship',
          'entity' => 'FundingProgramRelationship',
          'bao' => 'CRM_Funding_DAO_FundingProgramRelationship',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'Civi\Funding\FundingPseudoConstants::getFundingProgramRelationshipTypes',
          ],
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_program_relationship', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_program_relationship', $prefix, []);
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
