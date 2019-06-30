<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       tourneedelivraison_card.php
 *		\ingroup    tourneesdelivraison
 *		\brief      Page to create/edit/view tourneedelivraison
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP','none');					// Disable all Content Security Policies
/*

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");*/

$arr=explode("/",$_SERVER["PHP_SELF" ]);
$typetournee = str_replace("_card.php","", $arr[count($arr)-1]);

if($typetournee == 'tourneedelivraison'){
	$typenom='TourneeDeLivraison';
}elseif ($typetournee == 'tourneeunique'){
	$typenom='TourneeUnique';
}else die("erreur type objet inconnu: $typetournee");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

dol_include_once('/tourneesdelivraison/class/html.formtourneesdelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/'.$typetournee.'.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/'.$typetournee.'_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/lib/tournee.lib.php');

$r=addCategorieData();

require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

// Load translation files required by the page
$langs->loadLangs(array("tourneesdelivraison@tourneesdelivraison","other","orders","sendings","bills","main"));
if (! empty($conf->categorie->enabled)) $langs->load("categories");

// Get parameters
$id			= GETPOST('id', 'int');
$lineid = GETPOST('lineid', 'int');
$contactid = GETPOST('contactid', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):$typetournee.'card';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
if( $typetournee == 'tourneedelivraison')  $object=new TourneeDeLivraison($db);
else $object = new TourneeUnique($db);

