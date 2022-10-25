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

dol_include_once('/tourneesdelivraison/class/html.formexp.class.php');

dol_include_once('/tourneesdelivraison/class/html.formtourneesdelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/'.$typetournee.'.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/'.$typetournee.'_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/lib/tournee.lib.php');

$r=addCategorieData();

dol_include_once('/categorie/class/categorie.class.php');

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
		$no_email = empty(GETPOST('addcontactid_'.$lineid.'_noemail'))?0:1;
		$sms = empty(GETPOST('addcontactid_'.$lineid.'_sms'))?0:1;

		unset($_POST['addcontactid_'.$lineid]);
		unset($_POST['addcontactid_'.$lineid.'_noemail']);
		unset($_POST['addcontactid_'.$lineid.'_sms']);
	}
	unset($_POST['addcontactid_'.$line->id]);
}


// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
$isdraft = (($object->statut == TourneeGeneric::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'tourneesdelivraison', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

// activer ajax (ou pas)
$ajaxActif = true;
if( GETPOSTISSET('noAjax') || $typetournee == 'tourneedelivraison') $ajaxActif = false;



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


	$date=getdate();


	if( $typetournee == 'tourneeunique' && ! $ajaxActif){
		if( $object->statut==TourneeGeneric::STATUS_VALIDATED && $object->date_tournee >= mktime(0,0,0,$date['mon'], getdate['mday'], getdate['year'])) {
		// si date tournée unique non dépassé, cherche les nouvelles commandes
			$object->checkCommande($user);
		} else {
			// sinon ne cherche que les nouveaux éléments (livraisons et factures correspondant aux commande déjà affectés)
			$object->checkElt($user);
		}
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
		 dol_print_error($db,'Bad value for modulepart');
		 return -1;
	 }

// préparation des tags clients/fournisseur à exclure
$listeParam=array("TOURNEESDELIVRAISON_CATEGORIES_CLIENT_A_NE_PAS_AFFICHER"=>'categoriesClientExclure',
									"TOURNEESDELIVRAISON_CATEGORIES_FOURNISSEUR_A_NE_PAS_AFFICHER"=>'categoriesFournisseurExclure',
									"TOURNEESDELIVRAISON_CATEGORIES_CONTACT_A_NE_PAS_AFFICHER"=>'categoriesContactExclure',
									"TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_COMMANDE" => 'categoriesLineCmdeExclure',
								);
foreach($listeParam as $param => $cat){
	if(strpos($conf->global->{$param},'|' === false )){
		$data=$conf->global->{$param};
	} else {
		$data=substr($conf->global->{$param}, strpos($conf->global->{$param},'|')+1);
	}
	$$cat=explode(',',$data);
}
// $categoriesFournisseurExclure

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to build doc
	$upload_dir = $conf->tourneesdelivraison->dir_output . $type;
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';



	$includes=array('/tourneesdelivraison/actions_suppravertiss.php',
									'/tourneesdelivraison/actions_tournees.php',
									'/tourneesdelivraison/actions_elts.php',
									'/tourneesdelivraison/actions_lines.php',
									'/tourneesdelivraison/actions_contacts.php',
								);

	foreach($includes as $tpl){
		$tpl=dol_buildpath($tpl);

		if (empty($conf->file->strict_mode)) {
			@include $tpl;
		} else {
			include $tpl; // for debug
		}
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

$form=new FormExp($db);
$formtournee=new FormTourneesDeLivraison($db);
$formfile=new FormFile($db);

$morejs=array();

llxHeader('',$typenom,'','','','',$morejs,'',0,0);

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
if ($action == 'create') {
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
if (($id || $ref) && $action == 'edit') {
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
		if( !empty($object->fk_tourneedelivraison)){
			$object->field_create('date_prochaine', 'edit');
			$object->field_view('fk_tourneedelivraison');
		} else {
			// a modifier
			$object->field_view('fk_tourneedelivraison');
		}
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


	$includes=array('/tourneesdelivraison/actions_confirmations.php',
								);

	foreach($includes as $tpl){
		$tpl=dol_buildpath($tpl);

		if (empty($conf->file->strict_mode)) {
			@include $tpl;
		} else {
			include $tpl; // for debug
		}
	}

	// Print form confirm
	echo '<div id="formulaireConfirm">';
	print $formconfirm;
	echo '</div>';

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
		if( !empty($object->fk_tourneedelivraison)) {
			$object->field_view('fk_tourneedelivraison', false);
			$object->field_view('date_prochaine');
		}
	} else if($typetournee == 'tourneedelivraison'){
		$object->field_view('date_prochaine');
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
		// Output template part (modules that overwrite templates must declare this into descriptor)
		$dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl'));
		foreach($dirtpls as $reldir)
		{
			$tpl = dol_buildpath($reldir.'/ajaxrow-tournee.tpl.php');
			if (empty($conf->file->strict_mode)) {
				$res=@include $tpl;
			} else {
				$res=include $tpl; // for debug
			}
			if ($res) break;
		}

		// Output template part (modules that overwrite templates must declare this into descriptor)
		$dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl'));
		foreach($dirtpls as $reldir)
		{
			$tpl = dol_buildpath($reldir.'/tournee_ajax.tpl.php');
			if (empty($conf->file->strict_mode)) {
				$res=@include $tpl;
			} else {
				$res=include $tpl; // for debug
			}
			if ($res) break;
		}
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow" width="100%">';
	//print '<div style="transform: scale(0.8,0.8);">';

	// Show object lines
	if (! empty($object->lines))
		if( $action=='editline') $lineid=GETPOST('lineid');
		$ret = $object->printTourneeLines($action,$mysoc,(($action=='editline'||$action=='edit_note_elt')?$lineid:0), 0, true);

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

	//print '</div>';
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
					print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_affectationauto">' . $langs->trans("AffectationAuto") . '</a></div>';
				} else {
					print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('AffectationAuto').'</a>'."\n";
				}

			if( $user->rights->tourneesdelivraison->{$typetournee}->mailer ){
				print '<a class="butAction" href="' . $object->mailtoToAll() . '">' . $langs->trans('SendMailToAll') . '</a>'."\n";		// A FAIRE
			} else {
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('SendMailToAll').'</a>'."\n";
			}

			if($user->rights->tourneesdelivraison->{$typetournee}->lire){
				print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_genererdocs">' . $langs->trans("GenererDocs") . '</a></div>';
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
				print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=ask_validate">' . $langs->trans("Validate") . '</a></div>';
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
				print '<a class="butAction askAjaxable" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_unvalidate">'.$langs->trans("Modify").'</a>'."\n";
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
			if($object->statut!=TourneeGeneric::STATUS_CLOSED) print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_close">'.$langs->trans('Close').'</a></div>'."\n";
			else print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_reopen">'.$langs->trans('Reopen').'</a></div>'."\n";

			print '<div class="inline-block divButAction"><a class="butAction askAjaxable" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=ask_clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
			print '<a class="butActionDelete askAjaxable" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_cancel">'.$langs->trans('Cancel').'</a>'."\n";
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
    			print '<a class="butActionDelete askAjaxable" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_delete">'.$langs->trans('Delete').'</a>'."\n";
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
