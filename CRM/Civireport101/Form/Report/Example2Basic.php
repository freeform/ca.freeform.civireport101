<?php

class CRM_Civireport101_Form_Report_Example2Basic extends CRM_Report_Form {

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
          'first_name' => array(
            'title' => ts('First Name'),
            //'no_repeat' => TRUE,
          ),
          'last_name' => array(
            'title' => ts('Last Name'),
            //'no_repeat' => TRUE,
          ),
          'birth_date' => array(
            'title' => ts('Birth Date'),
          ),
        ),
        'filters' => array(
          'last_name' => array(
            'title' => ts('Last Name'),
            'operatorType' => CRM_Report_Form::OP_STRING,
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'grouping' => 'contact-fields',
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
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
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
  }
}