$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->tourneesdelivraison->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array($typetournee.'card','globalcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

foreach($object->lines as $line){
	if( ! empty(GETPOST('addcontact_'.$line->id))) {
		$action = 'addcontact';
		$lineid = $line->id;
		$contactid=GETPOST('addcontactid_'.$lineid);
		unset($_POST['addcontactid_'.$lineid]);
	}
	unset($_POST['addcontactid_'.$line->id]);
}


// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
$isdraft = (($object->statut == TourneeGeneric::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'tourneesdelivraison', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);




$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	if ($cancel) {
		if (! empty($backtopage)) {
			header("Location: ".$backtopage);
			exit;
		}
		$action='';
	}

	$error=0;

	$permissiontoadd = $user->rights->tourneesdelivraison->{$typetournee}->ecrire;
	$permissiontodelete = $user->rights->tourneesdelivraison->{$typetournee}->effacer || ($permissiontoadd && $object->status == 0);
	$permissiontonote = $user->rights->tourneesdelivraison->{$typetournee}->ecrire;
	$permissioncreate = $permissiontoadd;

  $backurlforlist = dol_buildpath('/tourneesdelivraison/'.$typetournee.'_list.php',1);

	if (empty($backtopage)) {
		if (empty($id)) $backtopage = $backurlforlist;
	  else $backtopage = dol_buildpath('/tourneesdelivraison/'.$typetournee.'_card.php',1).($id > 0 ? $id : '__ID__');
  }

	$triggermodname = 'TOURNEESDELIVRAISON_'.strtoupper($typetournee).'_MODIFY';	// Name of trigger action code to execute when we modify record


	if( $typetournee == 'tourneeunique' && $object->statut==TourneeGeneric::STATUS_VALIDATED && $object->date_tournee > time()) {
		$object->checkCommande($user);
	}



	// récupération de la liste de model pdf
	$modulepart='tourneesdelivraison';
	// For normalized standard modules
	 $file=dol_buildpath('/core/modules/'.$modulepart.'/modules_'.$modulepart.'.php',0);
	 if (file_exists($file)) {
		 $res=include_once $file;
	 } else {
	 // For normalized external modules
		 $file=dol_buildpath('/'.$modulepart.'/core/modules/'.$modulepart.'/modules_'.$modulepart.'.php',0);
		 $res=include_once $file;
	 }
	 $class='ModelePDF'.ucfirst($modulepart);
	 if (class_exists($class)) {
		 $modellist=call_user_func($class.'::liste_modeles',$object->db);
	 } else {
		 dol_print_error($this->db,'Bad value for modulepart');
		 return -1;
	 }



	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to build doc
	$upload_dir = $conf->tourneesdelivraison->dir_output . $type;
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

// (éventuelles) Annulation des avertissements éventuels
if( substr($action,0,4)=='ask_' && $conf->global->{'TOURNEESDELIVRAISON_ASK_'.mb_strtoupper(substr($action,4))}){
	$action='confirm_'.substr($action,4);
	$confirm='yes';
}

	// Affectation Automatique
	if( $typetournee=='tourneeunique' && $action == 'confirm_affectationauto' && $confirm == 'yes' && $object->statut == TourneeGeneric::STATUS_VALIDATED && $user->rights->tourneesdelivraison->tourneeunique->ecrire){
		if (! ($object->id > 0)) {
			dol_print_error('', 'Error, object must be fetched before');
			exit;
		}

		// paramètre généraux
		// 1 => actif   0 => inactif

		// sur l'objet
		// 0=> defaut 1=> inactif 2=> actif

		// en sortie
		// 1 => actif   0 => inactif

		if( empty($object->ae_1elt_par_cmde)||$object->ae_1elt_par_cmde == 0){
			$ae_1elt_par_cmde=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE==1?1:0);
		} else $ae_1elt_par_cmde=$object->ae_1elt_par_cmde-1;

		if( empty($object->ae_1ere_future_cmde)||$object->ae_1ere_future_cmde == 0){
			$ae_1ere_future_cmde=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE==1?1:0);
		} else $ae_1ere_future_cmde=$object->ae_1ere_future_cmde-1;

		if( empty($object->ae_datelivraisonidentique)||$object->ae_datelivraisonidentique == 0){
			$ae_datelivraisonidentique=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK==1?1:0);
		} else $ae_datelivraisonidentique=$object->ae_datelivraisonidentique-1;

		// paramètre généraux
		//  0 => inactif  1 => manuel  2 => manuel et auto
		// sur l'objet
		// 0=> defaut 1=> inactif 2=> manuel 3=> manuel et auto
		// en sortie
		//  0 => inactif  1 => manuel  2 => manuel et auto

		if( empty($object->change_date_affectation)||$object->change_date_affectation == 0){
			$change_date_affectation=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE==2?1:0);
		} else $change_date_affectation=($object->change_date_affectation==3?1:0);

		$result=$object->affectationAuto($user,
			$ae_datelivraisonidentique,
			$ae_1ere_future_cmde,
			$ae_1elt_par_cmde,
			$change_date_affectation
		);
		if ($result > 0) {
			// Delete OK
			$action='';
		} else {
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
		$action='';
	}


	else if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_genererdocs' && $permissioncreate){

		// Reload to get all modified line records and be ready for hooks
     $ret = $object->fetch($id);
     $ret = $object->fetch_thirdparty();


     $outputlangs = $langs;
     $newlang='';

     if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang=GETPOST('lang_id','aZ09');
     if ($conf->global->MAIN_MULTILANGS && empty($newlang) && isset($object->thirdparty->default_lang)) $newlang=$object->thirdparty->default_lang;  // for proposal, order, invoice, ...
     if ($conf->global->MAIN_MULTILANGS && empty($newlang) && isset($object->default_lang)) $newlang=$object->default_lang;                  // for thirdparty
     if (! empty($newlang))
     {
         $outputlangs = new Translate("",$conf);
         $outputlangs->setDefaultLang($newlang);
     }

     // To be sure vars is defined
     if (empty($hidedetails)) $hidedetails=0;
     if (empty($hidedesc)) $hidedesc=0;
     if (empty($hideref)) $hideref=0;
     if (empty($moreparams)) $moreparams=null;

				$result = $object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);

     if ($result <= 0){
       setEventMessages($object->error, $object->errors, 'errors');
       $action='';
     } else {
     	if (empty($donotredirect))	// This is set when include is done by bulk action "Bill Orders"
     	{
        setEventMessages($langs->trans("FileGenerated"), null);

        $urltoredirect = $_SERVER['REQUEST_URI'];
        $urltoredirect = preg_replace('/#builddoc$/', '', $urltoredirect);
        $urltoredirect = preg_replace('/action=confirm_genererdocs&?/', '', $urltoredirect);	// To avoid infinite loop

        header('Location: '.$urltoredirect.'#builddoc');
        exit;
     	}
		}
	}

	else if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_changedateelt'){
		$elt_type=GETPOST('elt_type', 'aZ09');
		$elt_id=GETPOST('elt_id', 'int');
		$elt_lineid=GETPOST('elt_lineid','int');

		echo "elt_type=$elt_type|elt_id=$elt_id|elt_lineid=$elt_lineid|lineid=$lineid|";

		$result = $object->changeDateEltToDateTournee($user, $lineid, $elt_type, $elt_lineid);
		if ($result > 0)
		{
			setEventMessages($langs->trans('DateEltModifiee', $elt_type), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'view';
		}
	}

	else if ($action == 'setnocmde_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
		$line=$object->getLineById($lineid);
		if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
		else {
			if( count($line->lines_cmde == 0)){
				$line->aucune_cmde=1;
				$line->update($user);
			} else { // A FAIRE -> générer erreur car déjà une commande sur la ligen on ne peut donc pas mettre Pas de Commande

			}
		}
		$action='view';
	}

	else if ($action == 'unsetnocmde_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
		$line=$object->getLineById($lineid);
		if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
		else {
			$line->aucune_cmde=0;
			$line->update($user);
		}
		$action='view';
	}

	else if($typetournee == 'tourneeunique' && $action=='supprimerTags' && $permissiontonote){
		//Catégories
		if (!empty($user->rights->categorie->lire))
		{
			// Categories association
			$categories = GETPOST( 'cats_suppr', 'array' );

			foreach ($categories as $c) {
				$cat=new Categorie($db);
				$cat->fetch($c);
				$f=$cat->get_filles();
				foreach ($f as $fc) {
					if( ! in_array($fc->id, $categories)) $categories[]=$fc->id;
				}
			}
			var_dump($categories);

			$object->supprimerCategoriesLines($categories);

			// A FAIRE
			$action='view';

		}
	}

	else if ($action == 'setnote_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
		$line=$object->getLineById($lineid);
		if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
		else {

		$result1=$line->update_note(dol_html_entity_decode(GETPOST('note_public_elt', 'none'), ENT_QUOTES),'_public');
		if ($result1 < 0) {
			$error++;
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$result2=$line->update_note(dol_html_entity_decode(GETPOST('note_private_elt', 'none'), ENT_QUOTES),'_private');
		if ($result2 < 0) {
			$error++;
			setEventMessages($object->error, $object->errors, 'errors');
		}

		//Catégories
		if (!empty($user->rights->categorie->lire))
		{
			// Categories association
			$categories = GETPOST( 'cats_line', 'array' );
			$result3 = $line->setCategories($categories, $object->element);
			if ($result3 < 0) {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}



		if ($result1 >= 0 || $result2 >= 0 || $result3 >= 0){
			if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
				$object->deleteAllDocuments();
			}

			if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
				// Define output language
				$outputlangs = $langs;
				$newlang = GETPOST('lang_id', 'alpha');
				if (! empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}

				$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
		}
	}
}

	else if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_changestatutelt'){
		$statut=GETPOST('statut', 'int');
		$elt_type=GETPOST('elt_type', 'aZ09');
		$elt_id=GETPOST('elt_id', 'int');
		$elt_lineid=GETPOST('elt_lineid','int');

		if( empty($object->change_date_affectation)||$object->change_date_affectation == 0){
			$change_date_affectation=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE>0);
		} else $change_date_affectation=($object->change_date_affectation>1);

		$result = $object->changeStatutElt($user, $lineid, $elt_type, $elt_lineid, $statut, $change_date_affectation);
		if ($result > 0)
		{
			setEventMessages($langs->trans('StatutEltModifiee', $elt_type), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'view';
		}
	}

	else if( (($action == 'set_ae_datelivraisonidentique'
						|| $action == 'set_ae_1ere_future_cmde'
						|| $action == 'set_ae_1elt_par_cmde'
						||$action == 'set_change_date_affectation'
						|| $action == 'set_date_tournee'
						|| $action == 'set_label'
						|| $action=='set_description')
					&& $object->statut==TourneeGeneric::STATUS_DRAFT && !empty($permissiontoadd)
					) || (
						$action == 'set_masque_ligne'
						&& $typetournee == 'tourneeunique'
						)
				){ // modification d'un paramètre

		if( $action == 'set_date_tournee'){
			$key=substr($action,4);
			$value=GETPOST($key.'year','int').'-'.GETPOST($key.'month','int').'-'.GETPOST($key.'day','int');
		} else {
			$key=substr($action,4);
			if( GETPOSTISSET($key)){
				$value=GETPOST($key,'aZ09');
			} else {
				$value=GETPOST('label', 'int');
			}
		}

		$object->{$key}=$value;

		$result = $object->update($user);

		if ($result > 0)
		{
			//setEventMessages($langs->trans($key.'Modifiee'), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'view';
		}

	}
	else if( $action=='createTourneeUnique' && $typetournee == 'tourneedelivraison' && $user->rights->tourneesdelivraison->{$typetournee}->lire && $user->rights->tourneesdelivraison->tourneeunique->ecrire){

		if (! ($object->id > 0)) {
			dol_print_error('', 'Error, object must be fetched before being create');
			exit;
		}
		$result=$object->createTourneeUnique($user);
		if ($result > 0)
		{
			// create tournee  unique OK
			setEventMessages("CreateTourneeUniqueFromTourneeDeLivraison", null, 'mesgs');
			header("Location: ".str_replace($typetournee,'tourneeunique',$_SERVER["PHP_SELF"]). '?action=edit&id=' . $result);
			exit;
		} else {
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
	}


	// Action to add record
	else if ($action == 'add' && ! empty($permissiontoadd))
	{
		foreach ($object->fields as $key => $val)
		{
			if (in_array($key, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat', 'fk_user_modif', 'import_key'))) continue;	// Ignore special fields

			// Set value to insert
			if (in_array($object->fields[$key]['type'], array('text', 'html'))) {
				$value = GETPOST($key,'none');
			} elseif ($object->fields[$key]['type']=='date') {
				$value = dol_mktime(12, 0, 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='datetime') {
				$value = dol_mktime(GETPOST($key.'hour'), GETPOST($key.'min'), 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='price') {
				$value = price2num(GETPOST($key));
			} else {
				$value = GETPOST($key,'alpha');
			}
			if (preg_match('/^integer:/i', $object->fields[$key]['type']) && $value == '-1') $value='';		// This is an implicit foreign key field
			if (! empty($object->fields[$key]['foreignkey']) && $value == '-1') $value='';					// This is an explicit foreign key field

			$object->$key=$value;
			if ($val['notnull'] > 0 && $object->$key == '' && is_null($val['default']))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv($val['label'])), null, 'errors');
			}
		}

		if (! $error)
		{
			$result=$object->create($user);
			if ($result > 0)
			{
				// Creation OK
				$action='view';

				// Categories association
				$custcats = GETPOST('custcats', 'array');
				$result = $object->setCategories($custcats);
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
			else
			{
				// Creation KO
				if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
				else  setEventMessages($object->error, null, 'errors');
				$action='create';
			}
		}
		else
		{
			$action='create';
		}
	}

	// Action to update record	// A FAIRE / MODIFIER
	else if ($action == 'update' && ! empty($permissiontoadd))
	{
		foreach ($object->fields as $key => $val)
		{
			if (! GETPOSTISSET($key)) continue;		// The field was not submited to be edited
			if (in_array($key, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat', 'fk_user_modif', 'import_key'))) continue;	// Ignore special fields

			// Set value to update
			if (in_array($object->fields[$key]['type'], array('text', 'html'))) {
				$value = GETPOST($key,'none');
			} elseif ($object->fields[$key]['type']=='date') {
				$value = dol_mktime(12, 0, 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='datetime') {
				$value = dol_mktime(GETPOST($key.'hour'), GETPOST($key.'min'), 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='price') {
				$value = price2num(GETPOST($key));
			} else {
				$value = GETPOST($key,'alpha');
			}
			if (preg_match('/^integer:/i', $object->fields[$key]['type']) && $value == '-1') $value='';		// This is an implicit foreign key field
			if (! empty($object->fields[$key]['foreignkey']) && $value == '-1') $value='';					// This is an explicit foreign key field

			$object->$key=$value;
			if ($val['notnull'] > 0 && $object->$key == '' && is_null($val['default']))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv($val['label'])), null, 'errors');
			}
		}

		if (! $error)
		{
			$result=$object->update($user);
			if ($result > 0)
			{
				$action='view';

				// Prevent categorie's emptying if a user hasn't rights $user->rights->categorie->lire (in such a case, post of 'custcats' is not defined)
				if (! $error && !empty($user->rights->categorie->lire))
				{
					// Categories association
					$categories = GETPOST( 'custcats', 'array' );
					$result = $object->setCategories($categories, $object->element);
					if ($result < 0)
					{
						$error++;
						setEventMessages($object->error, $object->errors, 'errors');
					}
				}
			}
			else
			{
				// Creation KO
				setEventMessages($object->error, $object->errors, 'errors');
				$action='edit';
			}
		}
		else
		{
			$action='edit';
		}
	}

	// Action to update one extrafield	// A FAIRE / MODIFIER
	else if ($action == "update_extras" && ! empty($permissiontoadd))
	{
		$object->fetch(GETPOST('id','int'));

		$attributekey = GETPOST('attribute','alpha');
		$attributekeylong = 'options_'.$attributekey;
		$object->array_options['options_'.$attributekey] = GETPOST($attributekeylong,' alpha');

		$result = $object->insertExtraFields(empty($triggermodname)?'':$triggermodname, $user);
		if ($result > 0)
		{
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'edit_extras';
		}
	}

	// Action to delete	// A FAIRE / MODIFIER
	else if ($action == 'confirm_delete' && ! empty($permissiontodelete))
	{
	    if (! ($object->id > 0))
	    {
		dol_print_error('', 'Error, object must be fetched before being deleted');
		exit;
	    }

		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".$backurlforlist);
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
	}

	// Action clone object	// A FAIRE / MODIFIER
	else if ($action == 'confirm_clone' && $confirm == 'yes' && ! empty($permissiontoadd))
	{
		if (1==0 && ! GETPOST('clone_content') && ! GETPOST('clone_receivers'))
		{
			setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
		}
		else
		{
			if ($object->id > 0)
			{
				// Because createFromClone modifies the object, we must clone it so that we can restore it later if error
				$orig = clone $object;

				$result=$object->createFromClone($user, $object->id);
				if ($result > 0)
				{
					$newid = 0;
					if (is_object($result)) $newid = $result->id;
					else $newid = $result;
					header("Location: ".$_SERVER['PHP_SELF'].'?id='.$newid."&action=edit");	// Open record of new object
					exit;
				}
				else
				{
					setEventMessages($object->error, $object->errors, 'errors');
					$object = $orig;
					$action='';
				}
			}
		}
	}

	// action validate / unvalidate / cancel / close
	else if( ($action=='confirm_validate' || $action=='confirm_unvalidate' || $action == 'confirm_cancel' || $action == 'confirm_close' || $action == 'confirm_reopen' ) && $confirm == 'yes' && !empty($permissiontoadd)){
		if( $object->id > 0) {
			if( $action == 'confirm_validate' && $object->statut == TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_VALIDATED;
			elseif( $action == 'confirm_unvalidate' && $object->statut == TourneeGeneric::STATUS_VALIDATED) $object->statut = TourneeGeneric::STATUS_DRAFT;
			elseif( $action == 'confirm_close' && $object->statut != TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_CLOSED;
			elseif( $action == 'confirm_reopen' && $object->statut != TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_VALIDATED;
			elseif( $action == 'confirm_cancel' ) $object->statut = TourneeGeneric::STATUS_CANCELED;
			else {
				setEventMessages($object->error, $object->errors, 'errors');
				$action='';
			}

			if( $action != '')
			{
				$result=$object->update($user);

				if ($result > 0) {
					$action='view';
				} else{
					// Creation KO
					setEventMessages($object->error, $object->errors, 'errors');
					$action='view';
				}
			}
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action='';
		}

	} else if( $action == 'addline' && !empty($permissiontoadd) ) {
		$langs->load('errors');
		$error = 0;

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		$tournee_line_type_thirdparty = GETPOST('tournee_line_type_thirdparty');
		$tournee_line_type_tournee = GETPOST('tournee_line_type_tournee');

		$type=-1;
		if( $tournee_line_type_tournee == 'tournee' )
		{
			$tourneeincluseid=GETPOST('tourneeincluseid', 'int');
			if( ! empty($tourneeincluseid) ) $type=TourneeGeneric_lines::TYPE_TOURNEE;
		}
		if( $tournee_line_type_thirdparty == 'thirdparty' )
		{
			$socid=GETPOST('socid', 'int');
			if( ! empty($socid) )  $type=TourneeGeneric_lines::TYPE_THIRDPARTY;
		}

		if($type >= 0){
			$BL=0;
			$BL1=GETPOST('BL1');
			$BL2=GETPOST('BL2');
			if( $BL1=='BL1') $BL++;
			if( $BL2=='BL2') $BL++;


			$facture=GETPOST('facture');
			if( $facture=='facture') $facture=1;
			else $facture=0;

			$etiquettes=GETPOST('etiquettes');
			if( $etiquettes=='etiquettes') $etiquettes=1;
			else $etiquettes=0;

			$infolivraison=GETPOST('infolivraison');
			$tempstheorique=GETPOST('tempstheorique');

			$fk_adresselivraison=GETPOST('adresselivraisonid');
			if( empty($fk_adresselivraison)) $fk_adresselivraison=0;

			$note_public=GETPOST('note_public');
			$note_private=GETPOST('note_private');

			$result = $object->addline($type, $socid, $tourneeincluseid, $BL, $facture, $etiquettes, $tempstheorique, $infolivraison, $fk_adresselivraison, $note_public, $note_private);

			if ($result > 0) {
				$ret = $object->fetch($object->id); // Reload to get new records

				//Catégories
				$cats_line = GETPOST('cats_line', 'array');
				$line=$object->getLineById($result);
				$result = $line->setCategories($cats_line);
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}

				if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
					$object->deleteAllDocuments();
				}

				if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
					// Define output language
					$outputlangs = $langs;
					$newlang = GETPOST('lang_id', 'alpha');
					if (! empty($newlang)) {
						$outputlangs = new Translate("", $conf);
						$outputlangs->setDefaultLang($newlang);
					}

					$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
				}
			}
			else
			{
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		unset($_POST['tournee_line_type_thirdparty']);
		unset($_POST['tournee_line_type_tournee']);
		unset($_POST['socid']);
		unset($_POST['tourneeincluseid']);
		unset($_POST['BL1']);
		unset($_POST['BL2']);
		unset($_POST['facture']);
		unset($_POST['etiquettes']);
		unset($_POST['infolivraison']);
		unset($_POST['note_public']);
		unset($_POST['note_private']);
		unset($_POST['tempstheorique']);
		unset($_POST['cats_line']);
	}

	else if( $action == 'confirm_deleteline' && $confirm == 'yes' && !empty($permissiontoadd) )
	{
		$result = $object->deleteline($user, $lineid);

		if ($result > 0) {
			// Define output language
			$outputlangs = $langs;
			$newlang = '';

			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09'))
				$newlang = GETPOST('lang_id','aZ09');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))
				$newlang = $object->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
				$object->deleteAllDocuments();
			}
			if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {
				$ret = $object->fetch($object->id); // Reload to get new records
				$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}

			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;

		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	else if( ($action == 'updateline'|| substr($action,0,9) == 'editline_') && !empty($permissiontoadd) )
	{
		$langs->load('errors');
		$error = 0;

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		$tournee_line_type_thirdparty = GETPOST('tournee_line_type_thirdparty');
		$tournee_line_type_tournee = GETPOST('tournee_line_type_tournee');

		$type=-1;
		if( $tournee_line_type_tournee == 'tournee' ) {
			$tourneeincluseid=GETPOST('tourneeincluseid', 'int');
			if( ! empty($tourneeincluseid) ) $type=TourneeGeneric_lines::TYPE_TOURNEE;
		}
		if( $tournee_line_type_thirdparty == 'thirdparty' ){
			$socid=GETPOST('socid', 'int');
			if( ! empty($socid) )  $type=TourneeGeneric_lines::TYPE_THIRDPARTY;
		}

		if($type >= 0 && ! empty($lineid)){

			foreach($object->lines as $line) {
				if( $line->rowid == $lineid) $ret=$lineid;
			}

			if( $ret > 0){ // la ligne à modifier existe bien et appartient bien à cette objet
				$BL=0;
				$BL1=GETPOST('BL1');
				$BL2=GETPOST('BL2');
				if( $BL1=='BL1') $BL++;
				if( $BL2=='BL2') $BL++;


				$facture=GETPOST('facture');
				if( $facture=='facture') $facture=1;
				else $facture=0;

				$etiquettes=GETPOST('etiquettes');
				if( $etiquettes=='etiquettes') $etiquettes=1;
				else $etiquettes=0;

				$infolivraison=GETPOST('infolivraison');
				$tempstheorique=GETPOST('tempstheorique');

				$fk_adresselivraison=GETPOST('adresselivraisonid');
				if( empty($fk_adresselivraison)) $fk_adresselivraison=0;

				$note_public=GETPOST('note_public');
				$note_private=GETPOST('note_private');

				$result = $object->updateline($lineid, $type, $socid, $tourneeincluseid, $BL, $facture, $etiquettes, $tempstheorique, $infolivraison, $fk_adresselivraison, $note_public, $note_private);

				if ($result > 0) {
					$ret = $object->fetch($object->id); // Reload to get new records

					// Catégories
					// Prevent categorie's emptying if a user hasn't rights $user->rights->categorie->lire (in such a case, post of 'custcats' is not defined)
					if (!empty($user->rights->categorie->lire))
					{
						$line=$object->getLineById($result);
						// Categories association
						$categories = GETPOST( 'cats_line', 'array' );
						$result = $line->setCategories($categories, $object->element);
						if ($result < 0)
						{
							$error++;
							setEventMessages($object->error, $object->errors, 'errors');
						}
					}
					if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
						$object->deleteAllDocuments();
					}
					if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
						// Define output language
						$outputlangs = $langs;
						$newlang = GETPOST('lang_id', 'alpha');
						if (! empty($newlang)) {
							$outputlangs = new Translate("", $conf);
							$outputlangs->setDefaultLang($newlang);
						}

						$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
					}
				}
				else
				{
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
			else
			{	// la ligne à modifier n'existe pas ou n'appartient pas à cette objet

			}
		}
		$action='';

		unset($_POST['tournee_line_type_thirdparty']);
		unset($_POST['tournee_line_type_tournee']);
		unset($_POST['socid']);
		unset($_POST['tourneeincluseid']);
		unset($_POST['BL1']);
		unset($_POST['BL2']);
		unset($_POST['facture']);
		unset($_POST['etiquettes']);
		unset($_POST['infolivraison']);
		unset($_POST['note_private']);
		unset($_POST['note_public']);
		unset($_POST['tempstheorique']);
		unset($_POST['cats_line']);
	}

	else if ( $action =='addcontact' && !empty($permissiontoadd) ){
		$langs->load('errors');
		$error = 0;

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		$result = $object->addcontactline($lineid, 0, $contactid);

		if ($result > 0) {
			$ret = $object->fetch($object->id); // Reload to get new records
			if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
				$object->deleteAllDocuments();
			}
			if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
				// Define output language
				$outputlangs = $langs;
				$newlang = GETPOST('lang_id', 'alpha');
				if (! empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}

				$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action='';
	}

	else if ( $action =='confirm_deletecontact' && !empty($permissiontoadd) ){

		$langs->load('errors');
		$error = 0;

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		$result = $object->deletecontactline($user, $contactid);


		if ($result > 0) {
			$ret = $object->fetch($object->id); // Reload to get new records
			if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
				$object->deleteAllDocuments();
			}
			if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
				// Define output language
				$outputlangs = $langs;
				$newlang = GETPOST('lang_id', 'alpha');
				if (! empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}

				$object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action='';
	}


	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emailsurneedelivraison_card.php?id=2&action=confirm_validate
	$trigger_name=strtoupper($typetournee).'_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_'.strtoupper($typetournee).'_TO';
	$trackid=$typetournee.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}

/*
 * View
 *
 * Put here all code to build page
 */

$form=new Form($db);
$formtournee=new FormTourneesDeLivraison($db);
$formfile=new FormFile($db);

llxHeader('',$typenom,'');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
	function onClickMenuStatut(ID){
		if( jQuery("#"+ID).css("display")==\'none\'){
			jQuery(".menuStatutLine").attr("style","display:none");
			jQuery("#"+ID).attr("style","display:block;");
		} else {
			jQuery(".menuStatutLine").attr("style","display:none");
		}
	}
</script>
<style>
.tourneeBoutons{
	font-size:0.7em;
}
</style>
';

// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv($typenom)));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent">'."\n";

	// Common attributes
	// include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Libellé
	$object->field_create('ref');

	// Libellé
	$object->field_create('label');

	//description
	$object->field_create('description');


	// Distance totale
	$object->field_create('km');

	// Durée du trajet
	$object->field_create('dureeTrajet');

	// date de la tournée (si tournée unique)
	if( $typetournee == 'tourneeunique'){
		$object->field_create('date_tournee');
	}

	// Categories
	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
			$langs->load('categories');


			print '<tr class="visibleifcustomer"><td class="toptd">' . fieldLabel($object->nomelement . 'CategoriesShort', 'custcats') . '</td><td colspan="3">';
			$cate_arbo = $form->select_all_categories($object->element, null, 'parent', null, null, 1);
			print $form->multiselectarray('custcats', $cate_arbo, GETPOST('custcats', 'array'), null, null, null, null, "90%");
			print "</td></tr>";
		}

	print '</table>';
	print '<div class="titre inline-block">' . $langs->trans('ReglesAutoAffectation') . '</div>';
	print '<table class="border centpercent">'."\n";


	// règle d'autoaffectation

	$object->field_create('ae_datelivraisonidentique', 'create', '50%');
	$object->field_create('ae_1ere_future_cmde', 'create', '50%');
	$object->field_create('ae_1elt_par_cmde', 'create', '50%');
	$object->field_create('change_date_affectation', 'create', '50%');
	$object->field_create('statut');

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '<input type="hidden" name="masque_ligne" value="0">';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans($typenom));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	dol_fiche_head();

	print '<table class="border centpercent">'."\n";

	// Common attributes
	//include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';
	// Libellé
	if( empty(fk_tourneedelivraison)) $object->field_create('ref', 'edit');
	else $object->field_view('ref');

	// Libellé
	$object->field_create('label', 'edit');

	//description
	$object->field_create('description', 'edit');


	// Distance totale
	$object->field_create('km', 'edit');

	// Durée du trajet
	$object->field_create('dureeTrajet', 'edit');

	// date de la tournée et référence à la tournée de livraison liée (si tournée unique)
	if( $typetournee == 'tourneeunique'){
		$object->field_create('date_tournee', 'edit');
		$object->field_view('fk_tourneedelivraison');
	}

	// Categories
	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
			$langs->load('categories');

			// Customer
			print '<tr class=""><td>' . fieldLabel($object->nomelement . 'CategoriesShort', 'custcats') . '</td>';
			print '<td colspan="3">';
			$cate_arbo = $form->select_all_categories($object->element, null, null, null, null, 1);
			$c = new Categorie($db);
			$cats = $c->containing($object->id, $object->element);
			$arrayselected=array();
			foreach ($cats as $cat) {
				$arrayselected[] = $cat->id;
			}
			print $form->multiselectarray('custcats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
			print "</td></tr>";
		}

	print '</table>';
	print '<div class="titre inline-block">' . $langs->trans('ReglesAutoAffectation') . '</div>';
	print '<table class="border centpercent">'."\n";


	// règle d'autoaffectation

	$object->field_create('ae_datelivraisonidentique', 'edit', '50%');
	$object->field_create('ae_1ere_future_cmde', 'edit', '50%');
	$object->field_create('ae_1elt_par_cmde', 'edit', '50%');
	$object->field_create('change_date_affectation', 'edit', '50%');
	$object->field_create('statut');

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '<input type="hidden" name="masque_ligne" value="'.$object->masque_ligne.'">';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
    $res = $object->fetch_optionals();

	$head = tourneePrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans($typenom), -1, $typetournee.'@tourneesdelivraison');

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

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/tourneesdelivraison/'.$typetournee.'_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref='<div class="refidno">';
	//$morehtmlref.=$form->editfieldkey("RefBis", 'label', $object->label, $object, $permissiontoadd, 'string', '', 0, 1);
	/*
	// Ref bis
	$morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $permissiontoadd 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $permissiontoadd, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
	// Project
	if (! empty($conf->projet->enabled))
	{
	    $langs->load("projects");
	    $morehtmlref.='<br>'.$langs->trans('Project') . ' ';
	    if ($permissiontoadd)
	    {
	        if ($action != 'classify')
	            $morehtmlref.='<a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
            if ($action == 'classify') {
                //$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
                $morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
                $morehtmlref.='<input type="hidden" name="action" value="classin">';
                $morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
                $morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', 0, 0, 1, 0, 1, 0, 0, '', 1);
                $morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
                $morehtmlref.='</form>';
            } else {
                $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	        }
	    } else {
	        if (! empty($object->fk_project)) {
	            $proj = new Project($db);
	            $proj->fetch($object->fk_project);
	            $morehtmlref.=$proj->getNomUrl();
	        } else {
	            $morehtmlref.='';
	        }
	    }
	}
	*/
	$morehtmlref.='</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswithonsecondcolumn';
	//include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

	// Libellé
	$object->field_view('label', true);

	// Distance totale
	$object->field_view('km');

	// Durée du trajet
	$object->field_view('dureeTrajet');

	// date de la tournée (si tournée unique)
	if( $typetournee == 'tourneeunique'){
		$object->field_view('date_tournee', true);
		if( !empty($object->fk_tourneedelivraison)) $object->field_view('fk_tourneedelivraison', false);
	}

	//description
	$object->field_view('description', true);

	// règle d'autoaffectation

	$object->field_view('ae_datelivraisonidentique', true);
	$object->field_view('ae_1ere_future_cmde', true);
	$object->field_view('ae_1elt_par_cmde', true);
	$object->field_view('change_date_affectation', true);

	// Catégories
	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
			print '<tr><td>' . $langs->trans($object->nomelement . "CategoriesShort") . '</td>';
			print '<td>';
			print $form->showCategories($object->id, $object->element, 1);
			print "</td></tr>";
		}

