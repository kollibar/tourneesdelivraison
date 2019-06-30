<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/tourneedelivraison.class.php
 * \ingroup     tourneesdelivraison
 * \brief       This file is a CRUD class file for TourneeDeLivraison (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/tourneedelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines_contacts.class.php');

/**
 * Class for TourneeDeLivraison
 */
class TourneeUnique extends TourneeGeneric
{

	const AUCUN_MASQUE			= 0;
	const MASQUE_PASDECMDE	= 1;
	const MASQUE_SANSCMDE		=	2;
	const MASQUE_SANSCMDEAFF_OU_INC = 3;
	const MASQUE_SANSCMDEAFFECTE = 4;
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'tourneeunique';
	public $nomelement = 'TourneeUnique';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneeunique';
	/**
	 * @var int Field with ID of this object key in the child table
	 */
	public $fk_element = 'fk_tournee';

	public $table_element_line = 'tourneeunique_lines';
	public $class_element_line = 'TourneeUnique_lines';
	//protected $childtables=array('tourneeunique_lines');
	public $lines = array();

	/**
	 * @var int  Does tourneedelivraison support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does tourneedelivraison support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for tourneedelivraison. Must be the part after the 'object_' into object_tourneedelivraison.png
	 */
	public $picto = 'tourneeunique@tourneesdelivraison';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>1, 'visible'=>-1, 'position'=>60, 'notnull'=>-1,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>61, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>62, 'notnull'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'statut' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>-1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'default'=>'0', 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Actif', '2'=>'Clos', '-1'=>'Annul&eacute;')),
		'km' => array('type'=>'integer', 'label'=>'DistanceTotale', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'dureeTrajet' => array('type'=>'integer', 'label'=>'DureeTrajet', 'enabled'=>1, 'visible'=>1, 'position'=>51, 'notnull'=>-1,),
		'fk_tourneedelivraison' => array('type'=>'integer:TourneeDeLivraison:tourneesdelivraison/class/tourneedelivraison.class.php', 'label'=>'TourneeDeLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1,),
		'date_tournee' => array('type'=>'date', 'label'=>'DateTournee', 'enabled'=>1, 'visible'=>1, 'position'=>70, 'notnull'=>1,),
		'date_prochaine' => array('type'=>'date', 'label'=>'DateProchaineTournee', 'enabled'=>1, 'visible'=>1, 'position'=>71, 'notnull'=>-1,),
		'ae_datelivraisonidentique' => array('type'=>'integer', 'label'=>'AffecteAutoDateLivraisonOK', 'enabled'=>1, 'visible'=>1, 'position'=>80, 'notnull'=>-1,'default'=>'1','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'ae_1ere_future_cmde' => array('type'=>'integer', 'label'=>'AffecteAuto1ereFutureCmde', 'enabled'=>1, 'visible'=>1, 'position'=>81, 'notnull'=>-1,'default'=>'0','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'ae_1elt_par_cmde' => array('type'=>'integer', 'label'=>'AffectationAutoSi1EltParCmde', 'enabled'=>1, 'visible'=>1, 'position'=>82, 'notnull'=>-1,'default'=>'0','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'change_date_affectation' => array('type'=>'integer', 'label'=>'ChangeAutoDateLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>83, 'notnull'=>-1,'default'=>'0','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Manuelle seulement', '4'=>'manuelle et automatique',)),
		'masque_ligne' => array('type'=>'integer', 'label'=>'MasquerLesLignes', 'enabled'=>1, 'visible'=>1, 'position'=>85, 'notnull'=>-1,'default'=>'0','arrayofkeyval'=>array('0'=>'AucunMasquage','1'=>'MarqueSansCommande', '2'=>'SansCmde', '3' => 'SansCmdeAffOuInc','4'=>'SansCmdeAffecte',)),
	);
	public $rowid;
	public $ref;
	public $label;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $statut;
	public $km;
	public $dureeTrajet;
	public $date_tournee;
	public $ae_datelivraisonidentique;
	public $ae_1ere_future_cmde;
	public $ae_1elt_par_cmde;
	public $change_date_affectation;
	public $model_pdf;
	public $masque_ligne;
	// END MODULEBUILDER PROPERTIES





	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 *//*
	public function __construct(DoliDB $db)
	{
		global $conf, $langs, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		foreach($this->fields as $key => $val)
		{
			if (is_array($this->fields['status']['arrayofkeyval']))
			{
				foreach($this->fields['status']['arrayofkeyval'] as $key2 => $val2)
				{
					$this->fields['status']['arrayofkeyval'][$key2]=$langs->trans($val2);
				}
			}
		}
	}*/

	public function getNewLine($parent=null){
		$line=new TourneeUnique_lines($this->db, $parent);
		return $line;
	}// phpcs:enable


	public function checkCommande(User $user){
		$listeSoc=$this->getListeSoc();

		foreach ($listeSoc['soclineid'] as $fk_lineid) {
			$line=new TourneeUnique_lines($this->db);
			$line->fetch($fk_lineid);
			$line->checkCommande($user, $this->date_tournee);
		}
	}

	public function affectationAuto(User $user, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate){
		$listeSoc=$this->getListeSoc();

		foreach ($listeSoc['uniquesoclineid'] as $fk_lineid) {
			$line=new TourneeUnique_lines($this->db);
			$line->fetch($fk_lineid);
			$line->affectationAuto($user, $this->date_tournee, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate);
		}
		return 1;
	}

	public function setLineNotHasCmde(User $user, $lineid, $aucune_cmde){
		$line=$this->getLineById($lineid);

		$line->aucune_cmde=($aucune_cmde)?1:0;

		return $line->update($user);
	}

	public function getTourneeDeLivraison(){
		if( empty($this->fk_tourneedelivraison)) return null;
		if( empty($this->tourneedelivraison)) {
			$this->tourneedelivraison = new TourneeDeLivraison($this->db);
			$this->tourneedelivraison->fetch($this->fk_tourneedelivraison);
		}
		return $this->tourneedelivraison;
	}

	public function getTotalWeightVolume($type="shipping"){
		$totalWeight=0;
		$totalVolume=0;
		$totalOrdered=0;
		$totalToShip=0;
		foreach ($this->lines as $line) {
			$ret=$line->getTotalWeightVolume($type);
			$totalWeight += $ret['weight'];
			$totalVolume += $ret['volume'];
			$totalOrdered += $ret['ordered'];
			$totalToShip += $ret['toship'];
		}
		return array('weight'=>$totalWeight, 'volume'=>$totalVolume, 'ordered'=>$totalOrdered, 'toship'=>$totalToShip);
	}

	public function changeDateEltToDateTournee(User $user, $lineid, $elt_type, $elt_lineid){
		$line = $this->getLineById($lineid);
		if($line==null) return -1;
		return $line->changeDateElt($user,$elt_type,$elt_lineid,$this->date_tournee, true);
	}

	public function changeStatutElt(User $user, $lineid, $elt_type,$elt_lineid, $statut, $changeDate=false){
		$line = $this->getLineById($lineid);
		if($line==null) return -1;
		return $line->changeStatutElt($user, $elt_type,$elt_lineid, $statut, $changeDate);
	}



}

?>
