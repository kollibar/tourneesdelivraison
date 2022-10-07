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


// Load translation files required by the page
$langs->loadLangs(array("tourneesdelivraison@tourneesdelivraison","other","orders","sendings","bills","main"));

// Get parameters
$tourneeid			= GETPOST('id', 'int');
$lineid = GETPOST('lineid', 'int');
$contactid = GETPOST('contactid', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):$typetournee.'card';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

$noCheck = GETPOST('noCheck','alpha');

$statutTournee = GETPOST('statutTournee', 'int');

// Initialize technical objects
if( $typetournee == 'tourneedelivraison')  $object=new TourneeDeLivraison($db);
else $object = new TourneeUnique($db);

if( empty($object) && ! empty($lineid) ){
	$sql = 'SELECT `fk_tournee` FROM ' . MAIN_DB_PREFIX . $typetournee . '_lines WHERE `rowid` = ' . $lineid;

	dol_syslog("/tourneesdelivraison/ajax/tournee_card.php", LOG_DEBUG);
	$result = $db->query($sql);
	if ($result)
	{
		$num = $db->num_rows($result);
		if( i> 0 ){
			$objp = $db->fetch_object($result);
			$tourneeid = $objp->fk_tournee;
		}

		$db->free($result);
	}
	/*
	else
	{
		$this->error=$db->error();
		return -3;
	}*/
}

if( empty($statutTournee) || empty($dateTournee)){
	$sql = 'SELECT statut, date_tournee FROM ' . MAIN_DB_PREFIX . $typetournee . ' WHERE `rowid`=' . $tourneeid;

	dol_syslog("/tourneesdelivraison/ajax/tournee_card.php", LOG_DEBUG);
	$result = $db->query($sql);
	if ($result)
	{
		if( $db->num_rows($result) > 0 ){
			$objp = $db->fetch_object($result);
			$statutTournee = $objp->statut;
			$dateTournee = $objp->date_tournee;
		}
		$db->free($result);
	}
	/*
	else
	{
		$this->error=$db->error();
		return -3;
	}*/
}


// Initialize technical objects
if( $typetournee == 'tourneedelivraison')  $object=new TourneeDeLivraison_lines($db);
else $object = new TourneeUnique_lines($db);

$id=$lineid;

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
$line=$object;
unset($object);

if( $typetournee == 'tourneedelivraison')  $object=new TourneeDeLivraison($db);
else $object = new TourneeUnique($db);

$object->miniLoad($tourneeid, $statutTournee);
$line->tournee=$object;

/*
if( $typetournee == 'tourneeunique' ){
	if( empty($noCheck) || $noCheck != true ){
		if( $statutTournee == TourneeGeneric::STATUS_VALIDATED && $dateTournee >= mktime(0,0,0,$date['mon'], getdate['mday'], getdate['year'])) {
			$line->checkCommande($user, $dateTournee);
		}
		$line->checkElt($user);
	}
}
*/

/*
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
		$no_email = empty(GETPOST('addcontactid_'.$lineid.'_noemail'))?0:1;
		$sms = empty(GETPOST('addcontactid_'.$lineid.'_sms'))?0:1;

		unset($_POST['addcontactid_'.$lineid]);
		unset($_POST['addcontactid_'.$lineid.'_noemail']);
		unset($_POST['addcontactid_'.$lineid.'_sms']);
	}
	unset($_POST['addcontactid_'.$line->id]);
}*/


// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
$isdraft = (($object->statut == TourneeGeneric::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'tourneesdelivraison', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);


$permissiontoadd = $user->rights->tourneesdelivraison->{$typetournee}->ecrire;
$permissiontodelete = $user->rights->tourneesdelivraison->{$typetournee}->effacer || ($permissiontoadd && $object->status == 0);
$permissiontonote = $user->rights->tourneesdelivraison->{$typetournee}->ecrire;
$permissioncreate = $permissiontoadd;

$form=new Form($db);
$formtournee=new FormTourneesDeLivraison($db);
$formfile=new FormFile($db);


$includes=array('/tourneesdelivraison/actions_suppravertiss.php',
                '/tourneesdelivraison/actions_ajax.php',
								'/tourneesdelivraison/actions_tournees.php',
								'/tourneesdelivraison/actions_elts.php',
								'/tourneesdelivraison/actions_lines.php',
								'/tourneesdelivraison/actions_contacts.php',
								'/tourneesdelivraison/actions_confirmations.php',
							);

foreach($includes as $tpl){
	$tpl=dol_buildpath($tpl);

	if (empty($conf->file->strict_mode)) {
		@include $tpl;
	} else {
		include $tpl; // for debug
	}
}
if( substr($action,0,4) === "ask_" && ! empty($formconfirm) ){
	print $formconfirm;
} else if( $action == 'createline'){
	$object->formAddTourneeLine($mysoc);
} else {
	$var = GETPOST('var', 'int');
	if( empty($var) ) $var=0;
	$i = GETPOST('i', 'int');
	$num = GETPOST('num', 'int');

	if( empty($num) || empty($i) ) $object->printTourneeLineUnique_fetchLines($lineid, $action, $seller, (($action=='editline'||$action=='edit_note_elt')?$lineid:0), 0, false, false);
	else $object->printTourneeLineUnique($action, $line, $var, $num, $i, $mysoc, (($action=='editline'||$action=='edit_note_elt')?$lineid:0), 0, false, false);
}
