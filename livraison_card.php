<?php
/* Copyright (C)
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


if( !function_exists(dol_banner_tab)){
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
	if (! $res) die("Include of main fails");
}

$arr=explode("/",$_SERVER["PHP_SELF" ]);

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('/tourneesdelivraison/class/html.formtourneesdelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines.class.php');
dol_include_once('/tourneesdelivraison/class/TourneeUnique_lines_cmde.class.php');
dol_include_once('/tourneesdelivraison/class/TourneeUnique_lines_cmde_elt.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');

dol_include_once('/tourneesdelivraison/lib/livraison.lib.php');
dol_include_once('/tourneesdelivraison/lib/tournee.lib.php');


$r=addCategorieData();

require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

// Load translation files required by the page
$langs->loadLangs(array("tourneesdelivraison@tourneesdelivraison","other","orders","sendings","bills","main"));
if (! empty($conf->categorie->enabled)) $langs->load("categories");

// Get parameters
if( GETPOSTISSET('le')){
	$hash = GETPOST('h','aZ09');
	$le = GETPOST('le','int');
	$carton = GETPOST('c','int');

	$params = "h=$hash&le=$le&c=$carton";

	$lelt=new TourneeUnique_lines_cmde_elt($db);
	$lelt->fetch($le);
	$lelt->fetch_optionals();

	$lcmde=$lelt->getParent();

	$line=$lcmde->getParent();
	$lineid=$line->id;
} else {
	$lineid=GETPOST('lineid','int');
	$line=new TourneeUnique_lines($db);
	$line->fetch($lineid);
	$line->fetch_optionals();

	$params = "lineid=$lineid";
}

$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'livraisoncard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$lcmde_id = GETPOST('lcmde_id','int');
$lelt_id=GETPOST('lelt_id','int');

// Initialize technical objects
$object=$line;



$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->tourneesdelivraison->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('livraisoncard','globalcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

// Initialize array of search criterias
/*
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';*/


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
	  else $backtopage = dol_buildpath('/tourneesdelivraison/livraison_card.php',1).($id > 0 ? $id : '__ID__');
  }

	$triggermodname = 'TOURNEESDELIVRAISON_'.strtoupper($typetournee).'_MODIFY';	// Name of trigger action code to execute when we modify record




	// Actions when printing a doc from card
	//include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to build doc
	//$upload_dir = $conf->tourneesdelivraison->dir_output . $type;
	//include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	// (éventuelles) Annulation des avertissements éventuels
	if( substr($action,0,4)=='ask_' && $conf->global->{'TOURNEESDELIVRAISON_ASK_'.mb_strtoupper(substr($action,4))}){
		$action='confirm_'.substr($action,4);
		$confirm='yes';
	}

	if( ! empty($lcmde_id)){
		if ($action == 'confirm_cmde_classifybilled' && $confirm == 'yes' && $user->rights->commande->creer ) {
			$ret=$line->getCmdelineByLineId($lcmde_id)->loadElt()->classifyBilled($user);

			if ($ret < 0) {
				setEventMessages($cmde->error, $cmde->errors, 'errors');
			}
		}
		else if ($action == 'confirm_cmde_classifyunbilled' && $confirm == 'yes' && $user->rights->commande->creer) {
			$ret=$line->getCmdelineByLineId($lcmde_id)->loadElt()->classifyUnBilled($user);
			if ($ret < 0) {
				setEventMessages($cmde->error, $cmde->errors, 'errors');
			}
		}
		else if ($action == 'confirm_cmde_shipped' && $confirm == 'yes' && $user->rights->commande->cloturer) {
			$ret=$line->getCmdelineByLineId($lcmde_id)->loadElt()->cloture($user);

			if ($ret < 0) {
				setEventMessages($cmde->error, $cmde->errors, 'errors');
			}
		}
		else if ($action == 'confirm_cmde_reopen' && $confirm == 'yes' && $user->rights->commande->creer) {
			$cmde=$line->getCmdelineByLineId($lcmde_id)->loadElt();

			if ($cmde->statut == Commande::STATUS_CANCELED || $cmde->statut == Commande::STATUS_CLOSED) {
				$result = $cmde->set_reopen($user);
				if ($result > 0) {
					setEventMessages($langs->trans('OrderReopened', $cmde->ref), null);
				} else {
					setEventMessages($cmde->error, $cmde->errors, 'errors');
				}
			}
		}
	}
	// Reopen
	if( !empty($lelt_id)){
		if( substr($action,0,12)=='confirm_exp_'){
			$element=$line->getEltById('shipping', $lelt_id)->loadElt();

			if ($action == 'confirm_exp_reopen' && $confirm == 'yes' && $user->rights->expedition->creer) {
			    $result = $element->reOpen();
					if ($result > 0) {
						setEventMessages($langs->trans('ExpeditionReopened', $element->ref), null);
					} else {
						setEventMessages($element->error, $element->errors, 'errors');
					}
			}
			elseif ($action == 'confirm_exp_shipped' && $confirm == 'yes' && $user->rights->expedition->creer) {
			  $result = $element->setClosed();
			  if($result >= 0) {
			  	header('Location: ' . $_SERVER["PHP_SELF"] . "?$params");
			  	exit();
			  } else {
					setEventMessages($element->error, $element->errors, 'errors');
				}
			}
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

llxHeader('',$langs->trans('Livraison'),'');

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


// Part to show record
if ($lineid > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
  $res = $object->fetch_optionals();

	$head = livraisonPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans('Livraison'), -1, 'livraison@tourneesdelivraison');

	$formconfirm = '';


	if( substr($action,0,4) == 'ask_' ){
		$act=substr($action,4);
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . "?".$params."&lcmde_id=$lcmde_id&lelt_id=$lelt_id", $langs->trans(ucfirst($act).'Livraison'), $langs->trans('Confirm'.ucfirst($act).'Livraison'), 'confirm_'.$act, $formquestion, 'yes', 1);
	}


	// Call Hook formConfirm
	$parameters = array('carton' => $carton,'lelt_id'=>$lelt_id, 'lineid'=>$lcmde->id);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/tourneesdelivraison/livraison_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $object->getThirdParty()->getNomUrl(1);
	if( !empty($object->fk_adresselivraison )) {
		$contact=new Contact($db);
		$contact->fetch($object->fk_adresselivraison);
		$morehtmlref.='<br>'.$contact->getBannerAddress('refaddress',$contact);
	}
	else $morehtmlref.='<br>'.$object->getThirdParty()->getBannerAddress('refaddress',$object->getThirdParty());
	$morehtmlref.='</div>';

	dol_banner_tab($line, 'lineid', $linkback, 1, 'id', 'ref_arret', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";


	// temps theorique de livraison
	$object->field_view('tpstheorique');
	$object->field_view('fk_tournee');

	// Catégories
	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
			print '<tr><td>' . $langs->trans($object->nomelement . "CategoriesShort") . '</td>';
			print '<td>';
			print $form->showCategories($object->id, $object->element, 1);
			print "</td></tr>";
		}



	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';


	print '</table>';
	print '</div>';
	print '</div>';


	print '<div class="clearboth"></div><br>';


	/*
	 * Lines
	 */



	//if (! empty($conf->use_javascript_ajax) && $object->statut == TourneeGeneric::STATUS_DRAFT) {
	if (! empty($conf->use_javascript_ajax)) {
		include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow" width="100%">';


	foreach ($object->lines_cmde as $lcmde) {
		if($lcmde->statut != TourneeUnique_lines_cmde::DATE_OK && $lcmde->statut != TourneeUnique_lines_cmde::DATE_NON_OK) continue;

		print "<thead><tr  class=\"liste_titre nodrag nodrop\"><td>";
		print $lcmde->loadElt()->getNomUrl() . $lcmde->loadElt()->getLibStatut(3);
		print "</td><td>";

		$total=$lcmde->getTotalWeightVolume("commande");
		if (!empty($total['weight'])) print showDimensionInBestUnit($total['weight'], 0, "weight", $outputlangs) . '<br>';
		if (!empty($total['volume'])) print showDimensionInBestUnit($total['volume'], 0, "volume", $outputlangs);

		print "</td><td>";

		// Reopen a closed order
		if ( $lcmde->loadElt()->statut==Commande::STATUS_CLOSED && $user->rights->commande->creer) {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?' . $params . '&amp;action=ask_cmde_reopen&amp;lcmde_id='.$lcmde->id.'">' . $langs->trans('ReOpen') . '</a></div>';
		}
		// Set to shipped
		if ($lcmde->loadElt()->statut==Commande::STATUS_VALIDATED && $user->rights->commande->cloturer) {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?' . $params . '&amp;action=ask_cmde_shipped&amp;lcmde_id='.$lcmde->id.'">' . $langs->trans('ClassifyShipped') . '</a></div>';
		}


		// Create bill and Classify billed
		// Note: Even if module invoice is not enabled, we should be able to use button "Classified billed"
		if( $lcmde->nbFacture()>0 && $user->rights->commande->creer && empty($conf->global->WORKFLOW_DISABLE_CLASSIFY_BILLED_FROM_ORDER) && empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT)) {
			if( empty($lcmde->loadElt()->billed)){
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?' . $params . '&amp;action=ask_cmde_classifybilled&amp;lcmde_id='.$lcmde->id.'">' . $langs->trans("ClassifyBilled") . '</a></div>';
			} else {
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?' . $params . '&amp;action=ask_cmde_classifyunbilled&amp;lcmde_id='.$lcmde->id.'">' . $langs->trans("ClassifyUnBilled") . '</a></div>';
			}
		}

		print "</tr></thead><td><tr>";


		foreach ($lcmde->lines as $lelt) {
			if( $lelt->type_element != 'shipping' ) continue;
			if( $lelt->statut != TourneeUnique_lines_cmde_elt::DATE_OK && $lelt->statut != TourneeUnique_lines_elt::DATE_NON_OK) continue;

			if( empty($livree) || empty($non_livree)){
				$exp=new Expedition($db);
				$exp->fetch($lelt->fk_elt);
				if( $exp->statut ==Expedition::STATUS_CLOSED) $livree=1;
				else $non_livree=1;
			}

			print "<table>";

			print "<thead><tr><td>";
			print $lelt->loadElt()->getNomUrl() . $lelt->loadElt()->getLibStatut(3);
			print "</td><td>";

			$total=$lelt->getTotalWeightVolume("shipping");
			if (!empty($total['weight'])) print showDimensionInBestUnit($total['weight'], 0, "weight", $outputlangs) . '<br>';
			if (!empty($total['volume'])) print showDimensionInBestUnit($total['volume'], 0, "volume", $outputlangs);

			print "</td><td>";

			// Reopen a closed order
			if( $user->rights->commande->creer ){
				if ( $lelt->loadElt()->statut==Expedition::STATUS_CLOSED  ) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?' . $params . '&amp;action=ask_exp_reopen&amp;lelt_id='.$lelt->id.'">' . $langs->trans('ReOpen') . '</a></div>';
				} else {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?' . $params . '&amp;action=ask_exp_shipped&amp;lelt_id='.$lelt->id.'">' . $langs->trans('ClassifyShipped') . '</a></div>';
				}
			}

			print "</td></tr></thead>";
			$lelt->printListeCarton();

			print "</table>";

		}

		print "</td></tr>";
	}




/*
	// Show object lines
	if (! empty($object->lines))
		if( $action=='editline') $lineid=GETPOST('lineid');
		$ret = $object->printTourneeLines($action,$mysoc,(($action=='editline'||$action=='edit_note_elt')?$lineid:0));

	$numlines = count($object->lines);
*/



	print '</table>';
	print '</div>';


	dol_fiche_end();


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
    	print '<div class="tabsAction">'."\n";
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');



    	if (empty($reshook))
    	{

				if( !empty($hash)){
					$params="h=$hash&le=$lelt_id&c=$carton";
			} else {
					$params="lineid=$lineid";
			}



			}

    	print '</div>'."\n";
	}

}

// End of page
llxFooter();
$db->close();
?>
