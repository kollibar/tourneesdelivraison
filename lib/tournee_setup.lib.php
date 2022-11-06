<?php
/* Copyright (C) ---Put here your own copyright and developer email---
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
 * \file    lib/tourneesdelivraison_tourneedelivraison.lib.php
 * \ingroup tourneesdelivraison
 * \brief   Library files with common functions for TourneeDeLivraison
 */

/**
 * Prepare array of tabs for Tournee
 *
 * @param	TourneeDeLivraison	$object		TourneeDeLivraison
 * @return 	array					Array of tabs
 */

 dol_include_once('/categorie/class/categorie.class.php');


function AfficheEnteteTableau($titre, $id=""){
  global $conf, $user, $form, $langs;
  print '<div '.(!empty($id)?'id="'.$id.'"':'').'class="div-table-responsive-no-min">';
  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre">';
  print '<td>'.$titre.'</td>'."\n";
  print '<td width="120">&nbsp;</td>';
  print '<td align="right" width="120">'.$langs->trans("Value").'</td>'."\n";
  print '<td width="80">&nbsp;</td></tr>'."\n";
}

 function AfficheLigneOnOff($texte, $variable, $action, $idlink=''){
   global $conf, $user, $form, $langs;
  print '<tr class="oddeven">';
  print '<td width="80%">'.$texte.'</td>';
  print '<td>&nbsp</td>';
  print '<td>&nbsp</td>';
  print '<td align="center">';
  if (!empty($conf->global->{$variable}))
  {
 	 print '<a href="' . $_SERVER['PHP_SELF'].'?action='.$action.'&value=0'.(!empty($idlink)?'#'.$idlink:'').'">';
 	 print img_picto($langs->trans("Activated"),'switch_on');
  }
  else
  {
 	 print '<a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&value=1'.(!empty($idlink)?'#'.$idlink:'').'">';
 	 print img_picto($langs->trans("Disabled"),'switch_off');
  }
  print '</a></td>';
  print '</tr>';
 }

function AfficheLigneTag($texte, $varConf, $typeObject){
  global $conf, $user, $form, $langs;
  if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){

  	$arrayselected=array();
  	$cate_arbo = $form->select_all_categories($typeObject, null, null, null, null, 1);

  	$c = new Categorie($db);

  	if ( ! empty($conf->global->{$varConf})){
  		if( strpos($conf->global->{$varConf}, '|') === false ){
  				$arrayselected = explode(',', $conf->global->{$varConf});
  		} else {
  			$arrayselected = explode(',', substr($conf->global->{$varConf}, 0, strpos($conf->global->{$varConf}, '|')));
  		}
  	}

  	print '<tr class="oddeven">';
  	print '<td width="80%">'.$texte.'</td>';
  	print '<td>&nbsp</td>';
  	print '<td align="center">';

  	print $form->multiselectarray('cats_'.$varConf, $cate_arbo, $arrayselected, '', 0, '', 0, '90%');

  	print '</td><td align="right">';
  	print '<input type="submit" class="button" name="'.$varConf.'" value="'.$langs->trans("Modify").'">';
  	print "</td>";
  	print '</tr>';
  }
}
