<?php

class CRM_Civireport101_Form_Report_Example1Empty extends CRM_Report_Form {

  function __construct() {
    $this->_columns = array(
      'table_name' => array(
        'fields' => array(
          'hello' => array(
            'title' => ts('Hello'),
            'default' => TRUE,
          ),
        ),
      ),
    );
    //dpm($this->_columns);
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Empty Report'));
    parent::preProcess();
  }

  function select() {
    $this->_select = 'SELECT "Hello Reporting!" AS hello ';
    // note2: register columns you want displayed-
    $this->_columnHeaders = array(
      'hello' => array( 'title' => 'Hello' ),
    );
  }

  function from() { $this->_from = " "; }

  function where() { $this->_where = " "; }

  function groupBy() { $this->_groupBy = " "; }

  function orderBy() {  $this->_orderBy = " "; }

  function postProcess() {
    $this->beginPostProcess();
    $sql = $this->buildQuery(TRUE);
    //dpm($sql);

    $rows = array();
    $this->buildRows($sql, $rows);
    //dpm($rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    // TODO: custom code to alter rows
  }
}
