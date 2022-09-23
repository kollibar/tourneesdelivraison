<?php
/* Copyright (C) 2003-2008	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2005-2016	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2005		Simon TOSSER			<simon@kornog-computing.com>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2011-2017	Juanjo Menent			<jmenent@2byte.es>
 * Copyright (C) 2013       Florian Henry		  	<florian.henry@open-concept.pro>
 * Copyright (C) 2013       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2014		Cedric GROSS			<c.gross@kreiz-it.fr>
 * Copyright (C) 2014-2017	Francis Appels			<francis.appels@yahoo.com>
 * Copyright (C) 2015		Claudio Aschieri		<c.aschieri@19.coop>
 * Copyright (C) 2016-2018	Ferran Marcet			<fmarcet@2byte.es>
 * Copyright (C) 2016		Yasser Carreón			<yacasia@gmail.com>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2020       Lenin Rivas         	<lenin@leninrivas.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

 /**
  *   \file       htdocs/tourneesdelivraison/expedition_card.php
  *		\ingroup    tourneesdelivraison
  *		\brief      Page to view/edit a delivery stop
  */



  /**
 *
 * actions possible :
 *          * cloturer livraison
 *          * marqué comme lvré (commande) si clotué n'a pas fonctionné
 *          * marqué commande comme facturée
 *          * facturer livraison
 *          * envoyer email
 *          * supprimer expédition
 *          * ...
  */

 // Load Dolibarr environment
 $res = 0;
 // Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
 if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
 	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
 }
 // Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
 $tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
 while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
 	$i--; $j--;
 }
 if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
 	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
 }
 if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
 	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
 }
 // Try main.inc.php using relative path
 if (!$res && file_exists("../main.inc.php")) {
 	$res = @include "../main.inc.php";
 }
 if (!$res && file_exists("../../main.inc.php")) {
 	$res = @include "../../main.inc.php";
 }
 if (!$res && file_exists("../../../main.inc.php")) {
 	$res = @include "../../../main.inc.php";
 }
 if (!$res) {
 	die("Include of main fails");
 }


require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/sendings.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (!empty($conf->product->enabled) || !empty($conf->service->enabled))  require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
if (!empty($conf->propal->enabled))   require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
if (!empty($conf->productbatch->enabled)) require_once DOL_DOCUMENT_ROOT.'/product/class/productbatch.class.php';
if (!empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}


$typetournee='tourneeunique';

dol_include_once('/tourneesdelivraison/class/html.formtourneesdelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_arret.class.php');
dol_include_once('/tourneesdelivraison/lib/tournee.lib.php');


// Load translation files required by the page
$langs->loadLangs(array("tourneesdelivraison@tourneesdelivraison", "sendings", "companies", "bills", 'deliveries', 'orders', 'stocks', 'other', 'propal'));

if (! empty($conf->categorie->enabled)) $langs->load("categories");
if (!empty($conf->incoterm->enabled)) $langs->load('incoterm');
if (!empty($conf->productbatch->enabled)) $langs->load('productbatch');

$origin = GETPOST('origin', 'alpha') ?GETPOST('origin', 'alpha') : 'expedition'; // Example: commande, propal
$origin_id = GETPOST('id', 'int') ?GETPOST('id', 'int') : '';
$id = $origin_id;
if (empty($origin_id)) $origin_id  = GETPOST('origin_id', 'int'); // Id of order or propal
if (empty($origin_id)) $origin_id  = GETPOST('object_id', 'int'); // Id of order or propal
$ref = GETPOST('ref', 'alpha');
$line_id = GETPOST('lineid', 'int') ?GETPOST('lineid', 'int') : '';

// Security check
$socid = '';
if ($user->socid) $socid = $user->socid;

if ($origin == 'expedition') $result = restrictedArea($user, $origin, $id);
else {
	$result = restrictedArea($user, 'expedition');
	if (empty($user->rights->{$origin}->lire) && empty($user->rights->{$origin}->read)) accessforbidden();
}

