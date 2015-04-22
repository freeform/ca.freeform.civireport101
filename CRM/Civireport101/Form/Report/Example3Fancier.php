<?php

class CRM_Civireport101_Form_Report_Example3Fancier extends CRM_Report_Form {

  protected $_exposeContactID = FALSE;

  protected $_customGroupExtends = array('Individual');

  function __construct() {
    $this->_autoIncludeIndexedFieldsAsOrderBys = TRUE;

    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'default' => TRUE,
            'required' => TRUE,
          ),
          'sort_name' => array(
            'title' => ts('Contact Name'),
            'default' => TRUE,
            'required' => TRUE,
          ),
          'prefix_id' => array(
            'title' => ts('Title'),
          ),
          'first_name' => array(
            'title' => ts('First Name'),
            'no_repeat' => TRUE,
          ),
          'last_name' => array(
            'title' => ts('Last Name'),
            'no_repeat' => TRUE,
          ),
          'gender_id' => array(
            'name' => 'gender_id',
            'title' => ts('Gender'),
          ),
          'birth_date' => array(
            'title' => ts('Birth Date'),
          ),
          'age' => array(
            'title' => ts('Age'),
            // Note: We have to use name to give the real name of the field since it is being used twice
            'name' => 'birth_date',
          ),
          'random_integer' => array(
            'title' => ts('Random Integer'),
            // dbAlias is the entire SQL that will be selected for this field
            // Here we just run some calculations
            'dbAlias' => 'FLOOR((RAND() * 10))',
          ),
        ),
        'filters' => array(
          'last_name' => array(
            'title' => ts('Last Name'),
            'operatorType' => CRM_Report_Form::OP_STRING,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'gender_id' => array(
            'name' => 'gender_id',
            'title' => ts('Gender'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'type' => CRM_Utils_Type::T_STRING,
            'options' => CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id'),
          ),
          'birth_date' => array(
            'name' => 'birth_date',
            'title' => ts('Birth Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
    );
    $this->_options = array(
      'larger_integers' => array(
        'title' => ts('Larger random integers'),
        'type' => 'checkbox',
      ),
    );
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Basic Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if ($fieldName == 'random_integer' && CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            if (array_key_exists('larger_integers', $this->_params) &&
              CRM_Utils_Array::value('larger_integers', $this->_params)) {
              $select[] = "{$field['dbAlias']} * 10 as {$tableName}_{$fieldName}";
            }
          }
          else if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
          }
          $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
          $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  function from() {
    $this->_from = "
         FROM  civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom} ";

  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $sql = $this->buildQuery(TRUE);
    //dpm($sql);
    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows

    $entryFound = FALSE;
    $gender = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');
    $prefix = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'prefix_id');

    $onHover = ts('View Contact Summary for this Contact');
    foreach ($rows as $rowNum => $row) {

      if (array_key_exists('civicrm_contact_sort_name', $row) && $this->_outputMode != 'csv') {
        // Here we use the hidden 'id' column
        if ($value = $row['civicrm_contact_id']) {
          $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $value, $this->_absoluteUrl);

          $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
          $rows[$rowNum]['civicrm_contact_sort_name_hover'] = $onHover;
          $entryFound = TRUE;
        }
      }
      if (array_key_exists('civicrm_contact_gender_id', $row)) {
        $value = $row['civicrm_contact_gender_id'];
        if ($value && isset($gender[$value])) {
          $rows[$rowNum]['civicrm_contact_gender_id'] = $gender[$value];
          $entryFound = TRUE;
        }
      }
      if (array_key_exists('civicrm_contact_prefix_id', $row)) {
        $value = $row['civicrm_contact_prefix_id'];
        if ($value && isset($prefix[$value])) {
          $rows[$rowNum]['civicrm_contact_prefix_id'] = $prefix[$value];
          $entryFound = TRUE;
        }
      }
      if (!$entryFound) {
        break;
      }
    }
  }
}
