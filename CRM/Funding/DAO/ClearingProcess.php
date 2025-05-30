<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingClearingProcess.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:f076b3650dea1c6084d20ca0c52efbae)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the ClearingProcess entity.
 */
class CRM_Funding_DAO_ClearingProcess extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_clearing_process';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingClearingProcess ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to FundingApplicationProcess
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $application_process_id;

  /**
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $status;

  /**
   * Start of the clearing. (Not date of entity creation.)
   *
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
   * Actual start of the activity
   *
   * @var string
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $start_date;

  /**
   * Actual end of the activity
   *
   * @var string
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $end_date;

  /**
   * @var string
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $report_data;

  /**
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_review_content;

  /**
   * FK to Contact
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $reviewer_cont_contact_id;

  /**
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_review_calculative;

  /**
   * FK to Contact
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $reviewer_calc_contact_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_clearing_process';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Clearing Processes') : E::ts('Clearing Process');
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
          'description' => E::ts('Unique FundingClearingProcess ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.id',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'application_process_id' => [
          'name' => 'application_process_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Application Process ID'),
          'description' => E::ts('FK to FundingApplicationProcess'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.application_process_id',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'FKClassName' => 'CRM_Funding_DAO_ApplicationProcess',
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
          'where' => 'civicrm_funding_clearing_process.status',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'Civi\Funding\FundingPseudoConstants::getClearingProcessStatus',
          ],
          'add' => NULL,
        ],
        'creation_date' => [
          'name' => 'creation_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Creation Date'),
          'description' => E::ts('Start of the clearing. (Not date of entity creation.)'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.creation_date',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
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
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.modification_date',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'start_date' => [
          'name' => 'start_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Start Date'),
          'description' => E::ts('Actual start of the activity'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.start_date',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'end_date' => [
          'name' => 'end_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('End Date'),
          'description' => E::ts('Actual end of the activity'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.end_date',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'report_data' => [
          'name' => 'report_data',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Report Data'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.report_data',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_JSON,
          'add' => NULL,
        ],
        'is_review_content' => [
          'name' => 'is_review_content',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is Review Content'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.is_review_content',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
        'reviewer_cont_contact_id' => [
          'name' => 'reviewer_cont_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Reviewer Cont Contact ID'),
          'description' => E::ts('FK to Contact'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.reviewer_cont_contact_id',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
          ],
          'add' => NULL,
        ],
        'is_review_calculative' => [
          'name' => 'is_review_calculative',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is Review Calculative'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.is_review_calculative',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
          'add' => NULL,
        ],
        'reviewer_calc_contact_id' => [
          'name' => 'reviewer_calc_contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Reviewer Calc Contact ID'),
          'description' => E::ts('FK to Contact'),
          'required' => FALSE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_clearing_process.reviewer_calc_contact_id',
          'table_name' => 'civicrm_funding_clearing_process',
          'entity' => 'ClearingProcess',
          'bao' => 'CRM_Funding_DAO_ClearingProcess',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_clearing_process', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_clearing_process', $prefix, []);
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
      'UI_application_process_id' => [
        'name' => 'UI_application_process_id',
        'field' => [
          0 => 'application_process_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_funding_clearing_process::1::application_process_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
