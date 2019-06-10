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
 * \file        class/tourneedelivraison_lines.class.php
 * \ingroup     tourneesdelivraison
 * \brief       This file is a CRUD class file for TourneeDeLivraison_lines (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';


dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines_contacts.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines_cmde.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines_cmde_elt.class.php');

/**
 * Class for TourneeDeLivraison_lines
 */
class TourneeUnique_lines extends TourneeGeneric_lines
{


	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'tourneeunique_lines';
	public $nomelement = 'TourneeUnique_lines';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneeunique_lines';

	/**
	 * @var int Field with ID of this object key in the child table
	 */
	public $fk_element = 'fk_tournee_lines';

	public $table_element_line = 'tourneeunique_lines_contacts';

	public $class_element_line = 'TourneeUnique_lines_contacts';

	//protected $childtables=array('tourneeunique_lines_contacts','tourneeunique_lines_cmde');
	public $lines = array();
	public $lines_cmde = array();

	/**
	 * @var int  Does tourneedelivraison_lines support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does tourneedelivraison_lines support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for tourneedelivraison_lines. Must be the part after the 'object_' into object_tourneedelivraison_lines.png
	 */
	public $picto = 'tourneeunique_lines@tourneesdelivraison';


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
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>61, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>62, 'notnull'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'BL' => array('type'=>'integer', 'label'=>'BL', 'enabled'=>1, 'visible'=>1, 'position'=>51, 'notnull'=>1, 'arrayofkeyval'=>array('0'=>'0', '1'=>'1', '2'=>'2')),
		'facture' => array('type'=>'integer', 'label'=>'facture', 'enabled'=>1, 'visible'=>1, 'position'=>52, 'notnull'=>1, 'arrayofkeyval'=>array('0'=>'0', '1'=>'1')),
		'etiquettes' => array('type'=>'integer', 'label'=>'etiquettes', 'enabled'=>1, 'visible'=>1, 'position'=>53, 'default'=>1, 'notnull'=>1, 'arrayofkeyval'=>array('0'=>'0', '1'=>'1')),
		'fk_adresselivraison' => array('type'=>'integer:Contact:contact/class/contact.class.php', 'label'=>'Contact', 'enabled'=>1, 'visible'=>1, 'position'=>55, 'notnull'=>-1,),
		'rang' => array('type'=>'integer', 'label'=>'Position', 'enabled'=>1, 'visible'=>-1, 'position'=>400, 'notnull'=>1,),
		'fk_tournee' => array('type'=>'integer:Unique:tourneesdelivraison/class/tourneeunique.class.php', 'label'=>'TourneeUnique', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>1,),
		'fk_tournee_incluse' => array('type'=>'integer:TourneeUnique:tourneesunique/class/tourneeunique.class.php', 'label'=>'TourneeDeLivraisonIncluse', 'enabled'=>1, 'visible'=>1, 'position'=>41, 'notnull'=>-1,),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToThirparty",),
		'type' => array('type'=>'integer', 'label'=>'Type', 'enabled'=>1, 'visible'=>1, 'position'=>39, 'notnull'=>1, 'arrayofkeyval'=>array('0'=>'ThirdParty', '1'=>'TourneeDeLivraison')),
		'tpstheorique' => array('type'=>'integer', 'label'=>'TempsTheorique', 'enabled'=>1, 'visible'=>1, 'position'=>56, 'notnull'=>-1,),
		'infolivraison' => array('type'=>'html', 'label'=>'InfoLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>57, 'notnull'=>-1,),
		'fk_parent_line' => array('type'=>'integer', 'label'=>'ParentLine', 'enabled'=>1, 'visible'=>-1, 'position'=>80, 'notnull'=>-1,),
		'aucune_cmde' => array('type'=> 'integer','label'=>'AucuneCmde','enabled'=>1, 'visible'=>1, 'position'=>54, ),
	);
	public $rowid;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $BL;
	public $facture;
	public $etiquettes;
	public $rang;
	public $fk_tournee;
	public $fk_tournee_incluse;
	public $fk_soc;
	public $fk_adresselivraison;
	public $type;
	public $tpstheorique;
	public $infolivraison;
	public $fk_parent_line;
	public $aucune_cmde;
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


	/**
	* Load object line in memory from the database
	*
	*	@return		int						<0 if KO, >0 if OK
	*/
	public function fetch_lines()
	{
		$this->lines=array();

		$sql = 'SELECT l.rowid, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.$this->table_element_line.' as l';
		$sql.= ' WHERE l.'.$this->fk_element.' = '.$this->id;
		$sql .= ' ORDER BY l.rang, l.rowid';

		dol_syslog(get_class($this)."::fetch_lines", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);

			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$line = $this->getNewLine();
				$line->fetch($objp->rowid);
				$line->fetch_optionals();

				$this->lines[$i] = $line;
				$i++;
			}

			$this->db->free($result);

			return $this->fetch_cmdes();
		}
		else
		{
			$this->error=$this->db->error();
			return -3;
		}
	}



	/**
	* Load object line in memory from the database
	*
	*	@return		int						<0 if KO, >0 if OK
	*/
	public function fetch_cmdes()
	{
		$this->lines_cmde=array();

		$sql = 'SELECT l.rowid, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.'tourneeunique_lines_cmde as l';
		$sql.= ' WHERE l.'.$this->fk_element.' = '.$this->id;
		$sql .= ' ORDER BY l.rang, l.rowid';

		dol_syslog(get_class($this)."::fetch_cmdes", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);

			$i = 0;
			while ($i < $num) {
				$objp = $this->db->fetch_object($result);
				$line = new TourneeUnique_lines_cmde($this->db);
				$line->fetch($objp->rowid);
				$line->fetch_optionals();
				if( ! $line->checkStatut()) {
					// $line->delete();  // suppression impossible si absence de $user
					$num--;
				} else {
					$this->lines_cmde[$i] = $line;
					$i++;
				}

			}

			$this->db->free($result);

			return 1;
		} else {
			$this->error=$this->db->error();
			return -3;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		dol_syslog(get_class($this)."::delete($this->id)", LOG_DEBUG);
		//if (! empty($this->table_element_line)) $this->deletelines($user);
		$this->deletelines($user);
		$this->deletelinescmde($user);
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	* Delete all lines
	*
	*	@return int >0 if OK, <0 if KO
	*/
	public function deletelinescmde(User $user,$notrigger = false)
	{
		dol_syslog(get_class($this)."::deletelinescmde", LOG_DEBUG);
		$num=count($this->lines_cmde);
		for($i=0;$i<$num;$i++){
			//if( !empty($this->lines_cmde[$i]))
			$this->lines_cmde[$i]->delete($user,$notrigger);
			unset($this->lines_cmde[$i]);
		}
	}


	public function getNewLine(){
		$line=new TourneeUnique_lines_contacts($this->db);
		return $line;
	}
	public function getNewTournee(){
		$tournee= new TourneeUnique($this->db);
		return $tournee;
	}


	public function checkCommande(User $user, $date){
		global $user;

		$parent=$this->getParent();

		// requète de parcours de toutes les commandes concernant la societe indiquée dans la ligne, avec un statut =1 (validé) ou =2(en cours d'expédition)
		$sql = 'SELECT t.rowid, t.ref, t.fk_soc, t.fk_statut, t.date_livraison, t.facture ';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'commande as t';
		$sql .= ' WHERE t.fk_soc = '.$this->fk_soc . ' AND (t.fk_statut = 1 OR t.fk_statut = 2)';

		dol_syslog(get_class($this)."::checkCommande", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result) { // query sql succés
			$num = $this->db->num_rows($result);

			$i = 0;
			// parcours de la requête
			while ($i < $num) {
				$tulc=null;
				$ok=0;

				$objp = $this->db->fetch_object($result);

				// recherche la commande en cours (dans la requête n'est pas déjà pointée par un TourneeUnique_lines_cmde)
				foreach ($this->lines_cmde as $line_cmde) {
					if( $line_cmde->fk_commande == $objp->rowid ) {
						$tulc=$line_cmde;
						$line_cmde->fait=1;

						if( $objp->date_livraison == date("Y-m-d",$parent->date_tournee)){	// date en correspondance
							// si statut sur INUTILE -> on met non affecté
							//if( $tulc->statut == TourneeUnique_lines_cmde::INUTILE ) $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK;
							// si statut actuel avec NON date ok -> on le change
							if($tulc->statut == TourneeUnique_lines_cmde::NON_AFFECTE) $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK;
							elseif($tulc->statut == TourneeUnique_lines_cmde::DATE_NON_OK) $tulc->statut=TourneeUnique_lines_cmde::DATE_OK;
						} else {	// date diférente
							// si statut sur INUTILE -> on met non affecté
							//if( $tulc->statut == TourneeUnique_lines_cmde::INUTILE ) $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE;
							// si statut actuel avec date ok -> on le change
							if($tulc->statut == TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK) $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE;
							elseif($tulc->statut == TourneeUnique_lines_cmde::DATE_OK) $tulc->statut=TourneeUnique_lines_cmde::DATE_NON_OK;
						}
						$tulc->update($user);
						$ok=1;
						break;
					}
				}

				if( $ok==0 ){	// aucun enregistrement correspondant => création d'une nouvelle TourneeUnique_lines_cmde
					$tulc=new TourneeUnique_lines_cmde($this->db);
					$tulc->fk_commande=$objp->rowid;
					$tulc->fk_tournee_lines=$this->rowid;
					$tulc->fait=1;
					if( $objp->date_livraison == $parent->date_tournee) $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK;
					else $tulc->statut=TourneeUnique_lines_cmde::NON_AFFECTE;

					$tulc->create($user);

					$this->lines_cmde[]=$tulc;
				} else {


				}
				$i++;
			}

			// parcours de toues les TourneeUnique_lines_cmde et suppression de toutes celles pointant vers une commande introuvable (donc commande supprimée)
			$i=0; $num=count($this->lines_cmde);
			while( $i <= $num){
				$sql = 'SELECT t.rowid';
				$sql .= ' FROM ' . MAIN_DB_PREFIX . 'commande as t';
				$sql .= ' WHERE t.rowid = '.$this->lines_cmde[$i]->fk_commande;

				dol_syslog(get_class($this)."::checkCommande", LOG_DEBUG);
				$result = $this->db->query($sql);
				$error=0;
				if ($result) { // query sql succés
					$nb = $this->db->num_rows($result);
					if($nb == 0){	// aucune commande correspondant à cet id. Elle donc été supprimé. on supprime la ligne
						$this->lines_cmde[$i]->delete($user);
						$num--;
						unset($this->lines_cmde[$i]);
					}
				}
				$i++;
			}

			$this->db->free($result);

			$this->checkElt($user);


			if ($error<0) {
				return $error;
			} else {
				return 1;
			}
		} else {
			$this->error=$this->db->error();
			return -3;
		}


	}

	public function checkElt(User $user){
		// parcours de toutes les ligne cmde
		foreach ($this->lines_cmde as $lcmde) {
			if( $lcmde->statut == TourneeUnique_lines_cmde::DATE_OK || $lcmde->statut == TourneeUnique_lines_cmde::DATE_NON_OK){
				$lcmde->checkElt($user);
			}
		}
	}


	public function affectationAuto(User $user, $date, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate){

		if (!empty($this->aucune_cmde)) return 1;

		// affecation auto de toutes les lignes dont la date de livraison est la date de la tournée
		if ($AE_dateOK==1){
			foreach ($this->lines_cmde as $line_cmde) {
				if( $line_cmde->statut == TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK ) {
					$line_cmde->changeAffectation($user, TourneeUnique_lines_cmde::DATE_OK, $date, ($AE_changeAutoDate==1));
				}
				if( $line_cmde->statut == TourneeUnique_lines_cmde::DATE_OK || $line_cmde->statut==TourneeUnique_lines_cmde::DATE_NON_OK){
					$line_cmde->affectationAuto($user, $date, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate);
				}
			}
		}

		//affectation de la prochaine commande future liée à ce client
		if( $AE_1cmdeFuture==1){
			$ajd=mktime(0,0,0);
			$lcmde=null;
			$deja=false;
			foreach ($this->lines_cmde as $line_cmde) {
				if( $line_cmde->statut == TourneeUnique_lines_cmde::DATE_OK || $line_cmde->statut == TourneeUnique_lines_cmde::DATE_NON_OK){
					// cette ligne est déjà affectée à cette tournée
					$deja=true;
					break;
				} else {
					if( $line_cmde->statut != TourneeUnique_lines_cmde::INUTILE && $line_cmde->statut != TourneeUnique_lines_cmde::AUTRE_AFFECTATION
						&& ($lcmde == null
							|| $line_cmde->getTimestamp() > $ajd && $line_cmde->getTimestamp() < $lcmde->getTimestamp())
						&& ! $line_cmde->dejaAffecte()) {
						$lcmde=$line_cmde;
					}
				}
			}

			if( $lcmde != null && ! $deja ){	// si on a trouvé une prochaine commande et qu'il n'y a pas dejà une commande liée à cette ligne
				$lcmde->changeAffectation($user, TourneeUnique_lines_cmde::DATE_OK, $date, ($AE_changeAutoDate==1));
				$lcmde->affectationAuto($user, $date, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate);
			}
		}

		$this->checkElt($user);

		return 1;
	}

	public function getCmdelineByLineId($cmde_lineid){
		foreach ($this->lines_cmde as $lcmde) {
			if( $lcmde->id == $cmde_lineid) return $lcmde;
		}
		return null;
	}

	public function getCmdelineByFk_cmde($fk_commande){
		foreach ($this->lines_cmde as $lcmde) {
			if( $lcmde->fk_commande == $fk_commande) return $lcmde;
		}
		return null;
	}

	public function getEltById($elt_type, $elt_lineid){
		if( $elt_type == 'commande' ) return $this->getCmdelineByLineId($elt_lineid);
		foreach ($this->lines_cmde as $lcmde) {
			$elt_line=$lcmde->getEltById($elt_type, $elt_lineid);
			if($elt_line != null) return $elt_line;
		}
		return null;
	}

	public function changeDateElt(User $user, $elt_type,$elt_lineid, $date, $estDateTournee=true){
		$elt=$this->getEltById($elt_type, $elt_lineid);
		if( $elt==null) return -1;
		return $elt->changeDateLivraison($user, $date, $estDateTournee);
	}

	public function changeStatutElt(User $user, $elt_type,$elt_lineid, $statut, $changeDate=false){
		$elt=$this->getEltById($elt_type, $elt_lineid);
		if( $elt==null) return -1;
		$tournee=$this->getTournee();

		return $elt->changeAffectation($user, $statut, $tournee->date_tournee, $changeDate);
	}

	public function affecteElt(User $user, $elt, $notrigger = 0){
		$this->getTournee();
		if( $elt->element == 'commande'){
			$lcmde=$this->getCmdelineByFk_cmde($elt->id);
			if( empty($lcmde)){	// pas de ligne correspondant à cette commande, on l'ajoute
				$tulc=new TourneeUnique_lines_cmde($this->db);
				$tulc->fk_commande=$elt->id;
				$tulc->fk_tournee_lines=$this->id;
				if( $elt->date_livraison == $parent->date_tournee) $tulc->statut=TourneeUnique_lines_cmde::DATE_OK;
				else $tulc->statut=TourneeUnique_lines_cmde::DATE_NON_OK;

				$tulc->create($user);

				$this->lines_cmde[]=$tulc;
			} else {
				if( $lcmde->statut != TourneeUnique_lines_cmde::DATE_OK && $lcmde->statut != TourneeUnique_lines_cmde::DATE_NON_OK){
					$lcmde->changeAffectation($user, TourneeUnique_lines_cmde::DATE_NON_OK, $this->parent->date_tournee, false, $notrigger);
				}
			}
		}
	}

	public function getNbCmdeParStatut(){
		$nb=array(TourneeUnique_lines_cmde::DATE_NON_OK=>0, TourneeUnique_lines_cmde::DATE_OK=>0,
			TourneeUnique_lines_cmde::INUTILE=>0,
			TourneeUnique_lines_cmde::AUTRE_AFFECTATION=>0,
			TourneeUnique_lines_cmde::NON_AFFECTE=>0, TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK=>0
		);
		foreach ($this->lines_cmde as $lcmde) {
			$nb[$lcmde->statut]++;
		}
		return $nb;
	}

	function getTotalWeightVolume($type="shipping"){
		$totalWeight=0;
		$totalVolume=0;
		$totalOrdered=0;
		$totalToShip=0;
		foreach ($this->lines_cmde as $line) {
			$ret=$line->getTotalWeightVolume($type);
			$totalWeight += $ret['weight'];
			$totalVolume += $ret['volume'];
			$totalOrdered += $ret['ordered'];
			$totalToShip += $ret['toship'];
		}
		return array('weight'=>$totalWeight, 'volume'=>$totalVolume, 'ordered'=>$totalOrdered, 'toship'=>$totalToShip);
	}

	function getNbColis(){
		$nb=0;
		foreach ($this->lines_cmde as $line) {
			$nb+=$line->getNbColis();
		}
		return $nb;
	}




}
