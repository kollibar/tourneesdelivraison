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


 function AfficheLigneOnOff($texte, $variable, $action){
   global $langs, $conf;
  print '<tr class="oddeven">';
  print '<td width="80%">'.$texte.'</td>';
  print '<td>&nbsp</td>';
  print '<td>&nbsp</td>';
  print '<td align="center">';
  if (!empty($conf->global->{$variable}))
  {
 	 print '<a href="' . $_SERVER['PHP_SELF'].'?action='.$action.'&value=0#divaff">';
 	 print img_picto($langs->trans("Activated"),'switch_on');
  }
  else
  {
 	 print '<a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&value=1#divaff">';
 	 print img_picto($langs->trans("Disabled"),'switch_off');
  }
  print '</a></td>';
  print '</tr>';
 }