$action		= GETPOST('action', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');

//PDF
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (!empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

$exp = new Expedition($db);
$exporder = new Commande($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($exp->table_element);
$extrafields->fetch_name_optionals_label($exp->table_element_line);
$extrafields->fetch_name_optionals_label($exporder->table_element_line);

// Load object. Make an object->fetch
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('expeditioncard', 'globalcard'));

$permissiondellink = $user->rights->expedition->delivery->creer; // Used by the include of actions_dellink.inc.php
//var_dump($exp->lines[0]->detail_batch);

$date_delivery = dol_mktime(GETPOST('date_deliveryhour', 'int'), GETPOST('date_deliverymin', 'int'), 0, GETPOST('date_deliverymonth', 'int'), GETPOST('date_deliveryday', 'int'), GETPOST('date_deliveryyear', 'int'));


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $exp, $action); // Note that $action and $exp may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if ($cancel)
	{
		$action = '';
		$exp->fetch($id); // show shipment also after canceling modification
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php'; // Must be include, not include_once

	// Actions to build doc
	$upload_dir = $conf->expedition->dir_output.'/sending';
	$permissiontoadd = $user->rights->expedition->creer;
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	// Reopen
	if ($action == 'reopen' && $user->rights->expedition->creer)
	{
		$exp->fetch($id);
		$result = $exp->reOpen();
	}

	// Set incoterm
	if ($action == 'set_incoterms' && !empty($conf->incoterm->enabled))
	{
		$result = $exp->setIncoterms(GETPOST('incoterm_id', 'int'), GETPOST('location_incoterms', 'alpha'));
	}

	if ($action == 'setref_customer')
	{
		$result = $exp->fetch($id);
		if ($result < 0) {
			setEventMessages($exp->error, $exp->errors, 'errors');
		}

		$result = $exp->setValueFrom('ref_customer', GETPOST('ref_customer', 'alpha'), '', null, 'text', '', $user, 'SHIPMENT_MODIFY');
		if ($result < 0) {
			setEventMessages($exp->error, $exp->errors, 'errors');
			$action = 'editref_customer';
		} else {
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$exp->id);
			exit;
		}
	}

	if ($action == 'update_extras')
	{
		$exp->oldcopy = dol_clone($exp);

		// Fill array 'array_options' with data from update form
		$ret = $extrafields->setOptionalsFromPost(null, $exp, GETPOST('attribute', 'restricthtml'));
		if ($ret < 0) $error++;

		if (!$error)
		{
			// Actions on extra fields
			$result = $exp->insertExtraFields('SHIPMENT_MODIFY');
			if ($result < 0)
			{
				setEventMessages($exp->error, $exp->errors, 'errors');
				$error++;
			}
		}

		if ($error)
			$action = 'edit_extras';
	}

	// Create shipment
	if ($action == 'add' && $user->rights->expedition->creer)
	{
		$error = 0;
		$predef = '';

		$db->begin();

		$exp->note = GETPOST('note', 'alpha');
		$exp->origin				= $origin;
		$exp->origin_id = $origin_id;
		$exp->fk_project = GETPOST('projectid', 'int');
		$exp->weight				= GETPOST('weight', 'int') == '' ? "NULL" : GETPOST('weight', 'int');
		$exp->sizeH				= GETPOST('sizeH', 'int') == '' ? "NULL" : GETPOST('sizeH', 'int');
		$exp->sizeW				= GETPOST('sizeW', 'int') == '' ? "NULL" : GETPOST('sizeW', 'int');
		$exp->sizeS				= GETPOST('sizeS', 'int') == '' ? "NULL" : GETPOST('sizeS', 'int');
		$exp->size_units = GETPOST('size_units', 'int');
		$exp->weight_units = GETPOST('weight_units', 'int');

		// We will loop on each line of the original document to complete the shipping object with various info and quantity to deliver
		$classname = ucfirst($exp->origin);
		$expsrc = new $classname($db);
		$expsrc->fetch($exp->origin_id);

		$exp->socid = $expsrc->socid;
		$exp->ref_customer = GETPOST('ref_customer', 'alpha');
		$exp->model_pdf = GETPOST('model');
		$exp->date_delivery = $date_delivery; // Date delivery planed
		$exp->fk_delivery_address	= $expsrc->fk_delivery_address;
		$exp->shipping_method_id		= GETPOST('shipping_method_id', 'int');
		$exp->tracking_number = GETPOST('tracking_number', 'alpha');
		$exp->ref_int = GETPOST('ref_int', 'alpha');
		$exp->note_private = GETPOST('note_private', 'restricthtml');
		$exp->note_public = GETPOST('note_public', 'restricthtml');
		$exp->fk_incoterms = GETPOST('incoterm_id', 'int');
		$exp->location_incoterms = GETPOST('location_incoterms', 'alpha');

		$batch_line = array();
		$stockLine = array();
		$array_options = array();

		$num = count($expsrc->lines);
		$totalqty = 0;

		for ($i = 0; $i < $num; $i++)
		{
			$idl = "idl".$i;

			$sub_qty = array();
			$subtotalqty = 0;

			$j = 0;
			$batch = "batchl".$i."_0";
			$stockLocation = "ent1".$i."_0";
			$qty = "qtyl".$i;

			if (!empty($conf->productbatch->enabled) && $expsrc->lines[$i]->product_tobatch)      // If product need a batch number
			{
				if (GETPOSTISSET($batch))
				{
					//shipment line with batch-enable product
					$qty .= '_'.$j;
					while (GETPOSTISSET($batch))
					{
						// save line of detail into sub_qty
						$sub_qty[$j]['q'] = GETPOST($qty, 'int'); // the qty we want to move for this stock record
						$sub_qty[$j]['id_batch'] = GETPOST($batch, 'int'); // the id into llx_product_batch of stock record to move
						$subtotalqty += $sub_qty[$j]['q'];

						//var_dump($qty);var_dump($batch);var_dump($sub_qty[$j]['q']);var_dump($sub_qty[$j]['id_batch']);

						$j++;
						$batch = "batchl".$i."_".$j;
						$qty = "qtyl".$i.'_'.$j;
					}

					$batch_line[$i]['detail'] = $sub_qty; // array of details
					$batch_line[$i]['qty'] = $subtotalqty;
					$batch_line[$i]['ix_l'] = GETPOST($idl, 'int');

					$totalqty += $subtotalqty;
				} else {
					// No detail were provided for lots
					if (!empty($_POST[$qty]))
					{
						// We try to set an amount
						// Case we dont use the list of available qty for each warehouse/lot
						// GUI does not allow this yet
						setEventMessages($langs->trans("StockIsRequiredToChooseWhichLotToUse"), null, 'errors');
					}
				}
			} elseif (GETPOSTISSET($stockLocation)) {
				//shipment line from multiple stock locations
				$qty .= '_'.$j;
				while (GETPOSTISSET($stockLocation))
				{
					// save sub line of warehouse
					$stockLine[$i][$j]['qty'] = price2num(GETPOST($qty, 'alpha'), 'MS');
					$stockLine[$i][$j]['warehouse_id'] = GETPOST($stockLocation, 'int');
					$stockLine[$i][$j]['ix_l'] = GETPOST($idl, 'int');

					$totalqty += price2num(GETPOST($qty, 'alpha'), 'MS');

					$j++;
					$stockLocation = "ent1".$i."_".$j;
					$qty = "qtyl".$i.'_'.$j;
				}
			} else {
				//var_dump(GETPOST($qty,'alpha')); var_dump($_POST); var_dump($batch);exit;
				//shipment line for product with no batch management and no multiple stock location
				if (GETPOST($qty, 'int') > 0) $totalqty += price2num(GETPOST($qty, 'alpha'), 'MS');
			}

			// Extrafields
			$array_options[$i] = $extrafields->getOptionalsFromPost($exp->table_element_line, $i);
			// Unset extrafield
			if (is_array($extrafields->attributes[$exp->table_element_line]['label'])) {
				// Get extra fields
				foreach ($extrafields->attributes[$exp->table_element_line]['label'] as $key => $value) {
					unset($_POST["options_".$key]);
				}
			}
		}

		//var_dump($batch_line[2]);

		if ($totalqty > 0)		// There is at least one thing to ship
		{
			//var_dump($_POST);exit;
			for ($i = 0; $i < $num; $i++)
			{
				$qty = "qtyl".$i;
				if (!isset($batch_line[$i]))
				{
					// not batch mode
					if (isset($stockLine[$i]))
					{
						//shipment from multiple stock locations
						$nbstockline = count($stockLine[$i]);
						for ($j = 0; $j < $nbstockline; $j++)
						{
							if ($stockLine[$i][$j]['qty'] > 0)
							{
								$ret = $exp->addline($stockLine[$i][$j]['warehouse_id'], $stockLine[$i][$j]['ix_l'], $stockLine[$i][$j]['qty'], $array_options[$i]);
								if ($ret < 0)
								{
									setEventMessages($exp->error, $exp->errors, 'errors');
									$error++;
								}
							}
						}
					} else {
						if (GETPOST($qty, 'int') > 0 || (GETPOST($qty, 'int') == 0 && $conf->global->SHIPMENT_GETS_ALL_ORDER_PRODUCTS))
						{
							$ent = "entl".$i;
							$idl = "idl".$i;
							$entrepot_id = is_numeric(GETPOST($ent, 'int')) ?GETPOST($ent, 'int') : GETPOST('entrepot_id', 'int');
							if ($entrepot_id < 0) $entrepot_id = '';
							if (!($expsrc->lines[$i]->fk_product > 0)) $entrepot_id = 0;

							$ret = $exp->addline($entrepot_id, GETPOST($idl, 'int'), GETPOST($qty, 'int'), $array_options[$i]);
							if ($ret < 0)
							{
								setEventMessages($exp->error, $exp->errors, 'errors');
								$error++;
							}
						}
					}
				} else {
					// batch mode
					if ($batch_line[$i]['qty'] > 0)
					{
						$ret = $exp->addline_batch($batch_line[$i], $array_options[$i]);
						if ($ret < 0)
						{
							setEventMessages($exp->error, $exp->errors, 'errors');
							$error++;
						}
					}
				}
			}
			// Fill array 'array_options' with data from add form
			$ret = $extrafields->setOptionalsFromPost(null, $exp);
			if ($ret < 0) $error++;

			if (!$error)
			{
				$ret = $exp->create($user); // This create shipment (like Odoo picking) and lines of shipments. Stock movement will be done when validating shipment.
				if ($ret <= 0)
				{
					setEventMessages($exp->error, $exp->errors, 'errors');
					$error++;
				}
			}
		} else {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("QtyToShip").'/'.$langs->transnoentitiesnoconv("Warehouse")), null, 'errors');
			$error++;
		}

		if (!$error)
		{
			$db->commit();
			header("Location: card.php?id=".$exp->id);
			exit;
		} else {
			$db->rollback();
			$_GET["commande_id"] = GETPOST('commande_id', 'int');
			$action = 'create';
		}
	}

	/*
	 * Build a receiving receipt
	 */
	elseif ($action == 'create_delivery' && $conf->delivery_note->enabled && $user->rights->expedition->delivery->creer)
	{
		$result = $exp->create_delivery($user);
		if ($result > 0)
		{
			header("Location: ".DOL_URL_ROOT.'/delivery/card.php?action=create_delivery&id='.$result);
			exit;
		} else {
			setEventMessages($exp->error, $exp->errors, 'errors');
		}
	} elseif ($action == 'confirm_valid' && $confirm == 'yes' &&
		((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->expedition->creer))
	   	|| (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->expedition->shipping_advance->validate)))
	)
	{
		$exp->fetch_thirdparty();

		$result = $exp->valid($user);

		if ($result < 0) {
			setEventMessages($exp->error, $exp->errors, 'errors');
		} else {
			// Define output language
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
			{
				$outputlangs = $langs;
				$newlang = '';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $exp->thirdparty->default_lang;
				if (!empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}
				$model = $exp->model_pdf;
				$ret = $exp->fetch($id); // Reload to get new records

				$result = $exp->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
				if ($result < 0) dol_print_error($db, $result);
			}
		}
	} elseif ($action == 'confirm_cancel' && $confirm == 'yes' && $user->rights->expedition->supprimer)
	{
		$also_update_stock = (GETPOST('alsoUpdateStock', 'alpha') ? 1 : 0);
		$result = $exp->cancel(0, $also_update_stock);
		if ($result > 0)
		{
			$result = $exp->setStatut(-1);
		} else {
			setEventMessages($exp->error, $exp->errors, 'errors');
		}
	} elseif ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->expedition->supprimer)
	{
		$also_update_stock = (GETPOST('alsoUpdateStock', 'alpha') ? 1 : 0);
		$result = $exp->delete(0, $also_update_stock);
		if ($result > 0)
		{
			header("Location: ".DOL_URL_ROOT.'/expedition/index.php');
			exit;
		} else {
			setEventMessages($exp->error, $exp->errors, 'errors');
		}
	}
	// TODO add alternative status
	/*elseif ($action == 'reopen' && (! empty($user->rights->expedition->creer) || ! empty($user->rights->expedition->shipping_advance->validate)))
	{
	    $result = $exp->setStatut(0);
	    if ($result < 0)
	    {
	        setEventMessages($exp->error, $exp->errors, 'errors');
	    }
	}*/

	elseif ($action == 'setdate_livraison' && $user->rights->expedition->creer)
	{
		//print "x ".$_POST['liv_month'].", ".$_POST['liv_day'].", ".$_POST['liv_year'];
		$datedelivery = dol_mktime(GETPOST('liv_hour', 'int'), GETPOST('liv_min', 'int'), 0, GETPOST('liv_month', 'int'), GETPOST('liv_day', 'int'), GETPOST('liv_year', 'int'));

		$exp->fetch($id);
		$result = $exp->setDeliveryDate($user, $datedelivery);
		if ($result < 0)
		{
			setEventMessages($exp->error, $exp->errors, 'errors');
		}
	}

	// Action update
	elseif (
		($action == 'settracking_number'
		|| $action == 'settracking_url'
		|| $action == 'settrueWeight'
		|| $action == 'settrueWidth'
		|| $action == 'settrueHeight'
		|| $action == 'settrueDepth'
		|| $action == 'setshipping_method_id')
		&& $user->rights->expedition->creer
		)
	{
		$error = 0;

		if ($action == 'settracking_number')		$exp->tracking_number = trim(GETPOST('tracking_number', 'alpha'));
		if ($action == 'settracking_url')		$exp->tracking_url = trim(GETPOST('tracking_url', 'int'));
		if ($action == 'settrueWeight') {
			$exp->trueWeight = trim(GETPOST('trueWeight', 'int'));
			$exp->weight_units = GETPOST('weight_units', 'int');
		}
		if ($action == 'settrueWidth')			$exp->trueWidth = trim(GETPOST('trueWidth', 'int'));
		if ($action == 'settrueHeight') {
						$exp->trueHeight = trim(GETPOST('trueHeight', 'int'));
						$exp->size_units = GETPOST('size_units', 'int');
		}
		if ($action == 'settrueDepth')			$exp->trueDepth = trim(GETPOST('trueDepth', 'int'));
		if ($action == 'setshipping_method_id')	$exp->shipping_method_id = trim(GETPOST('shipping_method_id', 'int'));

		if (!$error)
		{
			if ($exp->update($user) >= 0)
			{
				header("Location: card.php?id=".$exp->id);
				exit;
			}
			setEventMessages($exp->error, $exp->errors, 'errors');
		}

		$action = "";
	} elseif ($action == 'classifybilled')
	{
		$exp->fetch($id);
		$result = $exp->set_billed();
		if ($result >= 0) {
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$exp->id);
			exit();
		}
		setEventMessages($exp->error, $exp->errors, 'errors');
	} elseif ($action == 'classifyclosed')
	{
		$exp->fetch($id);
		$result = $exp->setClosed();
		if ($result >= 0) {
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$exp->id);
			exit();
		}
		setEventMessages($exp->error, $exp->errors, 'errors');
	}

	/*
	 *  delete a line
	 */
	elseif ($action == 'deleteline' && !empty($line_id))
	{
		$exp->fetch($id);
		$lines = $exp->lines;
		$line = new ExpeditionLigne($db);

		$num_prod = count($lines);
		for ($i = 0; $i < $num_prod; $i++)
		{
			if ($lines[$i]->id == $line_id)
			{
				if (count($lines[$i]->details_entrepot) > 1)
				{
					// delete multi warehouse lines
					foreach ($lines[$i]->details_entrepot as $details_entrepot) {
						$line->id = $details_entrepot->line_id;
						if (!$error && $line->delete($user) < 0)
						{
							$error++;
						}
					}
				} else {
					// delete single warehouse line
					$line->id = $line_id;
					if (!$error && $line->delete($user) < 0)
					{
						$error++;
					}
				}
			}
			unset($_POST["lineid"]);
		}

		if (!$error) {
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$exp->id);
			exit();
		} else {
			setEventMessages($line->error, $line->errors, 'errors');
		}
	}

	/*
	 *  Update a line
	 */
	elseif ($action == 'updateline' && $user->rights->expedition->creer && GETPOST('save'))
	{
		// Clean parameters
		$qty = 0;
		$entrepot_id = 0;
		$batch_id = 0;

		$lines = $exp->lines;
		$num_prod = count($lines);
		for ($i = 0; $i < $num_prod; $i++)
		{
			if ($lines[$i]->id == $line_id)		// we have found line to update
			{
				$line = new ExpeditionLigne($db);

				// Extrafields Lines
				$line->array_options = $extrafields->getOptionalsFromPost($exp->table_element_line);
				// Unset extrafield POST Data
				if (is_array($extrafields->attributes[$exp->table_element_line]['label'])) {
					foreach ($extrafields->attributes[$exp->table_element_line]['label'] as $key => $value) {
						unset($_POST["options_".$key]);
					}
				}
				$line->fk_product = $lines[$i]->fk_product;
				if (is_array($lines[$i]->detail_batch) && count($lines[$i]->detail_batch) > 0)
				{
					// line with lot
					foreach ($lines[$i]->detail_batch as $detail_batch)
					{
						$lotStock = new Productbatch($db);
						$batch = "batchl".$detail_batch->fk_expeditiondet."_".$detail_batch->fk_origin_stock;
						$qty = "qtyl".$detail_batch->fk_expeditiondet.'_'.$detail_batch->id;
						$batch_id = GETPOST($batch, 'int');
						$batch_qty = GETPOST($qty, 'int');
						if (!empty($batch_id) && ($batch_id != $detail_batch->fk_origin_stock || $batch_qty != $detail_batch->qty))
						{
							if ($lotStock->fetch($batch_id) > 0 && $line->fetch($detail_batch->fk_expeditiondet) > 0)	// $line is ExpeditionLine
							{
								if ($lines[$i]->entrepot_id != 0)
								{
									// allow update line entrepot_id if not multi warehouse shipping
									$line->entrepot_id = $lotStock->warehouseid;
								}

								// detail_batch can be an object with keys, or an array of ExpeditionLineBatch
								if (empty($line->detail_batch)) $line->detail_batch = new stdClass();

								$line->detail_batch->fk_origin_stock = $batch_id;
								$line->detail_batch->batch = $lotStock->batch;
								$line->detail_batch->id = $detail_batch->id;
								$line->detail_batch->entrepot_id = $lotStock->warehouseid;
								$line->detail_batch->qty = $batch_qty;
								if ($line->update($user) < 0) {
									setEventMessages($line->error, $line->errors, 'errors');
									$error++;
								}
							} else {
								setEventMessages($lotStock->error, $lotStock->errors, 'errors');
								$error++;
							}
						}
						unset($_POST[$batch]);
						unset($_POST[$qty]);
					}
					// add new batch
					$lotStock = new Productbatch($db);
					$batch = "batchl".$line_id."_0";
					$qty = "qtyl".$line_id."_0";
					$batch_id = GETPOST($batch, 'int');
					$batch_qty = GETPOST($qty, 'int');
					$lineIdToAddLot = 0;
					if ($batch_qty > 0 && !empty($batch_id))
					{
						if ($lotStock->fetch($batch_id) > 0)
						{
							// check if lotStock warehouse id is same as line warehouse id
							if ($lines[$i]->entrepot_id > 0)
							{
								// single warehouse shipment line
								if ($lines[$i]->entrepot_id == $lotStock->warehouseid)
								{
									$lineIdToAddLot = $line_id;
								}
							} elseif (count($lines[$i]->details_entrepot) > 1)
							{
								// multi warehouse shipment lines
								foreach ($lines[$i]->details_entrepot as $detail_entrepot)
								{
									if ($detail_entrepot->entrepot_id == $lotStock->warehouseid)
									{
										$lineIdToAddLot = $detail_entrepot->line_id;
									}
								}
							}
							if ($lineIdToAddLot)
							{
								// add lot to existing line
								if ($line->fetch($lineIdToAddLot) > 0)
								{
									$line->detail_batch->fk_origin_stock = $batch_id;
									$line->detail_batch->batch = $lotStock->batch;
									$line->detail_batch->entrepot_id = $lotStock->warehouseid;
									$line->detail_batch->qty = $batch_qty;
									if ($line->update($user) < 0) {
										setEventMessages($line->error, $line->errors, 'errors');
										$error++;
									}
								} else {
									setEventMessages($line->error, $line->errors, 'errors');
									$error++;
								}
							} else {
								// create new line with new lot
								$line->origin_line_id = $lines[$i]->origin_line_id;
								$line->entrepot_id = $lotStock->warehouseid;
								$line->detail_batch[0] = new ExpeditionLineBatch($db);
								$line->detail_batch[0]->fk_origin_stock = $batch_id;
								$line->detail_batch[0]->batch = $lotStock->batch;
								$line->detail_batch[0]->entrepot_id = $lotStock->warehouseid;
								$line->detail_batch[0]->qty = $batch_qty;
								if ($exp->create_line_batch($line, $line->array_options) < 0)
								{
									setEventMessages($exp->error, $exp->errors, 'errors');
									$error++;
								}
							}
						} else {
							setEventMessages($lotStock->error, $lotStock->errors, 'errors');
							$error++;
						}
					}
				} else {
					if ($lines[$i]->fk_product > 0)
					{
						// line without lot
						if ($lines[$i]->entrepot_id > 0)
						{
							// single warehouse shipment line
							$stockLocation = "entl".$line_id;
							$qty = "qtyl".$line_id;
							$line->id = $line_id;
							$line->entrepot_id = GETPOST($stockLocation, 'int');
							$line->qty = GETPOST($qty, 'int');
							if ($line->update($user) < 0) {
								setEventMessages($line->error, $line->errors, 'errors');
								$error++;
							}
							unset($_POST[$stockLocation]);
							unset($_POST[$qty]);
						} elseif (count($lines[$i]->details_entrepot) > 1)
						{
							// multi warehouse shipment lines
							foreach ($lines[$i]->details_entrepot as $detail_entrepot)
							{
								if (!$error) {
									$stockLocation = "entl".$detail_entrepot->line_id;
									$qty = "qtyl".$detail_entrepot->line_id;
									$warehouse = GETPOST($stockLocation, 'int');
									if (!empty($warehouse))
									{
										$line->id = $detail_entrepot->line_id;
										$line->entrepot_id = $warehouse;
										$line->qty = GETPOST($qty, 'int');
										if ($line->update($user) < 0) {
											setEventMessages($line->error, $line->errors, 'errors');
											$error++;
										}
									}
									unset($_POST[$stockLocation]);
									unset($_POST[$qty]);
								}
							}
						}
					} else {
						// Product no predefined
						$qty = "qtyl".$line_id;
						$line->id = $line_id;
						$line->qty = GETPOST($qty, 'int');
						$line->entrepot_id = 0;
						if ($line->update($user) < 0) {
							setEventMessages($line->error, $line->errors, 'errors');
							$error++;
						}
						unset($_POST[$qty]);
					}
				}
			}
		}

		unset($_POST["lineid"]);

		if (!$error) {
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
				// Define output language
				$outputlangs = $langs;
				$newlang = '';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09'))
					$newlang = GETPOST('lang_id', 'aZ09');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang))
					$newlang = $exp->thirdparty->default_lang;
				if (!empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}

				$ret = $exp->fetch($exp->id); // Reload to get new records
				$exp->generateDocument($exp->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
		} else {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$exp->id); // To redisplay the form being edited
			exit();
		}
	} elseif ($action == 'updateline' && $user->rights->expedition->creer && GETPOST('cancel', 'alpha') == $langs->trans("Cancel")) {
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$exp->id); // To redisplay the form being edited
		exit();
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	if (empty($id)) $id = $facid;
	$triggersendname = 'SHIPPING_SENTBYMAIL';
	$paramname = 'id';
	$mode = 'emailfromshipment';
	$trackid = 'shi'.$exp->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 * View
 */

