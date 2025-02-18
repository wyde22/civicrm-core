<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * This class generates form components for processing a Grant
 *
 */
class CRM_Grant_Form_GrantView extends CRM_Core_Form {

  /**
   * Set variables up before form is built.
   *
   * @return void
   */
  public function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $context = CRM_Utils_Request::retrieve('context', 'Alphanumeric', $this);
    $this->assign('context', $context);

    $values = [];
    $params['id'] = $this->_id;
    CRM_Grant_BAO_Grant::retrieve($params, $values);
    $grantType = CRM_Core_PseudoConstant::get('CRM_Grant_DAO_Grant', 'grant_type_id');
    $grantStatus = CRM_Core_PseudoConstant::get('CRM_Grant_DAO_Grant', 'status_id');
    $this->assign('grantType', $grantType[$values['grant_type_id']]);
    $this->assign('grantStatus', $grantStatus[$values['status_id']]);
    $grantTokens = [
      'amount_total',
      'amount_requested',
      'amount_granted',
      'rationale',
      'grant_report_received',
      'application_received_date',
      'decision_date',
      'money_transfer_date',
      'grant_due_date',
    ];

    foreach ($grantTokens as $token) {
      $this->assign($token, $values[$token] ?? NULL);
    }
    $displayName = CRM_Contact_BAO_Contact::displayName($values['contact_id']);
    $this->assign('displayName', $displayName);

    if (isset($this->_id)) {
      $noteDAO = new CRM_Core_BAO_Note();
      $noteDAO->entity_table = 'civicrm_grant';
      $noteDAO->entity_id = $this->_id;
      if ($noteDAO->find(TRUE)) {
        $this->_noteId = $noteDAO->id;
      }
    }

    if (isset($this->_noteId)) {
      $this->assign('note', CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Note', $this->_noteId, 'note'));
    }

    // add Grant to Recent Items
    $url = CRM_Utils_System::url('civicrm/grant/view',
      "action=view&reset=1&id={$values['id']}"
    );

    $title = CRM_Contact_BAO_Contact::displayName($values['contact_id']) . ' - ' . ts('Grant') . ': ' . CRM_Utils_Money::format($values['amount_total']) . ' (' . $grantType[$values['grant_type_id']] . ')';

    $recentOther = [];
    if (CRM_Core_Permission::check('edit grants')) {
      $recentOther['editUrl'] = CRM_Utils_System::url('civicrm/grant/add',
        "action=update&reset=1&id={$values['id']}&cid={$values['contact_id']}"
      );
    }
    if (CRM_Core_Permission::check('delete in CiviGrant')) {
      $recentOther['deleteUrl'] = CRM_Utils_System::url('civicrm/grant/add',
        "action=delete&reset=1&id={$values['id']}&cid={$values['contact_id']}"
      );
    }
    CRM_Utils_Recent::add($title,
      $url,
      $values['id'],
      'Grant',
      $values['contact_id'],
      NULL,
      $recentOther
    );

    $attachment = CRM_Core_BAO_File::attachmentInfo('civicrm_grant', $this->_id);
    $this->assign('attachment', $attachment);

    $grantType = CRM_Core_DAO::getFieldValue("CRM_Grant_DAO_Grant", $this->_id, "grant_type_id");
    $groupTree = CRM_Core_BAO_CustomGroup::getTree("Grant", NULL, $this->_id, 0, $grantType, NULL,
      TRUE, NULL, FALSE, CRM_Core_Permission::VIEW);
    CRM_Core_BAO_CustomGroup::buildCustomDataView($this, $groupTree, FALSE, NULL, NULL, NULL, $this->_id);

    $this->assign('id', $this->_id);

    $this->setPageTitle(ts('Grant'));
  }

  /**
   * Build the form object.
   *
   * @return void
   */
  public function buildQuickForm() {
    $this->addButtons([
      [
        'type' => 'cancel',
        'name' => ts('Done'),
        'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        'isDefault' => TRUE,
      ],
    ]);
  }

}
