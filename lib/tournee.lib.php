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
	/*
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
	*/
	return 1;
}

/**
* Function used to check if an object support tags
*
* @param $type : string type d'object ('tournee', 'product',...)
* @return bool
*/

function checkCategoriePourObjet($type){
	// ne fonctionne pas. a refaire....
	global $db;
	dol_include_once('/categorie/class/categorie.class.php');
	$c=new Categorie($db);

	if (! is_numeric($type) && ! array_key_exists($type,$c->MAP_ID)
		|| ! array_key_exists($type,$c->MAP_CAT_TABLE)
		) return false ;

	return true;
}


function cloneCategorieToAnotherObject(User $user, $categorie, $new_type){
	if( !empty($categorie->fk_parent)){
		$parent=new Categorie($categorie->db);
		$parent->fetch($categorie->fk_parent);
		$parent->fetch_optionals();
		$new_fk_parent = cloneCategorieToAnotherObject($user, $parent, $new_type);
		if( $new_fk_parent < 0 ) return $new_fk_parent;  // erreur
	} else {
		$new_fk_parent = 0;
	}

	// recherche si déjà existant
	$sql = "SELECT c.rowid";
	$sql.= " FROM ".MAIN_DB_PREFIX."categorie as c ";
	$sql.= " WHERE c.entity IN (".getEntity('category').")";
	$sql.= " AND c.type = ".$new_type;
	$sql.= " AND c.fk_parent = ".$new_fk_parent;
	$sql.= " AND c.label = '".$categorie->db->escape($categorie->label)."'";


	dol_syslog("cloneCategorieToAnotherObject", LOG_DEBUG);
	$resql = $categorie->db->query($sql);
	if ($resql)
	{
		if ($categorie->db->num_rows($resql) > 0)						// Checking for empty resql
		{
			$obj = $categorie->db->fetch_array($resql);

			if($obj[0] > 0 )
			{
				// cette catégorie existe déjà, on retourne son id
				return $obj[0];
			}
		}
	}
	else
	{
		$categorie->error=$categorie->db->error();
		return -1;
	}

	// cette catégorie n'existe pas, on la crée

	$newCat=new Categorie($categorie->db);
	$newCat->fetchCommon($categorie->id);
	$newCat->fetch_optionals();

	unset($newCat->id);
	$newCat->type=$new_type;
	$newCat->fk_parent = $new_fk_parent;

	return $newCat->create($user);

}


function getListeCategoriesDependant($cats){
	global $db;

	if( empty($cats) ) return array();

	$new=$cats;

	while( count($new) > 0 ){
		$sql = "SELECT c.rowid";
		$sql .= " FROM ".MAIN_DB_PREFIX."categorie as c ";
		$sql .= ' WHERE c.fk_parent IN (' . implode(',',$new) . ')';

		dol_syslog("getListeCategoriesDependant", LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql)
		{
			$new=array();

			$num = $db->num_rows($resql);

			$i = 0;
			while ($i < $num) {
				$obj = $db->fetch_array($resql);
				$new[]=$obj[0];

				$i++;
			}
		} else {
			// $categorie->error=$categorie->db->error();
			return -1;
		}
		$cats=array_merge($cats,$new);
	}
	return $cats;
}

function updateParametreListeCategories(){

	$listeParam=array("TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_COMMANDE","TOURNEESDELIVRAISON_CATEGORIES_CLIENT_A_NE_PAS_AFFICHER");

	foreach($listeParam as $param){
		$value=$conf->global->{$param};
		if( strpos($value, '|') === false ){
				$cats = explode(',', $value);
		} else {
			$cats = explode(',', substr($value, 0, strpos($value, '|')));
		}
		if( count($cats) == 0 ) continue;

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

/** récupère la liste des catégories d'un objet ($type et $id)
	* retoune une liste d'id de catégories
*/
function getListeCategories($id, $type){
	global $db;

	$cat = new Categorie($db);
	$categories = $cat->containing($id, $type);

	$arrayselected = array();

	foreach ($categories as $c) {
		$arrayselected[] = $c->id;
	}

	return $arrayselected;
}


function suppressionDataEntreCrochet($texte){
	$texte=preg_replace('/(\[.*?\])/m', '', $texte);
	return $texte;
}

function suppressionCategories($listeCategories, $categoriesExclues){
	$res=array();
	foreach ($listeCategories as $id => $texte) {
		if( in_array($id, $categoriesExclues)) continue;
		$res[$id]=$texte;
	}
	return $res;
}