llxHeader('', $langs->trans('Shipment'), 'Expedition');

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);
if (!empty($conf->projet->enabled)) { $formproject = new FormProjets($db); }

$product_static = new Product($db);
$shipment_static = new Expedition($db);
$warehousestatic = new Entrepot($db);

if ($id || $ref)
/* *************************************************************************** */
/*                                                                             */
/* Edit and view mode                                                          */
/*                                                                             */
/* *************************************************************************** */
{
	$lines = $exp->lines;

	$num_prod = count($lines);

	if ($exp->id > 0)
	{
		if (!empty($exp->origin) && $exp->origin_id > 0)
		{
			$typeobject = $exp->origin;
			$origin = $exp->origin;
			$origin_id = $exp->origin_id;
			$exp->fetch_origin(); // Load property $exp->commande, $exp->propal, ...
		}

		$soc = new Societe($db);
		$soc->fetch($exp->socid);

		$res = $exp->fetch_optionals();

		$head = shipping_prepare_head($exp);
		print dol_get_fiche_head($head, 'shipping', $langs->trans("Shipment"), -1, 'sending');

		$formconfirm = '';

		// Confirm deleteion
		if ($action == 'delete')
		{
			$formquestion = array();
			if ($exp->statut == Expedition::STATUS_CLOSED && !empty($conf->global->STOCK_CALCULATE_ON_SHIPMENT_CLOSE)) {
				$formquestion = array(
						array(
							'label' => $langs->trans('ShipmentIncrementStockOnDelete'),
							'name' => 'alsoUpdateStock',
							'type' => 'checkbox',
							'value' => 0
						),
					);
			}
			$formconfirm = $form->formconfirm(
				$_SERVER['PHP_SELF'].'?id='.$exp->id,
				$langs->trans('DeleteSending'),
				$langs->trans("ConfirmDeleteSending", $exp->ref),
				'confirm_delete',
				$formquestion,
				0,
				1
			);
		}

		// Confirmation validation
		if ($action == 'valid')
		{
			$expref = substr($exp->ref, 1, 4);
			if ($expref == 'PROV')
			{
				$numref = $exp->getNextNumRef($soc);
			} else {
				$numref = $exp->ref;
			}

			$text = $langs->trans("ConfirmValidateSending", $numref);

			if (!empty($conf->notification->enabled))
			{
				require_once DOL_DOCUMENT_ROOT.'/core/class/notify.class.php';
				$notify = new Notify($db);
				$text .= '<br>';
				$text .= $notify->confirmMessage('SHIPPING_VALIDATE', $exp->socid, $exp);
			}

			$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$exp->id, $langs->trans('ValidateSending'), $text, 'confirm_valid', '', 0, 1);
		}
		// Confirm cancelation
		if ($action == 'cancel')
		{
			$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$exp->id, $langs->trans('CancelSending'), $langs->trans("ConfirmCancelSending", $exp->ref), 'confirm_cancel', '', 0, 1);
		}

		// Call Hook formConfirm
		$parameters = array('formConfirm' => $formconfirm);
		$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $exp, $action); // Note that $action and $exp may have been modified by hook
		if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
		elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

		// Print form confirm
		print $formconfirm;

		// Calculate totalWeight and totalVolume for all products
		// by adding weight and volume of each product line.
		$tmparray = $exp->getTotalWeightVolume();
		$totalWeight = $tmparray['weight'];
		$totalVolume = $tmparray['volume'];


		if ($typeobject == 'commande' && $exp->$typeobject->id && !empty($conf->commande->enabled))
		{
			$expsrc = new Commande($db);
			$expsrc->fetch($exp->$typeobject->id);
		}
		if ($typeobject == 'propal' && $exp->$typeobject->id && !empty($conf->propal->enabled))
		{
			$expsrc = new Propal($db);
			$expsrc->fetch($exp->$typeobject->id);
		}

		// Shipment card
		$linkback = '<a href="'.DOL_URL_ROOT.'/expedition/list.php?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';
		$morehtmlref = '<div class="refidno">';
		// Ref customer shipment
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_customer', $exp->ref_customer, $exp, $user->rights->expedition->creer, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_customer', $exp->ref_customer, $exp, $user->rights->expedition->creer, 'string', '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>'.$langs->trans('ThirdParty').' : '.$exp->thirdparty->getNomUrl(1);
		// Project
		if (!empty($conf->projet->enabled)) {
			$langs->load("projects");
			$morehtmlref .= '<br>'.$langs->trans('Project').' ';
			if (0) {    // Do not change on shipment
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&amp;id='.$exp->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> : ';
				}
				if ($action == 'classify') {
					// $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $exp->id, $exp->socid, $exp->fk_project, 'projectid', 0, 0, 1, 1);
					$morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$exp->id.'">';
					$morehtmlref .= '<input type="hidden" name="action" value="classin">';
					$morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
					$morehtmlref .= $formproject->select_projects($exp->socid, $exp->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
					$morehtmlref .= '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
					$morehtmlref .= '</form>';
				} else {
					$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$exp->id, $exp->socid, $exp->fk_project, 'none', 0, 0, 0, 1);
				}
			} else {
				// We don't have project on shipment, so we will use the project or source object instead
				// TODO Add project on shipment
				$morehtmlref .= ' : ';
				if (!empty($expsrc->fk_project)) {
					$proj = new Project($db);
					$proj->fetch($expsrc->fk_project);
					$morehtmlref .= '<a href="'.DOL_URL_ROOT.'/projet/card.php?id='.$expsrc->fk_project.'" title="'.$langs->trans('ShowProject').'">';
					$morehtmlref .= $proj->ref;
					$morehtmlref .= '</a>';
				} else {
					$morehtmlref .= '';
				}
			}
		}
		$morehtmlref .= '</div>';


		dol_banner_tab($exp, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border tableforfield" width="100%">';

		// Linked documents
		if ($typeobject == 'commande' && $exp->$typeobject->id && !empty($conf->commande->enabled))
		{
			print '<tr><td>';
			print $langs->trans("RefOrder").'</td>';
			print '<td colspan="3">';
			print $expsrc->getNomUrl(1, 'commande');
			print "</td>\n";
			print '</tr>';
		}
		if ($typeobject == 'propal' && $exp->$typeobject->id && !empty($conf->propal->enabled))
		{
			print '<tr><td>';
			print $langs->trans("RefProposal").'</td>';
			print '<td colspan="3">';
			print $expsrc->getNomUrl(1, 'expedition');
			print "</td>\n";
			print '</tr>';
		}

		// Date creation
		print '<tr><td class="titlefield">'.$langs->trans("DateCreation").'</td>';
		print '<td colspan="3">'.dol_print_date($exp->date_creation, "dayhour")."</td>\n";
		print '</tr>';

		// Delivery date planned
		print '<tr><td height="10">';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('DateDeliveryPlanned');
		print '</td>';

		if ($action != 'editdate_livraison') print '<td class="right"><a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editdate_livraison&amp;id='.$exp->id.'">'.img_edit($langs->trans('SetDeliveryDate'), 1).'</a></td>';
		print '</tr></table>';
		print '</td><td colspan="2">';
		if ($action == 'editdate_livraison')
		{
			print '<form name="setdate_livraison" action="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'" method="post">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="setdate_livraison">';
			print $form->selectDate($exp->date_delivery ? $exp->date_delivery : -1, 'liv_', 1, 1, '', "setdate_livraison", 1, 0);
			print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
			print '</form>';
		} else {
			print $exp->date_delivery ? dol_print_date($exp->date_delivery, 'dayhour') : '&nbsp;';
		}
		print '</td>';
		print '</tr>';

		// Weight
		print '<tr><td>';
		print $form->editfieldkey("Weight", 'trueWeight', $exp->trueWeight, $exp, $user->rights->expedition->creer);
		print '</td><td colspan="3">';

		if ($action == 'edittrueWeight')
		{
			print '<form name="settrueweight" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input name="action" value="settrueWeight" type="hidden">';
			print '<input name="id" value="'.$exp->id.'" type="hidden">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input id="trueWeight" name="trueWeight" value="'.$exp->trueWeight.'" type="text" class="width50">';
			print $formproduct->selectMeasuringUnits("weight_units", "weight", $exp->weight_units, 0, 2);
			print ' <input class="button" name="modify" value="'.$langs->trans("Modify").'" type="submit">';
			print ' <input class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'" type="submit">';
			print '</form>';
		} else {
			print $exp->trueWeight;
			print ($exp->trueWeight && $exp->weight_units != '') ? ' '.measuringUnitString(0, "weight", $exp->weight_units) : '';
		}

		// Calculated
		if ($totalWeight > 0)
		{
			if (!empty($exp->trueWeight)) print ' ('.$langs->trans("SumOfProductWeights").': ';
			print showDimensionInBestUnit($totalWeight, 0, "weight", $langs, isset($conf->global->MAIN_WEIGHT_DEFAULT_ROUND) ? $conf->global->MAIN_WEIGHT_DEFAULT_ROUND : -1, isset($conf->global->MAIN_WEIGHT_DEFAULT_UNIT) ? $conf->global->MAIN_WEIGHT_DEFAULT_UNIT : 'no');
			if (!empty($exp->trueWeight)) print ')';
		}
		print '</td></tr>';

		// Width
		print '<tr><td>'.$form->editfieldkey("Width", 'trueWidth', $exp->trueWidth, $exp, $user->rights->expedition->creer).'</td><td colspan="3">';
		print $form->editfieldval("Width", 'trueWidth', $exp->trueWidth, $exp, $user->rights->expedition->creer);
		print ($exp->trueWidth && $exp->width_units != '') ? ' '.measuringUnitString(0, "size", $exp->width_units) : '';
		print '</td></tr>';

		// Height
		print '<tr><td>'.$form->editfieldkey("Height", 'trueHeight', $exp->trueHeight, $exp, $user->rights->expedition->creer).'</td><td colspan="3">';
		if ($action == 'edittrueHeight')
		{
			print '<form name="settrueHeight" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input name="action" value="settrueHeight" type="hidden">';
			print '<input name="id" value="'.$exp->id.'" type="hidden">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input id="trueHeight" name="trueHeight" value="'.$exp->trueHeight.'" type="text" class="width50">';
			print $formproduct->selectMeasuringUnits("size_units", "size", $exp->size_units, 0, 2);
			print ' <input class="button" name="modify" value="'.$langs->trans("Modify").'" type="submit">';
			print ' <input class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'" type="submit">';
			print '</form>';
		} else {
			print $exp->trueHeight;
			print ($exp->trueHeight && $exp->height_units != '') ? ' '.measuringUnitString(0, "size", $exp->height_units) : '';
		}

		print '</td></tr>';

		// Depth
		print '<tr><td>'.$form->editfieldkey("Depth", 'trueDepth', $exp->trueDepth, $exp, $user->rights->expedition->creer).'</td><td colspan="3">';
		print $form->editfieldval("Depth", 'trueDepth', $exp->trueDepth, $exp, $user->rights->expedition->creer);
		print ($exp->trueDepth && $exp->depth_units != '') ? ' '.measuringUnitString(0, "size", $exp->depth_units) : '';
		print '</td></tr>';

		// Volume
		print '<tr><td>';
		print $langs->trans("Volume");
		print '</td>';
		print '<td colspan="3">';
		$calculatedVolume = 0;
		$volumeUnit = 0;
		if ($exp->trueWidth && $exp->trueHeight && $exp->trueDepth)
		{
			$calculatedVolume = ($exp->trueWidth * $exp->trueHeight * $exp->trueDepth);
			$volumeUnit = $exp->size_units * 3;
		}
		// If sending volume not defined we use sum of products
		if ($calculatedVolume > 0)
		{
			if ($volumeUnit < 50)
			{
				print showDimensionInBestUnit($calculatedVolume, $volumeUnit, "volume", $langs, isset($conf->global->MAIN_VOLUME_DEFAULT_ROUND) ? $conf->global->MAIN_VOLUME_DEFAULT_ROUND : -1, isset($conf->global->MAIN_VOLUME_DEFAULT_UNIT) ? $conf->global->MAIN_VOLUME_DEFAULT_UNIT : 'no');
			} else print $calculatedVolume.' '.measuringUnitString(0, "volume", $volumeUnit);
		}
		if ($totalVolume > 0)
		{
			if ($calculatedVolume) print ' ('.$langs->trans("SumOfProductVolumes").': ';
			print showDimensionInBestUnit($totalVolume, 0, "volume", $langs, isset($conf->global->MAIN_VOLUME_DEFAULT_ROUND) ? $conf->global->MAIN_VOLUME_DEFAULT_ROUND : -1, isset($conf->global->MAIN_VOLUME_DEFAULT_UNIT) ? $conf->global->MAIN_VOLUME_DEFAULT_UNIT : 'no');
			//if (empty($calculatedVolume)) print ' ('.$langs->trans("Calculated").')';
			if ($calculatedVolume) print ')';
		}
		print "</td>\n";
		print '</tr>';

		// Other attributes
		$cols = 2;
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

		print '</table>';

		print '</div>';
		print '<div class="fichehalfright">';
		print '<div class="ficheaddleft">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border centpercent tableforfield">';

		// Sending method
		print '<tr><td height="10">';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('SendingMethod');
		print '</td>';

		if ($action != 'editshipping_method_id') print '<td class="right"><a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?action=editshipping_method_id&amp;id='.$exp->id.'">'.img_edit($langs->trans('SetSendingMethod'), 1).'</a></td>';
		print '</tr></table>';
		print '</td><td colspan="2">';
		if ($action == 'editshipping_method_id')
		{
			print '<form name="setshipping_method_id" action="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'" method="post">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="setshipping_method_id">';
			$exp->fetch_delivery_methods();
			print $form->selectarray("shipping_method_id", $exp->meths, $exp->shipping_method_id, 1, 0, 0, "", 1);
			if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
			print '</form>';
		} else {
			if ($exp->shipping_method_id > 0)
			{
				// Get code using getLabelFromKey
				$code = $langs->getLabelFromKey($db, $exp->shipping_method_id, 'c_shipment_mode', 'rowid', 'code');
				print $langs->trans("SendingMethod".strtoupper($code));
			}
		}
		print '</td>';
		print '</tr>';

		// Tracking Number
		print '<tr><td class="titlefield">'.$form->editfieldkey("TrackingNumber", 'tracking_number', $exp->tracking_number, $exp, $user->rights->expedition->creer).'</td><td colspan="3">';
		print $form->editfieldval("TrackingNumber", 'tracking_number', $exp->tracking_url, $exp, $user->rights->expedition->creer, 'safehtmlstring', $exp->tracking_number);
		print '</td></tr>';

		// Incoterms
		if (!empty($conf->incoterm->enabled))
		{
			print '<tr><td>';
			print '<table width="100%" class="nobordernopadding"><tr><td>';
			print $langs->trans('IncotermLabel');
			print '<td><td class="right">';
			if ($user->rights->expedition->creer) print '<a class="editfielda" href="'.DOL_URL_ROOT.'/expedition/card.php?id='.$exp->id.'&action=editincoterm">'.img_edit().'</a>';
			else print '&nbsp;';
			print '</td></tr></table>';
			print '</td>';
			print '<td colspan="3">';
			if ($action != 'editincoterm')
			{
				print $form->textwithpicto($exp->display_incoterms(), $exp->label_incoterms, 1);
			} else {
				print $form->select_incoterms((!empty($exp->fk_incoterms) ? $exp->fk_incoterms : ''), (!empty($exp->location_incoterms) ? $exp->location_incoterms : ''), $_SERVER['PHP_SELF'].'?id='.$exp->id);
			}
			print '</td></tr>';
		}

		// Other attributes
		$parameters = array('colspan' => ' colspan="3"', 'cols' => '3');
		$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $exp, $action); // Note that $action and $exp may have been modified by hook
		print $hookmanager->resPrint;

		print "</table>";

		print '</div>';
		print '</div>';
		print '</div>';

		print '<div class="clearboth"></div>';


		// Lines of products

