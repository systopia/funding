<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from funding/xml/schema/CRM/Funding/FundingApplicationCiviOfficeTemplate.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:5c0d6e78cb4bcb4f29c656ddca21c8b5)
 */
use CRM_Funding_ExtensionUtil as E;

/**
 * Database access object for the ApplicationCiviOfficeTemplate entity.
 */
class CRM_Funding_DAO_ApplicationCiviOfficeTemplate extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_funding_application_civioffice_template';

  /**
   * Field to show when displaying a record.
   *
   * @var string
   */
  public static $_labelField = 'label';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FundingApplicationCiviOfficeTemplate ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to FundingCaseType
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $case_type_id;

  /**
   * CiviOffice document URI
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $document_uri;

  /**
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $label;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_funding_application_civioffice_template';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Application Templates') : E::ts('Application Template');
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
          'description' => E::ts('Unique FundingApplicationCiviOfficeTemplate ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_application_civioffice_template.id',
          'table_name' => 'civicrm_funding_application_civioffice_template',
          'entity' => 'ApplicationCiviOfficeTemplate',
          'bao' => 'CRM_Funding_DAO_ApplicationCiviOfficeTemplate',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'case_type_id' => [
          'name' => 'case_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Case Type ID'),
          'description' => E::ts('FK to FundingCaseType'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_application_civioffice_template.case_type_id',
          'table_name' => 'civicrm_funding_application_civioffice_template',
          'entity' => 'ApplicationCiviOfficeTemplate',
          'bao' => 'CRM_Funding_DAO_ApplicationCiviOfficeTemplate',
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
        'document_uri' => [
          'name' => 'document_uri',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Document'),
          'description' => E::ts('CiviOffice document URI'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_application_civioffice_template.document_uri',
          'table_name' => 'civicrm_funding_application_civioffice_template',
          'entity' => 'ApplicationCiviOfficeTemplate',
          'bao' => 'CRM_Funding_DAO_ApplicationCiviOfficeTemplate',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'Civi\Funding\DocumentRender\CiviOffice\CiviOfficePseudoConstants::getSharedDocumentUris',
          ],
          'add' => NULL,
        ],
        'label' => [
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Label'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_funding_application_civioffice_template.label',
          'table_name' => 'civicrm_funding_application_civioffice_template',
          'entity' => 'ApplicationCiviOfficeTemplate',
          'bao' => 'CRM_Funding_DAO_ApplicationCiviOfficeTemplate',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'funding_application_civioffice_template', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'funding_application_civioffice_template', $prefix, []);
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
      'UI_case_type_id_label' => [
        'name' => 'UI_case_type_id_label',
        'field' => [
          0 => 'case_type_id',
          1 => 'label',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_funding_application_civioffice_template::1::case_type_id::label',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
