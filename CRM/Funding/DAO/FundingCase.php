<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingCase.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:ab1ce3104e077c474334092b3f6a52a3)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the FundingCase entity.
 */
class CRM_Funding_DAO_FundingCase extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_case';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingCase ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Unique generated identifier
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $identifier;

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
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $status;

  /**
   * @var string
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $creation_date;

  /**
   * @var string
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $modification_date;

  /**
   * FK to Contact
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $creation_contact_id;

  /**
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $notification_contact_ids;

  /**
   * FK to Contact
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $recipient_contact_id;

  /**
   * @var string|null
   *   (SQL type: decimal(10,2))
   *   Note that values will be retrieved from the database as a string.
   */
  public $amount_approved;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_case';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Funding Cases') : E::ts('Funding Case');
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
          'description' => E::ts('Unique FundingCase ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.id',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'identifier' => [
          'name' => 'identifier',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Identifier'),
          'description' => E::ts('Unique generated identifier'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.identifier',
          'dataPattern' => '/^[\p{L}\p{N}\p{P}]+$/u',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
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
          'where' => 'civicrm_funding_case.funding_program_id',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
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
          'title' => E::ts('Funding Case Type ID'),
          'description' => E::ts('FK to FundingCaseType'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.funding_case_type_id',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
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
        'status' => [
          'name' => 'status',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Status'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.status',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'Civi\Funding\FundingPseudoConstants::getFundingCaseStatus',
          ],
          'add' => NULL,
        ],
        'creation_date' => [
          'name' => 'creation_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Creation Date'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.creation_date',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'modification_date' => [
          'name' => 'modification_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Modification Date'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.modification_date',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'creation_contact_id' => [
          'name' => 'creation_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Creation Contact ID'),
          'description' => E::ts('FK to Contact'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.creation_contact_id',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
          ],
          'add' => NULL,
        ],
        'notification_contact_ids' => [
          'name' => 'notification_contact_ids',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Notification Contact Ids'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.notification_contact_ids',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_JSON,
          'add' => NULL,
        ],
        'recipient_contact_id' => [
          'name' => 'recipient_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Recipient'),
          'description' => E::ts('FK to Contact'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.recipient_contact_id',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
          ],
          'add' => NULL,
        ],
        'amount_approved' => [
          'name' => 'amount_approved',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => E::ts('Amount Approved'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_case.amount_approved',
          'dataPattern' => '/^\d{1,10}(\.\d{2})?$/',
          'table_name' => 'civicrm_funding_case',
          'entity' => 'FundingCase',
          'bao' => 'CRM_Funding_DAO_FundingCase',
          'localizable' => 0,
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
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_case', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_case', $prefix, []);
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
    $indices = [
      'UI_identifier' => [
        'name' => 'UI_identifier',
        'field' => [
          0 => 'identifier',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_funding_case::1::identifier',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