/*
		if ($action == 'editline')
		{
			print '	<form name="updateline" id="updateline" action="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;lineid='.$line_id.'" method="POST">
			<input type="hidden" name="token" value="' . newToken().'">
			<input type="hidden" name="action" value="updateline">
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="id" value="' . $exp->id.'">
			';
		}*/
		print '<br>';

		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder" width="100%" id="tablelines" >';
		print '<thead>';
		print '<tr class="liste_titre">';
		// Adds a line numbering column
		if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER))
		{
			print '<td width="5" class="center linecolnum">&nbsp;</td>';
		}
		// Product/Service
		print '<td  class="linecoldescription" >'.$langs->trans("Products").'</td>';
		// Qty
		print '<td class="center linecolqty">'.$langs->trans("QtyOrdered").'</td>';

		if ($exp->statut <= 1)
		{
			print '<td class="center linecolqtytoship">'.$langs->trans("QtyToShip").'</td>';
		} else {
			print '<td class="center linecolqtyshipped">'.$langs->trans("QtyShipped").'</td>';
		}
		if (!empty($conf->stock->enabled))
		{
			print '<td class="left linecolwarehousesource">'.$langs->trans("WarehouseSource").'</td>';
		}

		if (!empty($conf->productbatch->enabled))
		{
			print '<td class="left linecolbatch">'.$langs->trans("Batch").'</td>';
		}

		print '<td class="center linecolweight">'.$langs->trans("CalculatedWeight").'</td>';
		print '<td class="center linecolvolume">'.$langs->trans("CalculatedVolume").'</td>';
		//print '<td class="center">'.$langs->trans("Size").'</td>';
		if ($exp->statut == 0)
		{
			print '<td class="linecoledit"></td>';
			print '<td class="linecoldelete" width="10"></td>';
		}
		print "</tr>\n";
		print '</thead>';

		if (!empty($conf->global->MAIN_MULTILANGS) && !empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE))
		{
			$exp->fetch_thirdparty();
			$outputlangs = $langs;
			$newlang = '';
			if (empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
			if (empty($newlang)) $newlang = $exp->thirdparty->default_lang;
			if (!empty($newlang))
			{
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
		}

		print '<tbody>';

		// Loop on each product to send/sent
		for ($i = 0; $i < $num_prod; $i++)
		{
			$parameters = array('i' => $i, 'line' => $lines[$i], 'line_id' => $line_id, 'num' => $num_prod, 'editColspan' => $editColspan, 'outputlangs' => $outputlangs);
			$reshook = $hookmanager->executeHooks('printObjectLine', $parameters, $exp, $action);
			if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

			if (empty($reshook))
			{
				print '<!-- origin line id = '.$lines[$i]->origin_line_id.' -->'; // id of order line
				print '<tr class="oddeven" id="row-'.$lines[$i]->id.'" data-id="'.$lines[$i]->id.'" data-element="'.$lines[$i]->element.'" >';

				// #
				if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER))
				{
					print '<td class="center linecolnum">'.($i + 1).'</td>';
				}

				// Predefined product or service
				if ($lines[$i]->fk_product > 0)
				{
					// Define output language
					if (!empty($conf->global->MAIN_MULTILANGS) && !empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE))
					{
						$prod = new Product($db);
						$prod->fetch($lines[$i]->fk_product);
						$label = (!empty($prod->multilangs[$outputlangs->defaultlang]["label"])) ? $prod->multilangs[$outputlangs->defaultlang]["label"] : $lines[$i]->product_label;
					} else $label = (!empty($lines[$i]->label) ? $lines[$i]->label : $lines[$i]->product_label);

					print '<td class="linecoldescription">';

					// Show product and description
					$product_static->type = $lines[$i]->fk_product_type;
					$product_static->id = $lines[$i]->fk_product;
					$product_static->ref = $lines[$i]->ref;
					$product_static->status = $lines[$i]->product_tosell;
					$product_static->status_buy = $lines[$i]->product_tobuy;
					$product_static->status_batch = $lines[$i]->product_tobatch;

					$product_static->weight = $lines[$i]->weight;
					$product_static->weight_units = $lines[$i]->weight_units;
					$product_static->length = $lines[$i]->length;
					$product_static->length_units = $lines[$i]->length_units;
					$product_static->width = $lines[$i]->width;
					$product_static->width_units = $lines[$i]->width_units;
					$product_static->height = $lines[$i]->height;
					$product_static->height_units = $lines[$i]->height_units;
					$product_static->surface = $lines[$i]->surface;
					$product_static->surface_units = $lines[$i]->surface_units;
					$product_static->volume = $lines[$i]->volume;
					$product_static->volume_units = $lines[$i]->volume_units;

					$text = $product_static->getNomUrl(1);
					$text .= ' - '.$label;
					$description = (!empty($conf->global->PRODUIT_DESC_IN_FORM) ? '' : dol_htmlentitiesbr($lines[$i]->description));
					print $form->textwithtooltip($text, $description, 3, '', '', $i);
					print_date_range($lines[$i]->date_start, $lines[$i]->date_end);
					if (!empty($conf->global->PRODUIT_DESC_IN_FORM))
					{
						print (!empty($lines[$i]->description) && $lines[$i]->description != $lines[$i]->product) ? '<br>'.dol_htmlentitiesbr($lines[$i]->description) : '';
					}
					print "</td>\n";
				} else {
					print '<td class="linecoldescription" >';
					if ($lines[$i]->product_type == Product::TYPE_SERVICE) $text = img_object($langs->trans('Service'), 'service');
					else $text = img_object($langs->trans('Product'), 'product');

					if (!empty($lines[$i]->label)) {
						$text .= ' <strong>'.$lines[$i]->label.'</strong>';
						print $form->textwithtooltip($text, $lines[$i]->description, 3, '', '', $i);
					} else {
						print $text.' '.nl2br($lines[$i]->description);
					}

					print_date_range($lines[$i]->date_start, $lines[$i]->date_end);
					print "</td>\n";
				}

				// Qty ordered
				print '<td class="center linecolqty">'.$lines[$i]->qty_asked.'</td>';

				// Qty to ship or shipped
				print '<td class="linecolqtytoship center">'.$lines[$i]->qty_shipped.'</td>';

				// Warehouse source
				if (!empty($conf->stock->enabled))
				{
					print '<td class="linecolwarehousesource left">';
					if ($lines[$i]->entrepot_id > 0)
					{
						$entrepot = new Entrepot($db);
						$entrepot->fetch($lines[$i]->entrepot_id);
						print $entrepot->getNomUrl(1);
					} elseif (count($lines[$i]->details_entrepot) > 1)
					{
						$detail = '';
						foreach ($lines[$i]->details_entrepot as $detail_entrepot)
						{
							if ($detail_entrepot->entrepot_id > 0)
							{
								$entrepot = new Entrepot($db);
								$entrepot->fetch($detail_entrepot->entrepot_id);
								$detail .= $langs->trans("DetailWarehouseFormat", $entrepot->libelle, $detail_entrepot->qty_shipped).'<br/>';
							}
						}
						print $form->textwithtooltip(img_picto('', 'object_stock').' '.$langs->trans("DetailWarehouseNumber"), $detail);
					}
					print '</td>';
				}

				// Batch number managment
				if (!empty($conf->productbatch->enabled))
				{
					if (isset($lines[$i]->detail_batch))
					{
						print '<!-- Detail of lot -->';
						print '<td class="linecolbatch">';
						if ($lines[$i]->product_tobatch)
						{
							$detail = '';
							foreach ($lines[$i]->detail_batch as $dbatch)	// $dbatch is instance of ExpeditionLineBatch
							{
								$detail .= $langs->trans("Batch").': '.$dbatch->batch;
								if (empty($conf->global->PRODUCT_DISABLE_SELLBY)) {
									$detail .= ' - '.$langs->trans("SellByDate").': '.dol_print_date($dbatch->sellby, "day");
								}
								if (empty($conf->global->PRODUCT_DISABLE_EATBY)) {
									$detail .= ' - '.$langs->trans("EatByDate").': '.dol_print_date($dbatch->eatby, "day");
								}
								$detail .= ' - '.$langs->trans("Qty").': '.$dbatch->qty;
								$detail .= '<br>';
							}
							print $form->textwithtooltip(img_picto('', 'object_barcode').' '.$langs->trans("DetailBatchNumber"), $detail);
						} else {
							print $langs->trans("NA");
						}
						print '</td>';
					} else {
						print '<td class="linecolbatch" ></td>';
					}
				}


				// Weight
				print '<td class="center linecolweight">';
				if ($lines[$i]->fk_product_type == Product::TYPE_PRODUCT) print $lines[$i]->weight * $lines[$i]->qty_shipped.' '.measuringUnitString(0, "weight", $lines[$i]->weight_units);
				else print '&nbsp;';
				print '</td>';

				// Volume
				print '<td class="center linecolvolume">';
				if ($lines[$i]->fk_product_type == Product::TYPE_PRODUCT) print $lines[$i]->volume * $lines[$i]->qty_shipped.' '.measuringUnitString(0, "volume", $lines[$i]->volume_units);
				else print '&nbsp;';
				print '</td>';

				// Size
				//print '<td class="center">'.$lines[$i]->volume*$lines[$i]->qty_shipped.' '.measuringUnitString(0, "volume", $lines[$i]->volume_units).'</td>';

				if ($action == 'editline' && $lines[$i]->id == $line_id)
				{
					print '<td class="center" colspan="2" valign="middle">';
					print '<input type="submit" class="button button-save" id="savelinebutton marginbottomonly" name="save" value="'.$langs->trans("Save").'"><br>';
					print '<input type="submit" class="button button-cancel" id="cancellinebutton" name="cancel" value="'.$langs->trans("Cancel").'"><br>';
					print '</td>';
				} elseif ($exp->statut == Expedition::STATUS_DRAFT)
				{
					// edit-delete buttons
					print '<td class="linecoledit center">';
					print '<a class="editfielda reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=editline&amp;lineid='.$lines[$i]->id.'">'.img_edit().'</a>';
					print '</td>';
					print '<td class="linecoldelete" width="10">';
					print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=deleteline&amp;token='.newToken().'&amp;lineid='.$lines[$i]->id.'">'.img_delete().'</a>';
					print '</td>';

					// Display lines extrafields
					if (!empty($rowExtrafieldsStart))
					{
						print $rowExtrafieldsStart;
						print $rowExtrafieldsView;
						print $rowEnd;
					}
				}
				print "</tr>";

				// Display lines extrafields
				if (!empty($extrafields)) {
					$colspan = 6;
					if ($origin && $origin_id > 0) $colspan++;
					if (!empty($conf->productbatch->enabled)) $colspan++;
					if (!empty($conf->stock->enabled)) $colspan++;

					$line = $lines[$i];
					$line->fetch_optionals();

					if ($action == 'editline' && $line->id == $line_id)
					{
						print $lines[$i]->showOptionals($extrafields, 'edit', array('colspan'=>$colspan), $indiceAsked);
					} else {
						print $lines[$i]->showOptionals($extrafields, 'view', array('colspan'=>$colspan), $indiceAsked);
					}
				}
			}
		}

		// TODO Show also lines ordered but not delivered

		print "</table>\n";
		print '</tbody>';
		print '</div>';
	}


	print dol_get_fiche_end();


	$exp->fetchObjectLinked($exp->id, $exp->element);


	/*
	 *    Boutons actions
	 */

	if (($user->socid == 0) && ($action != 'presend'))
	{
		print '<div class="tabsAction">';

		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $exp, $action); // Note that $action and $exp may have been
																									   // modified by hook
		if (empty($reshook))
		{
			if ($exp->statut == Expedition::STATUS_DRAFT && $num_prod > 0)
			{
				if ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->expedition->creer))
	  			 || (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->expedition->shipping_advance->validate)))
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=valid">'.$langs->trans("Validate").'</a>';
				} else {
					print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans("Validate").'</a>';
				}
			}

			// TODO add alternative status
			// 0=draft, 1=validated, 2=billed, we miss a status "delivered" (only available on order)
			if ($exp->statut == Expedition::STATUS_CLOSED && $user->rights->expedition->creer)
			{
				if (!empty($conf->facture->enabled) && !empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT))  // Quand l'option est on, il faut avoir le bouton en plus et non en remplacement du Close ?
				{
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=reopen">'.$langs->trans("ClassifyUnbilled").'</a>';
				} else {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=reopen">'.$langs->trans("ReOpen").'</a>';
				}
			}

			// Send
			if (empty($user->socid)) {
				if ($exp->statut > 0)
				{
					if (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->expedition->shipping_advance->send)
					{
						print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a>';
					} else print '<a class="butActionRefused classfortooltip" href="#">'.$langs->trans('SendMail').'</a>';
				}
			}

			// Create bill
			if (!empty($conf->facture->enabled) && ($exp->statut == Expedition::STATUS_VALIDATED || $exp->statut == Expedition::STATUS_CLOSED))
			{
				if ($user->rights->facture->creer)
				{
					// TODO show button only   if (! empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT))
					// If we do that, we must also make this option official.
					print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture/card.php?action=create&amp;origin='.$exp->element.'&amp;originid='.$exp->id.'&amp;socid='.$exp->socid.'">'.$langs->trans("CreateBill").'</a>';
				}
			}

			// This is just to generate a delivery receipt
			//var_dump($exp->linkedObjectsIds['delivery']);
			if ($conf->delivery_note->enabled && ($exp->statut == Expedition::STATUS_VALIDATED || $exp->statut == Expedition::STATUS_CLOSED) && $user->rights->expedition->delivery->creer && empty($exp->linkedObjectsIds['delivery']))
			{
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=create_delivery">'.$langs->trans("CreateDeliveryOrder").'</a>';
			}
			// Close
			if ($exp->statut == Expedition::STATUS_VALIDATED)
			{
				if ($user->rights->expedition->creer && $exp->statut > 0 && !$exp->billed)
				{
					$label = "Close"; $paramaction = 'classifyclosed'; // = Transferred/Received
					// Label here should be "Close" or "ClassifyBilled" if we decided to make bill on shipments instead of orders
					if (!empty($conf->facture->enabled) && !empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT))  // Quand l'option est on, il faut avoir le bouton en plus et non en remplacement du Close ?
					{
						$label = "ClassifyBilled";
						$paramaction = 'classifybilled';
					}
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action='.$paramaction.'">'.$langs->trans($label).'</a>';
				}
			}