// action à traiter
//setdatetournee

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	if( $typetournee == 'tourneeunique' && $object->statut!=TourneeGeneric::STATUS_DRAFT){
		$act=$action;
		$action="edit_masque_ligne";
		$object->field_view('masque_ligne', true);
		$action=$act;
	}

	print '</table>';
	print '</div>';
	print '</div>';


	print '<div class="clearboth"></div><br>';


	/*
	 * Lines
	 */
	$result = $object->getLinesArray();


	//print '	<form name="addline" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid')) . '" method="POST">
	if( $object->statut!=TourneeGeneric::STATUS_VALIDATED){
		print '	<form name="addline" id="addline" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '&action=addline' : '&action=editline_' . GETPOST('lineid')) . '" method="POST">
		<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="id" value="' . $object->id . '">
		';
	}


	//if (! empty($conf->use_javascript_ajax) && $object->statut == TourneeGeneric::STATUS_DRAFT) {
	if (! empty($conf->use_javascript_ajax)) {
		include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow" width="100%">';

	// Show object lines
	if (! empty($object->lines))
		if( $action=='editline') $lineid=GETPOST('lineid');
		$ret = $object->printTourneeLines($action,$mysoc,(($action=='editline'||$action=='edit_note_elt')?$lineid:0));

	$numlines = count($object->lines);

	/*
	 * Form to add new line
	 */


	if ($object->statut == TourneeGeneric::STATUS_DRAFT && !empty($permissiontoadd) && $action != 'selectlines')
	{
		if ($action != 'editline')
		{
			// Add free products/services
			$object->formAddTourneeLine($mysoc);

			$parameters = array();
			$reshook = $hookmanager->executeHooks('formAddTourneLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		}
	}

	$object->printRecap();

	print '</table>';
	print '</div>';
	if( $object->statut != TourneeGeneric::STATUS_VALIDATED) print '</form>';


	dol_fiche_end();


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
    	print '<div class="tabsAction">'."\n";
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');



    	if (empty($reshook))
    	{


		if( $typetournee == 'tourneeunique'){
			// boutons spécifiques TourneeUnique

			if( $object->statut==TourneeGeneric::STATUS_VALIDATED ){
				if(!empty($permissiontoadd)){
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_affectationauto">' . $langs->trans("AffectationAuto") . '</a></div>';
				} else {
					print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('AffectationAuto').'</a>'."\n";
				}

			if( $user->rights->tourneesdelivraison->{$typetournee}->mailer ){
				print '<a class="butAction" href="' . $object->mailtoToAll() . '">' . $langs->trans('SendMailToAll') . '</a>'."\n";		// A FAIRE
			} else {
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('SendMailToAll').'</a>'."\n";
			}

			if($user->rights->tourneesdelivraison->{$typetournee}->lire){
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_genererdocs">' . $langs->trans("GenererDocs") . '</a></div>';
			} else {
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('GenererDocs').'</a>'."\n";
			}
		}


	} else {	// si pas tournee unique
			// Send Mail
			if ($user->rights->tourneesdelivraison->{$typetournee}->mailer && $object->statut==TourneeGeneric::STATUS_VALIDATED ){
				//print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";		// A FAIRE
				print '<a class="butAction" href="' . $object->mailtoToAll() . '">' . $langs->trans('SendMailToAll') . '</a>'."\n";
			}
			else{
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('SendMailToAll').'</a>'."\n";
			}
		}





		// Valider
		if( $object->statut==TourneeGeneric::STATUS_DRAFT ){
			if (!empty($permissiontoadd))
			{
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_validate">' . $langs->trans("Validate") . '</a></div>';
			}
			else
			{
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Validate').'</a>'."\n";
			}
		}

		if($object->statut==TourneeGeneric::STATUS_VALIDATED){

			// Modifier
			if (!empty($permissiontoadd))
			{
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_unvalidate">'.$langs->trans("Modify").'</a>'."\n";
			}
			else
			{
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
			}

			// créer tournée unique
			if( $typetournee=='tourneedelivraison'){
				if ($user->rights->tourneesdelivraison->{$typetournee}->lire && $user->rights->tourneesdelivraison->tourneeunique->ecrire){
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=createTourneeUnique&amp;origine=tourneedelivraison&amp;id='.$object->id.'">' . $langs->trans("CreerTournee") . '</a></div>';
				}
				else
				{
					print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('CreerTournee').'</a>'."\n";
				}
			}
		}

    		// Clore / Cloner / Annuler
    		if (!empty($permissiontoadd))
    		{
			if($object->statut!=TourneeGeneric::STATUS_CLOSED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_close">'.$langs->trans('Close').'</a></div>'."\n";
			else print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_reopen">'.$langs->trans('Reopen').'</a></div>'."\n";

			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=ask_clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_cancel">'.$langs->trans('Cancel').'</a>'."\n";
    		}
		else
		{
			if($object->statut!=TourneeGeneric::STATUS_CLOSED)  print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Reopen').'</a>'."\n";
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Cancel').'</a>'."\n";
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
		}

		// effacer
    		if ($permissiontodelete)
    		{
    			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_delete">'.$langs->trans('Delete').'</a>'."\n";
    		}
    		else
    		{
    			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
    		}
    	}
    	print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend')
	{
	    print '<div class="fichecenter"><div class="fichehalfleft">';
	    print '<a name="builddoc"></a>'; // ancre

	    // Documents
	    $objref = dol_sanitizeFileName($object->ref);
	    $filedir = $conf->tourneesdelivraison->dir_output . "/$typetournee/" . $objref;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->tourneesdelivraison->{$typetournee}->lire;	// If you can read, you can build the PDF to read content
	    $delallowed = $permissiontoadd;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments("tourneesdelivraison", "$typetournee/$objref", $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang,'',$object);


	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array($typetournee));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/tourneesdelivraison/'.$typetournee.'_info.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, $typetournee, $socid, 1, '', $MAXEVENT, '', $morehtmlright);

	    print '</div></div></div>';
	}

	//Select mail models is same action as presend
	/*
	 if (GETPOST('modelselected')) $action = 'presend';

	 // Presend form
	 $modelmail='inventory';
	 $defaulttopic='InformationMessage';
	 $diroutput = $conf->product->dir_output.'/inventory';
	 $trackid = 'stockinv'.$object->id;

	 include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
	 */
}

// End of page
llxFooter();
$db->close();
?>
