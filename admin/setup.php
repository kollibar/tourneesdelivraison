<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019 Thomas Kolli <thomas@brasserieteddybeer.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    tourneesdelivraison/admin/setup.php
 * \ingroup tourneesdelivraison
 * \brief   TourneesDeLivraison setup page.
 */

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
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/tourneesdelivraison.lib.php';
require_once '../lib/tournee.lib.php';
require_once '../lib/tournee_setup.lib.php';
//dol_include_once('/tourneesdelivraison/lib/tournee_setup.lib.php');

dol_include_once('/tourneesdelivraison/core/modules/tourneesdelivraison/modules_tourneesdelivraison.php');
dol_include_once('/categorie/class/categorie.class.php');
dol_include_once('/tourneesdelivraison/class/html.formexp.class.php');
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "tourneesdelivraison@tourneesdelivraison","other"));
$langs->load('categories');

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$value=GETPOST('value','alpha');
$label = GETPOST('label','alpha');
$scandir = GETPOST('scan_dir','alpha');
$type='tourneesdelivraison';

$arrayofparameters=array(
	'TOURNEESDELIVRAISON_MYPARAM1'=>array('css'=>'minwidth200','enabled'=>1),
	'TOURNEESDELIVRAISON_MYPARAM2'=>array('css'=>'minwidth500','enabled'=>1)
);


/*
 * Actions
 */
if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}