/*
			// Cancel
			if ($exp->statut == Expedition::STATUS_VALIDATED)
			{
				if ($user->rights->expedition->supprimer)
				{
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=cancel">'.$langs->trans("Cancel").'</a>';
				}
			}*/

			// Delete
			if ($user->rights->expedition->supprimer)
			{
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$exp->id.'&amp;action=delete&amp;token='.newToken().'">'.$langs->trans("Delete").'</a>';
			}
		}

		print '</div>';
	}


	/*
	 * Documents generated
	 */

	if ($action != 'presend' && $action != 'editline')
	{
		print '<div class="fichecenter"><div class="fichehalfleft">';

		$expref = dol_sanitizeFileName($exp->ref);
		$filedir = $conf->expedition->dir_output."/sending/".$expref;

		$urlsource = $_SERVER["PHP_SELF"]."?id=".$exp->id;

		$genallowed = $user->rights->expedition->lire;
		$delallowed = $user->rights->expedition->creer;

		print $formfile->showdocuments('expedition', $expref, $filedir, $urlsource, $genallowed, $delallowed, $exp->model_pdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);


		// Show links to link elements
		//$linktoelem = $form->showLinkToObjectBlock($exp, null, array('order'));
		$somethingshown = $form->showLinkedObjectBlock($exp, '');


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($exp, 'shipping', $socid, 1);

		print '</div></div></div>';
	}


	/*
	 * Action presend
	 */

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'shipping_send';
	$defaulttopic = 'SendShippingRef';
	$diroutput = $conf->expedition->dir_output.'/sending';
	$trackid = 'shi'.$exp->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
