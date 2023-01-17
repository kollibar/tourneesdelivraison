<?php

$formconfirm = '';

// Confirmation to delete
if ($action == 'ask_delete')
{
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteTourneeDeLivraison'), $langs->trans('ConfirmDeleteTourneeDeLivraison'), 'confirm_delete', '', 0, 1);
}

else if ($action == 'ask_cancel') {
  // Create an array for form
  $formquestion = array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CancelTourneeDeLivraison'), $langs->trans('ConfirmCancelTourneeDeLivraison', $object->ref), 'confirm_cancel', $formquestion, 'yes', 1);
}
else if ($action == 'ask_reprendre') {
  // Create an array for form
  $formquestion = array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ReprendreTourneeDeLivraison'), $langs->trans('ConfirmReprendreTourneeDeLivraison', $object->ref), 'confirm_reprendre', $formquestion, 'yes', 1);
}

// Clone confirmation
else if ($action == 'ask_clone') {
  // Create an array for form
  $formquestion = array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloneTourneeDeLivraison'), $langs->trans('ConfirmCloneTourneeDeLivraison', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
}
else if ($action == 'ask_close') {
  // Create an array for form
  $formquestion = array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloseTourneeDeLivraison'), $langs->trans('ConfirmCloseTourneeDeLivraison', $object->ref), 'confirm_close', $formquestion, 'yes', 1);
}

else if( $action == 'ask_validate'){
  $formquestion=array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ValidateTourneeDeLivraison'), $langs->trans('ConfirmValidateTourneeDeLivraison', $object->ref), 'confirm_validate', $formquestion, 'yes', 1);
}
else if( $action == 'ask_genererdocs'){
  $formquestion=array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('GenererDocs'), $langs->trans('ConfirmGenererDocs', $object->ref), 'confirm_genererdocs', $formquestion, 'yes', 1);
}

else if( $action == 'ask_unvalidate'){
  $formquestion=array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('UnvalidateTourneeDeLivraison'), $langs->trans('ConfirmUnvalidateTourneeDeLivraison', $object->ref), 'confirm_unvalidate', $formquestion, 'yes', 1);
}
else if( $action == 'ask_affectationauto'){
  $formquestion=array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('AffectationAutoTourneeUnique'), $langs->trans('ConfirmAffectationAutoTourneeUnique', $object->ref), 'confirm_affectationauto', $formquestion, 'yes', 1);
}
else if( $action == 'ask_reopen'){
  $formquestion=array();
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ReopenTourneeDeLivraison'), $langs->trans('ConfirmReopenTourneeDeLivraison', $object->ref), 'confirm_reopen', $formquestion, 'yes', 1);
}
else if( $action == 'ask_changestatutelt'){
  $formquestion=array();
  $statut=GETPOST('statut','int');
  $elt_type=GETPOST('elt_type','aZ09');
  $elt_id=GETPOST('elt_id','int');
  $elt_lineid=GETPOST('elt_lineid','int');
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . "&lineid=$lineid&elt_id=$elt_id&elt_lineid=$elt_lineid&elt_type=$elt_type&statut=$statut", $langs->trans('ChangeStatutElt'), $langs->trans('ConfirmChangeStatutElt', $langs->trans($type_elt) . ' '), 'confirm_changestatutelt', $formquestion, 'yes', 1);
}

else if($action == 'ask_changedateelt'){
  $formquestion=array();
  $elt_type=GETPOST('elt_type','aZ09');
  $elt_id=GETPOST('elt_id','int');
  $elt_lineid=GETPOST('elt_lineid','int');
  $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . "&lineid=$lineid&elt_id=$elt_id&elt_lineid=$elt_lineid&elt_type=$elt_type", $langs->trans('ChangeDateElt'), $langs->trans('ConfirmChangeDateEltToDateTournee', $langs->trans($type_elt) . ' '), 'confirm_changedateelt', $formquestion, 'yes', 1);
}

// Confirmation to delete line
else if ($action == 'ask_deleteline')
{
  $formconfirm=$form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeletelineTourneeDeLivraison'), $langs->trans('ConfirmDeletelineTourneeDeLivraison'), 'confirm_deleteline', '', 0, 1);
}

  // Confirmation to delete line
else if ($action == 'ask_deletecontact')
{
  $formconfirm=$form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&contactid='.$contactid, $langs->trans('DeletecontactTourneeDeLivraison'), $langs->trans('ConfirmDeletecontactTourneeDeLivraison'), 'confirm_deletecontact', '', 0, 1);
}

// Call Hook formConfirm
$parameters = array('lineid' => $lineid);
$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;
