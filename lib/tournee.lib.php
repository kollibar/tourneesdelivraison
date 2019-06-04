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
function tourneePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("tourneesdelivraison@tourneesdelivraison");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/tourneesdelivraison/".$object->element."_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private']))
	{
		$nbNote = 0;
		if (!empty($object->note_private)) $nbNote++;
		if (!empty($object->note_public)) $nbNote++;
		$head[$h][0] = dol_buildpath('/tourneesdelivraison/'.$object->element.'_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
		$head[$h][2] = 'note';
		$h++;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
	$upload_dir = $conf->tourneesdelivraison->dir_output . "/".$object->element."/" . dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview.*\.png)$'));
	$nbLinks=Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/tourneesdelivraison/".$object->element."_document.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($nbFiles+$nbLinks) > 0) $head[$h][1].= ' <span class="badge">'.($nbFiles+$nbLinks).'</span>';
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath("/tourneesdelivraison/".$object->element."_agenda.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'agenda';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@tourneesdelivraison:/tourneesdelivraison/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@tourneesdelivraison:/tourneesdelivraison/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, $object->element.'@tourneesdelivraison');

	return $head;
}

/*
	modifie le fichier /categories/class/categorie.class.php
	retourne le nombre d'octets écrits, ou FALSE si une erreur survient ou TRUE si pas besoin de modifier
*/

function addCategorieData(){
	$path=DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

	$data=file_get_contents($path);

	if( ! strpos($data,"class Categorie extends CommonObject")===false){	// la modification n'a pas déjà été faite
		$data = str_replace("class Categorie extends CommonObject", "class CategorieOld extends CommonObject", $data, $c);
		$data .= '
		dol_include_once(\'/tourneesdelivraison/class/categorie.class.php\');
		';
		$f=fopen($path, 'w');
		$result = fwrite($f, $data);
		fclose($f);
	} else $result=true;

	return $result;
}