if ($action == 'specimen')
{
	$modele=GETPOST('module','alpha');

	$obj = new TourneeUnique($db);
	$obj->initAsSpecimen();

	// Search template files
	$file=''; $classname=''; $filefound=0;
	$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);
	foreach($dirmodels as $reldir)
	{
	    $file=dol_buildpath($reldir."core/modules/tourneesdelivraison/doc/pdf_".$modele.".modules.php",0);
		if (file_exists($file))
		{
			$filefound=1;
			$classname = "pdf_".$modele;
			break;
		}
	}

	if ($filefound)
	{
		require_once $file;

		$module = new $classname($db);

		if ($module->write_file($obj,$langs) > 0)
		{
			header("Location: ".DOL_URL_ROOT."/document.php?modulepart=tourneesdelivraison&file=SPECIMEN.pdf");
			return;
		}
		else
		{
			setEventMessages($module->error, $module->errors, 'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	}
	else
	{
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
}

// Activate a model
else if ($action == 'set')
{
	$ret = addDocumentModel($value, $type, $label, $scandir);
}
else if ($action == 'del')
{
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
        if ($conf->global->TOURNEESDELIVRAISON_ADDON_PDF == "$value") dolibarr_del_const($db, 'TOURNEESDELIVRAISON_ADDON_PDF',$conf->entity);
	}
}
// Set default model
else if ($action == 'setdoc')
{
	if (dolibarr_set_const($db, "TOURNEESDELIVRAISON_ADDON_PDF",$value,'chaine',0,'',$conf->entity))
	{
		// La constante qui a ete lue en avant du nouveau set
		// on passe donc par une variable pour avoir un affichage coherent
		$conf->global->TOURNEESDELIVRAISON_ADDON_PDF = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
}

if( $action == 'setavertissement'){
	$avertissement = GETPOST('avertissement','aZ09');
	$value = GETPOST('value','int');

	$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_ASK_".mb_strtoupper($avertissement), $value,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;
	if (! $error)
	{
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
	else
	{
			setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

$actions=array(
	// commande 		=>     VARIABLE DE CONFIGURATION À MODIFIER
	'setpdfautodelete' => 'TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE',
	'setpdfautoupdate' => 'TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE',
	'setcontactintegre' => "TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE",
	'setafficheinfofacture' => 'TOURNEESDELIVRAISON_AFFICHER_INFO_FACTURES',
	'setpoidsbl' => "TOURNEESDELIVRAISON_POIDS_BL",
	'setaffectautodateok' => "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK",
	'setaffectautosi1eltparcmde' => "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE",
	'setaffectauto1erecmde' => "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE",
	'setsms' => "TOURNEESDELIVRAISON_SMS",
	'setnotesupprimerentrecrochet' => 'TOURNEESDELIVRAISON_NOTE_SUPPRIMER_ENTRE_CROCHET_COMMANDE',
	'setautorisereditiontag' => 'TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG',
	'setchargerpagevide' => 'TOURNEESDELIVRAISON_CHARGER_PAGE_VIDE',
	'set1erchargementsanstag' => 'TOURNEESDELIVRAISON_1ER_CHARGEMENT_SANS_TAG',
	'setsignatureelec' => 'TOURNEESDELIVRAISON_ACTIVER_SIGNATURE_ELECTRONIQUE',
);


if( array_key_exists($action, $actions) && GETPOSTISSET('value')){
    $value = GETPOST('value','int');
    $res = dolibarr_set_const($db, $actions[$action], $value,'yesno',0,'',$conf->entity);
    if (! $res > 0) $error++;
    if (! $error)
    {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
    else
    {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}


if( $action == 'setup_reduc_qrcode' && GETPOSTISSET('value')){
	if( !empty(GETPOST('value','int'))){
		if( strpos($_SERVER['PHP_SELF'], '/custom/') === FALSE){
			$url_origin = '/tourneesdelivraison/livraison_card.php';
			$url_replace = '/tdl.php';
		} else {
			$url_origin = '/custom/tourneesdelivraison/livraison_card.php';
			$url_replace = '/tdl.php';
		}
		$res=copy(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'setup.php')).'tdl.php', DOL_DOCUMENT_ROOT.'/tdl.php');
		if( $res ){
			$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_ORIGIN", $url_origin,'chaine',0,'',$conf->entity);
			if (! $res > 0) $error++;
			$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_REPLACE", $url_replace,'chaine',0,'',$conf->entity);
			if (! $res > 0) $error++;
			if (! $error)
			{
					setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
			}
			else
			{
					setEventMessages($langs->trans("Error"), null, 'errors');
			}
		} else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	} else {	// suppression
		if (file_exists(DOL_DOCUMENT_ROOT.'/tdl.php')){
			$url_origin = '';
			$url_replace = '';
			$res=unlink(DOL_DOCUMENT_ROOT.'/tdl.php');
			if( $res ){
				$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_ORIGIN", $url_origin,'chaine',0,'',$conf->entity);
				if (! $res > 0) $error++;
				$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_REPLACE", $url_replace,'chaine',0,'',$conf->entity);
				if (! $res > 0) $error++;
				if (! $error)
				{
						setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
				}
				else
				{
						setEventMessages($langs->trans("Error"), null, 'errors');
				}
			} else {
				setEventMessages($langs->trans("Error"), null, 'errors');
			}
		}
	}
}

if ($action == 'updateoptions') {
	if (GETPOSTISSET('TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE')) {
		$changeautodate = GETPOST('activate_TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE','alpha');
		$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE", $changeautodate,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
		if (! $error)
	    {
		    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	    }
	    else
	    {
		    setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}

	if (GETPOSTISSET("URL_QRCODE")){
		$url_origin=GETPOST('url_origin','nohtml');
		$url_replace=GETPOST('url_replace','nohtml');
		$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_ORIGIN", $url_origin,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
		$res = dolibarr_set_const($db, "TOURNEESDELIVRAISON_URL_REPLACE", $url_replace,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
		if (! $error)
		{
				setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else
		{
				setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}

	$listeParam=array('TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_BORDEREAU',
										'TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_COMMANDE',
										'TOURNEESDELIVRAISON_CATEGORIES_CLIENT_A_NE_PAS_AFFICHER',
										'TOURNEESDELIVRAISON_CATEGORIES_FOURNISSEUR_A_NE_PAS_AFFICHER',
										'TOURNEESDELIVRAISON_CATEGORIES_CONTACT_A_NE_PAS_AFFICHER',
										'TOURNEESDELIVRAISON_TAG_CLIENT_GESTION_BL',
										'TOURNEESDELIVRAISON_TAG_CLIENT_FACTURE_MAIL',
										'TOURNEESDELIVRAISON_TAG_CLIENT_SANS_SIGNATURE_ELECTRONIQUE'
									 );

	foreach ($listeParam as $param) {
		if( ! GETPOSTISSET($param)) continue;

		$cats = GETPOST('cats_' . $param, 'array');

		$r=getListeCategoriesDependant($cats);

		if( $r != -1 ) $data = implode(',',$cats) . '|' . implode(',',$r);
		else $data=implode(',',$cats);


		$res = dolibarr_set_const($db, $param, $data,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
		if (! $error)
		{
				setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else
		{
				setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}
}

/*
 * View
 */

$page_name = "TourneesDeLivraisonSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = tourneesdelivraisonAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "tourneesdelivraison@tourneesdelivraison");

// Setup page goes here
echo $langs->trans("TourneesDeLivraisonSetupPage").'<br><br>';

/*
if ($action == 'edit')
{
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

	foreach($arrayofparameters as $key => $val)
	{
		print '<tr class="oddeven"><td>';
		print $form->textwithpicto($langs->trans($key),$langs->trans($key.'Tooltip'));
		print '</td><td><input name="'.$key.'"  class="flat '.(empty($val['css'])?'minwidth200':$val['css']).'" value="' . $conf->global->$key . '"></td></tr>';
	}
	print '</table>';

	print '<br><div class="center">';
	print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
	print '</div>';

	print '</form>';
	print '<br>';
}
else
{
	if (! empty($arrayofparameters))
	{
		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

		foreach($arrayofparameters as $key => $val)
		{
			print '<tr class="oddeven"><td>';
			print $form->textwithpicto($langs->trans($key),$langs->trans($key.'Tooltip'));
			print '</td><td>' . $conf->global->$key . '</td></tr>';
		}

		print '</table>';

		print '<div class="tabsAction">';
		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit">'.$langs->trans("Modify").'</a>';
		print '</div>';
	}
	else
	{
		print '<br>'.$langs->trans("NothingToSetup");
	}
}
*/







print load_fiche_titre($langs->trans("ReglesAutoAffectation"),'','');

// Autres options
$form=new FormExp($db);



AfficheEnteteTableau($langs->trans("Parameters"), 'paramsaffectation');

AfficheLigneOnOff($langs->trans("AffecteAutoDateLivraisonOK"), 'TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK', 'setaffectautodateok', 'pareamsaffectation');
AfficheLigneOnOff($langs->trans("AffecteAuto1ereFutureCmde"), 'TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE', 'setaffectauto1erecmde', 'pareamsaffectation');
AfficheLigneOnOff($langs->trans("AffectationAutoSi1EltParCmde"), 'TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE', 'setaffectautosi1eltparcmde', 'pareamsaffectation');

print '<tr class="oddeven">';
print '<td width="80%">'.$langs->trans("ChangeAutoDateLivraison").'</td>';

print '<td width="60" align="right">';
$arrval=array('0'=>$langs->trans("No"),
'1'=>$langs->trans("Yes").' ('.$langs->trans("AffectationsManuellesSeulement",1).')',
'2'=>$langs->trans("Yes").' ('.$langs->trans("AffectationsManuellesEtAuto",2).')',
);
print $form->selectarray("activate_TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE", $arrval, $conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE, 0, 0, 0, '', 0, 0, 0, '', 'minwidth75imp');
print '</td><td align="right">';
print '<input type="submit" class="button" name="TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE" value="'.$langs->trans("Modify").'">';
print "</td>";

print '</tr>';



print '</table>';
print '</div>';







/*
 * Document templates generators
 */


print load_fiche_titre($langs->trans("TourneeModelModule"),'','');

// Load array def with activated templates
$def = array();
$sql = "SELECT nom";
$sql.= " FROM ".MAIN_DB_PREFIX."document_model";
$sql.= " WHERE type = '".$type."'";
$sql.= " AND entity = ".$conf->entity;
$resql=$db->query($sql);
if ($resql)
{
	$i = 0;
	$num_rows=$db->num_rows($resql);
	while ($i < $num_rows)
	{
		$array = $db->fetch_array($resql);
		array_push($def, $array[0]);
		$i++;
	}
}
else
{
	dol_print_error($db);
}


print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status")."</td>\n";
print '<td align="center" width="60">'.$langs->trans("Default")."</td>\n";
print '<td align="center" width="38">'.$langs->trans("ShortInfo").'</td>';
print '<td align="center" width="38">'.$langs->trans("Preview").'</td>';
print "</tr>\n";

clearstatcache();

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);


foreach ($dirmodels as $reldir)
{
    foreach (array('','/doc') as $valdir)
    {
    	$dir = dol_buildpath($reldir."core/modules/".$type.$valdir);

        if (is_dir($dir))
        {
            $handle=opendir($dir);
            if (is_resource($handle))
            {
                while (($file = readdir($handle))!==false)
                {
                    $filelist[]=$file;
                }
                closedir($handle);
                arsort($filelist);

                foreach($filelist as $file)
                {
                    if (preg_match('/\.modules\.php$/i',$file) && preg_match('/^(pdf_|doc_)/',$file))
                    {

                    	if (file_exists($dir.'/'.$file))
                    	{
                    		$name = substr($file, 4, dol_strlen($file) -16);
	                        $classname = substr($file, 0, dol_strlen($file) -12);

	                        require_once $dir.'/'.$file;
	                        $module = new $classname($db);

	                        $modulequalified=1;
	                        if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified=0;
	                        if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified=0;

	                        if ($modulequalified)
	                        {
															if( empty($var)) $var=false;
	                            $var = !$var;
	                            print '<tr class="oddeven"><td width="100">';
	                            print (empty($module->name)?$name:$module->name);
	                            print "</td><td>\n";
	                            if (method_exists($module,'info')) print $module->info($langs);
	                            else print $module->description;
	                            print '</td>';

	                            // Active
	                            if (in_array($name, $def))
	                            {
	                            	print '<td align="center">'."\n";
	                            	print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&value='.$name.'">';
	                            	print img_picto($langs->trans("Enabled"),'switch_on');
	                            	print '</a>';
	                            	print '</td>';
	                            }
	                            else
	                            {
	                                print '<td align="center">'."\n";
	                                print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
	                                print "</td>";
	                            }

	                            // Default
	                            print '<td align="center">';
	                            if ($conf->global->TOURNEESDELIVRAISON_ADDON_PDF == $name)
	                            {
	                                print img_picto($langs->trans("Default"),'on');
	                            }
	                            else
	                            {
	                                print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	                            }
	                            print '</td>';

	                           // Info
		    					$htmltooltip =    ''.$langs->trans("Name").': '.$module->name;
					    		$htmltooltip.='<br>'.$langs->trans("Type").': '.($module->type?$module->type:$langs->trans("Unknown"));
			                    if ($module->type == 'pdf')
			                    {
			                        $htmltooltip.='<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
			                    }
					    		$htmltooltip.='<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
					    		$htmltooltip.='<br>'.$langs->trans("Logo").': '.yn($module->option_logo,1,1);
					    		$htmltooltip.='<br>'.$langs->trans("PaymentMode").': '.yn($module->option_modereg,1,1);
					    		$htmltooltip.='<br>'.$langs->trans("PaymentConditions").': '.yn($module->option_condreg,1,1);
					    		$htmltooltip.='<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang,1,1);
					    		//$htmltooltip.='<br>'.$langs->trans("Discounts").': '.yn($module->option_escompte,1,1);
					    		//$htmltooltip.='<br>'.$langs->trans("CreditNote").': '.yn($module->option_credit_note,1,1);
					    		$htmltooltip.='<br>'.$langs->trans("WatermarkOnDraftOrders").': '.yn($module->option_draft_watermark,1,1);


	                            print '<td align="center">';
	                            print $form->textwithpicto('',$htmltooltip,1,0);
	                            print '</td>';

	                            // Preview
	                            print '<td align="center">';
	                            if ($module->type == 'pdf')
	                            {
	                                print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"),'bill').'</a>';
	                            }
	                            else
	                            {
	                                print img_object($langs->trans("PreviewNotAvailable"),'generic');
	                            }
	                            print '</td>';

	                            print "</tr>\n";
	                        }
                    	}
                    }
                }
            }
        }
    }
}

print '</table>';
print "<br>";




AfficheEnteteTableau($langs->trans("ParametersDocsTournee"), 'gestionbtl');

AfficheLigneOnOff($langs->trans("PoidsSurBL"), 'TOURNEESDELIVRAISON_POIDS_BL', 'setpoidsbl', 'gestionbtl');
AfficheLigneTag($langs->trans("CategorieASupprimerBTL"), 'TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_BORDEREAU','tourneeunique_lines', 'gestionbtl');


print '</table>';
print '</div>';

AfficheEnteteTableau($langs->trans("GestionDocs"), 'gestiondocs');

AfficheLigneOnOff($langs->trans("GenerationAutoDocs"), 'TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE', 'setpdfautoupdate', 'gestiondocs');
AfficheLigneOnOff($langs->trans("SuppressionAutoDocs"), 'TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE', 'setpdfautodelete', 'gestiondocs');

AfficheLigneTag($langs->trans("TagClientGestionBL"), 'TOURNEESDELIVRAISON_TAG_CLIENT_GESTION_BL','customer', 'gestiondocs');
AfficheLigneTag($langs->trans("TagClientFactureMail"), 'TOURNEESDELIVRAISON_TAG_CLIENT_FACTURE_MAIL','customer', 'gestiondocs');

print '</table></div>';



AfficheEnteteTableau($langs->trans("GestionSignatureElectronique"), 'divsignelec');

AfficheLigneOnOff($langs->trans("ActiverSignatureElectroniqueLivraison"), 'TOURNEESDELIVRAISON_ACTIVER_SIGNATURE_ELECTRONIQUE', 'setsignatureelec', 'divsignelec');
AfficheLigneTag($langs->trans("TagClientPasDeSignatureElectronique"), 'TOURNEESDELIVRAISON_TAG_CLIENT_SANS_SIGNATURE_ELECTRONIQUE','customer', 'divsignelec');

print '</table></div>';


print load_fiche_titre($langs->trans("Comportement"),'','');



AfficheEnteteTableau($langs->trans("ComportementAjoutCommande"), 'comportementajoutcommande');


AfficheLigneTag($langs->trans("CategorieASupprimer"), 'TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_COMMANDE','tourneeunique_lines', 'comportementajoutcommande');
AfficheLigneOnOff($langs->trans("SuppressionNoteEntreCrochet"), 'TOURNEESDELIVRAISON_NOTE_SUPPRIMER_ENTRE_CROCHET_COMMANDE', 'setnotesupprimerentrecrochet', 'comportementajoutcommande');

print '</table>';
print '</div>';



print load_fiche_titre($langs->trans("ParametresDAffichage"),'','');



AfficheEnteteTableau($langs->trans("Parameters"),'paramsaffich');


AfficheLigneOnOff($langs->trans("AffichageContactIntegre"), 'TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE', 'setcontactintegre', 'paramsaffich');
AfficheLigneOnOff($langs->trans("AffichageInfoFacture"), 'TOURNEESDELIVRAISON_AFFICHER_INFO_FACTURES', 'setafficheinfofacture', 'paramsaffich');
AfficheLigneOnOff($langs->trans("AutoriserEditionTagsClientContact"), 'TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG', 'setautorisereditiontag', 'paramsaffich');

$liste=array(
	'customer' => "TOURNEESDELIVRAISON_CATEGORIES_CLIENT_A_NE_PAS_AFFICHER",
	'supplier' => "TOURNEESDELIVRAISON_CATEGORIES_FOURNISSEUR_A_NE_PAS_AFFICHER",
	'contact' => "TOURNEESDELIVRAISON_CATEGORIES_CONTACT_A_NE_PAS_AFFICHER",
);
foreach ($liste as $key => $value) {
	AfficheLigneTag($langs->trans("ListeCategorie".$key."ANePasAfficher"), $value, $key, 'paramsaffich');
}


AfficheLigneOnOff($langs->trans("ChargerPageVideTourneeUnique"), 'TOURNEESDELIVRAISON_CHARGER_PAGE_VIDE', 'setchargerpagevide');
if( empty($conf->global->TOURNEESDELIVRAISON_CHARGER_PAGE_VIDE)){
	AfficheLigneOnOff($langs->trans("PremierChargementSansTag"), 'TOURNEESDELIVRAISON_1ER_CHARGEMENT_SANS_TAG', 'set1erchargementsanstag');
}


print '</table></div>';

print load_fiche_titre($langs->trans("ParametresDivers"),'','');

AfficheEnteteTableau($langs->trans("GestionsDesSMS"),'divdivers');
AfficheLigneOnOff($langs->trans("ActivelesSMS"), 'TOURNEESDELIVRAISON_SMS', 'setsms', 'divdivers');

print '</table></div>';




print load_fiche_titre($langs->trans("ParametresDesAvertissements"),'','');


AfficheEnteteTableau($langs->trans("DemandeConfirmationAvantDe"),'divav');


$avertissements = array('Delete' => 'TOURNEESDELIVRAISON_ASK_DELETE',
												'Cancel' => 'TOURNEESDELIVRAISON_ASK_CANCEL',
												'Clone' => 'TOURNEESDELIVRAISON_ASK_CLONE',
												'Close' => 'TOURNEESDELIVRAISON_ASK_CLOSE',
												'Validate' => 'TOURNEESDELIVRAISON_ASK_VALIDATE',
												'GenererDocs' => 'TOURNEESDELIVRAISON_ASK_GENERERDOCS',
												'Unvalidate' => 'TOURNEESDELIVRAISON_ASK_UNVALIDATE',
												'AffectationAuto' => 'TOURNEESDELIVRAISON_ASK_AFFECTATIONAUTO',
												'Reopen' => 'TOURNEESDELIVRAISON_ASK_REOPEN',
												'ChangeStatutElt' => 'TOURNEESDELIVRAISON_ASK_CHANGESTATUTELT',
												'ChangeDateElt' => 'TOURNEESDELIVRAISON_ASK_CHANGEDATEELT',
												'DeleteLine' => 'TOURNEESDELIVRAISON_ASK_DELETELINE',
												'DeleteContact' => 'TOURNEESDELIVRAISON_ASK_DELETECONTACT',
												'Cmde_ClassifyBilled' => 'TOURNEESDELIVRAISON_ASK_CMDE_CLASSIFYBILLED',
												'Cmde_ClassifyUnBilled' => 'TOURNEESDELIVRAISON_ASK_CMDE_CLASSIFYUNBILLED',
												'Cmde_Reopen' => 'TOURNEESDELIVRAISON_ASK_CMDE_REOPEN',
												'Cmde_Shipped' => 'TOURNEESDELIVRAISON_ASK_CMDE_SHIPPED',
												'Exp_Shipped' => 'TOURNEESDELIVRAISON_ASK_EXP_SHIPPED',
												'Exp_Reopen' => 'TOURNEESDELIVRAISON_ASK_EXP_REOPEN',
											);

foreach ($avertissements as $key => $value) {
	AfficheLigneOnOff($langs->trans("AvertissementAvant".$key), $value, 'setavertissement&avertissement='.mb_strtolower($key), 'divav');
}




print '</table></div>';







// Page end
dol_fiche_end();

llxFooter();
$db->close();
