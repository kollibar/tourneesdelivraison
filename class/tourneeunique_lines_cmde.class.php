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
 * \file        class/tourneeunique_clients_commandes.class.php
 * \ingroup     tourneesdelivraison
 * \brief       This file is a CRUD class file for TourneeUnique_clients_commandes (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
dol_include_once('/tourneesdelivraison/class/tourneeobject.class.php');

/**
 * Class for TourneeUnique_clients_commandes
 */
class TourneeUnique_lines_cmde extends TourneeObject
{

	const NON_AFFECTE = 0;
	const NON_AFFECTE_DATE_OK = 1;
	const DATE_OK = 2;
	const DATE_NON_OK = 3;
	const AUTRE_AFFECTATION = -1;
	const INUTILE = -2;
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'tourneeunique_lines_cmde';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneeunique_lines_cmde';

	/**
	 * @var int  Does tourneeunique_clients_commandes support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does tourneeunique_clients_commandes support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for tourneeunique_clients_commandes. Must be the part after the 'object_' into object_tourneeunique_clients_commandes.png
	 */
	public $picto = 'tourneeunique_lines_cmde@tourneesdelivraison';


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
		'fk_commande' => array('type'=>'integer:Commande:commande/class/commande.class.php', 'label'=>'Commande', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>1,),
		'fk_tournee_lines' => array('type'=>'integer:TourneeUnique:tourneesunique/class/tourneeunique.class.php', 'label'=>'TourneeUnique', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>1,),
		'rang' => array('type'=>'integer', 'label'=>'Rang', 'enabled'=>1, 'visible'=>-1, 'position'=>400, 'notnull'=>1,),
		'fk_parent_line' => array('type'=>'integer', 'label'=>'ParentLine', 'enabled'=>1, 'visible'=>-1, 'position'=>80, 'notnull'=>-1,),
		'statut' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>-1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'NonAffecte', '1'=>'NonAffecteDateOK', '2'=>'DateOK', '3'=>'DateNonOK',  '-1'=>'AutreAffectation', '-2'=>'Inutile')),
	);
	public $rowid;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $fk_commande;
	public $fk_tournee_lines;
	public $rang;
	public $fk_parent_line;
	public $statut;
	// END MODULEBUILDER PROPERTIES




	/**
	 * @var int    Name of subtable line
	 */
	public $table_element_line = 'tourneeunique_lines_cmde_elt';
	/**
	 * @var int Field with ID of this object key in the child table
	 */
	public $fk_element = 'fk_tournee_lines_cmde';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	public $class_element_line = 'TourneeUnique_lines_cmde_elt';

	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('tourneeunique_clients_cmde_elt');

	/**
	 * @var TourneeUnique_clients_commandesLine[]     Array of subtable lines
	 */
	public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
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
	}

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

				if( ! $line->checkStatut() ){
					// $line->delete();  // suppression impossible si absence de $user
					$num--;
				} else {
					$this->lines[$i] = $line;
					$i++;
				}
			}

			$this->db->free($result);

			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			return -3;
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 *//*
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $hookmanager, $extrafields;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $object->fetchCommon($fromid);
	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);

	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...
	    // Clear extrafields that are unique
	    if (is_array($object->array_options) && count($object->array_options) > 0)
	    {
	    	$extrafields->fetch_name_optionals_label($this->element);
	    	foreach($object->array_options as $key => $option)
	    	{
	    		$shortkey = preg_replace('/options_/', '', $key);
	    		if (! empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
	    		{
	    			//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
	    			unset($object->array_options[$key]);
	    		}
	    	}
	    }

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    unset($object->context['createfromclone']);

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}*/


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode=0)
	{
		global $langs;
		// phpcs:enable
		if (empty($this->labelstatus))
		{
			//$langs->load("tourneesdelivraison");
			$this->labelstatus[self::NON_AFFECTE] = $langs->trans('NonAffecteDateKO');
			$this->labelstatus[self::NON_AFFECTE_DATE_OK] = $langs->trans('NonAffecteDateOK');
			$this->labelstatus[self::DATE_OK] = $langs->trans('AffecteDateOK');
			$this->labelstatus[self::DATE_NON_OK] = $langs->trans('AffecteDateKO');
			$this->labelstatus[self::AUTRE_AFFECTATION] = $langs->trans('AutreAffectation');
			$this->labelstatus[self::INUTILE] = $langs->trans('Disabled');

			$this->labelpicto[self::NON_AFFECTE] = 'check_box_uncheck.png@tourneesdelivraison';
			$this->labelpicto[self::NON_AFFECTE_DATE_OK] = 'check_box_uncheck.png@tourneesdelivraison';
			$this->labelpicto[self::DATE_OK] = 'check_box.png@tourneesdelivraison';
			$this->labelpicto[self::DATE_NON_OK] = 'check_box.png@tourneesdelivraison';
			$this->labelpicto[self::AUTRE_AFFECTATION] = 'check_box_cross.png@tourneesdelivraison';
			$this->labelpicto[self::INUTILE] = 'cross.png@tourneesdelivraison';
		}

		if ($mode == 0)
		{
			return $this->labelstatus[$status];
		}
		elseif ($mode == 1)
		{
			return $this->labelstatus[$status];
		}
		elseif ($mode == 2)
		{
			if( $status == self::NON_AFFECTE || $status == self::DATE_NON_OK) return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle') .' '. img_picto($langs->trans('DateTourneeDifferente'),'warning', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
			else return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
		}
		elseif ($mode == 3)
		{
			if( $status == self::NON_AFFECTE || $status == self::DATE_NON_OK) return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.img_picto($langs->trans('DateTourneeDifferente'),'warning', '', false, 0, 0, '', 'valignmiddle');
			else return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
		}
		elseif ($mode == 4)
		{
			if( $status == self::NON_AFFECTE || $status == self::DATE_NON_OK) return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.img_picto($langs->trans('DateTourneeDifferente'),'warning', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
			else return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
		}
		elseif ($mode == 5)
		{
			if( $status == self::NON_AFFECTE || $status == self::DATE_NON_OK) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle'). ' ' .img_picto($langs->trans('DateTourneeDifferente'),'warning', '', false, 0, 0, '', 'valignmiddle');
			else return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
		}
		elseif ($mode == 6)
		{
			if( $status == self::NON_AFFECTE || $status == self::DATE_NON_OK)  return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle'). ' ' . img_picto($langs->trans('DateTourneeDifferente'),'warning', '', false, 0, 0, '', 'valignmiddle');
			else return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
		}
	}

	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param  int		$id       Id of order
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		}
		else
		{
			dol_print_error($this->db);
		}
	}

	public function getMenuStatut($visible=false,$morehtml=''){
		global $langs;

		$commande = new Commande($this->db);
		$commande->fetch($this->fk_commande);

		$tournee=$this->getTournee();
		$tourneeLine=$this->getParent();
		$date_livraison_elt=$commande->date_livraison;

		$out = '<div id="divMenuStatutLineCmde_' . $this->rowid . '" class="divMenuStatutLine" style="position:relative;">';

		$out .= '<a href="javascript:onClickMenuStatut(\'menuStatutLineCmde_' . $this->rowid . '\');">';
		$out .= $this->getLibStatut(3);
		$out .= '</a>';
		$out .= $commande->getNomUrl();

		$out .= '<div id="menuStatutLineCmde_' . $this->rowid . ($visible?'':'" style="display:none;"') . ' class="menuStatutLine">';

		if( 	$date_livraison_elt != $tournee->date_tournee ) {
			$out .= $langs->trans('MsgAttentionDateDifferente');
			$out .= '<div class="inline-block divButAction tourneeBoutons">
			<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
				'?id=' . $this->getTournee()->id .
				'&amp;action=ask_changedateelt' .
				'&amp;elt_type=commande' .
				'&amp;elt_lineid='. $this->id .
				'&amp;elt_id=' . $this->fk_commande .
				'&amp;lineid=' . $tourneeLine->id . '">' .
				$langs->trans("ChangeDateLivraison") .
			'</a>
			</div>';
		}

		$out .= '<ul style="list-style-type:none;">
		';
		if( $this->statut!=self::AUTRE_AFFECTATION) {
			$out .= '<li>
				<div class="inline-block divButAction tourneeBoutons">
				<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
					'?id=' . $this->getTournee()->id .
					'&amp;action=ask_changestatutelt' .
					'&amp;elt_lineid='. $this->id .
					'&amp;elt_type=commande'.
					'&amp;statut=' . self::AUTRE_AFFECTATION .
					'&amp;elt_id=' . $this->fk_commande .
					'&amp;lineid=' . $tourneeLine->id . '">' .
				$langs->trans("NePasAffecterCetteTournee") . '</a>
				</div></li>';
		}
		if( $this->statut!=self::DATE_OK && $this->statut != self::DATE_NON_OK) {
			$out .= '<li>
				<div class="inline-block divButAction tourneeBoutons">
				<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
					'?id=' . $this->getTournee()->id .
					'&amp;action=ask_changestatutelt' .
					'&amp;elt_lineid='. $this->id .
					'&amp;elt_type=commande' .
					'&amp;statut=' . (	$date_livraison_elt != $tournee->date_tournee?self::DATE_NON_OK:self::DATE_OK) .
					'&amp;elt_id=' . $this->fk_commande .
					'&amp;lineid=' . $tourneeLine->id . '">' .
				$langs->trans("AffecterCetteTournee") . '</a>
				</div></li>';
		}
		if( $this->statut!=self::NON_AFFECTE && $this->statut != self::NON_AFFECTE_DATE_OK) {
			$out .= '<li>
				<div class="inline-block divButAction tourneeBoutons">
				<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
					'?id=' . $this->getTournee()->id .
					'&amp;action=ask_changestatutelt' .
					'&amp;elt_lineid='. $this->id .
					'&amp;elt_type=commande' .
					'&amp;statut=' . ( $date_livraison_elt != $tournee->date_tournee?self::NON_AFFECTE:self::NON_AFFECTE_DATE_OK) .
					'&amp;elt_id=' . $this->fk_commande .
					'&amp;lineid=' . $tourneeLine->id . '">' .
				$langs->trans("MettreAffectationInconnue") . '</a>
				</div></li>';
			}
		$out .= '</ul>
		</div>';

		$out .= '</div>';

		return $out;
	}

	public function getTournee(){
		if( empty($this->tournee)){
			$parent=$this->getParent();
			$this->tournee=$parent->getTournee();
		}
		return $this->tournee;
	}

	public function getParent(){
		if( empty($this->parent)){
			$this->parent=new TourneeUnique_lines($this->db);;
			$this->parent->fetch($this->fk_tournee_lines);
		}
		return $this->parent;
	}

	public function checkElt(User $user){

		$tournee=$this->getTournee();

		$sql = 'SELECT t.fk_source, t.sourcetype, t.fk_target, t.targettype';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'element_element as t';
		$sql .= ' WHERE t.fk_source = '. $this->fk_commande . ' AND t.sourcetype = "commande" ';

		dol_syslog(get_class($this)."::checkElt", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result) { // query sql succés
			$num = $this->db->num_rows($result);

			$i = 0;
			// parcours de la requête
			while ($i < $num) {
				$tulce=null;
				$ok=0;

				$objp = $this->db->fetch_object($result);

				foreach ($this->lines as $line_elt) {
					if( $line_elt->fk_elt == $objp->fk_target && $line_elt->type_element == $objp->targettype) {
						$tulce=$line_cmde;
						$tulce->fait=1;
						if( $tulce->statut == TourneeUnique_lines_cmde_elt::INUTILE ) {	// si elle avais le styatut inutile, réinitialisation de son styatut
							if( $tulce->type_element == 'shipping'){	// cas d'une expédition
								$elt=$tulce->loadElt();
								// vérification de la date
								if( $elt->date_delivery == $tournee->date_tournee) $tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE_DATE_OK;
								else $tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE;
							} else if ($tulce->type_element == 'facture'){ // cas d'une facture
								$tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE;
							}
						}
						$ok=1;
						break;
					}
				}

				if( $ok==0 ){	// aucun enregistrement correspondant => création d'une nouvelle TourneeUnique_lines_cmde
					$tulce=new TourneeUnique_lines_cmde_elt($this->db);
					$tulce->type_element=$objp->targettype;
					$tulce->fk_elt = $objp->fk_target;
					$tulce->fk_tournee_lines_cmde = $this->rowid;
					$tulce->fait=1;

					if( $tulce->type_element == 'shipping'){	// cas d'une expédition
						$elt=$tulce->loadElt();
						// vérification de la date
						if( $elt->date_delivery == $tournee->date_tournee) $tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE_DATE_OK;
						else $tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE;

					} else if ($tulce->type_element == 'facture'){ // cas d'une facture
						$tulce->statut=TourneeUnique_lines_cmde_elt::NON_AFFECTE;
					}
					$tulce->create($user);

					$this->lines[]=$tulce;
				} else {


				}
				$i++;
			}	// fin while($i<$num)


			// parcours de toues les TourneeUnique_lines_cmde_elt et toutes celle n'ayant pas été pointé âr une commande sont mise à l'état INUTLE
			foreach ($this->lines as $line_elt) {
				if( empty($line_elt->fait) ){ // la commanbde a été supprimé ou est passé à un état inadéquat
					$line_elt->statut=TourneeUnique_lines_cmde_elt::INUTILE;
				} else{
					unset($line_elt->fait);
				}
			}



			$this->db->free($result);
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

	public function getTimestamp($force=false){
		if( empty($this->timestamp) || $force){
			$this->loadElt();
			$date=$this->elt->date_livraison;
			$this->timestamp=mktime(0,0,0,substr($date,0,4),substr($date,5,2), substr($date,8,2));
		}
		return $this->timestamp;
	}

	public function getNewLine(){
		return new TourneeUnique_lines_cmde_elt($this->db);
	}

	public function loadElt(){
		if( empty($this->elt)){
			$this->elt=new Commande($this->db);
			$this->elt->fetch($this->fk_commande);
			$this->elt->fetch_optionals();
		}

		return $this->elt;
	}

	public function changeAffectation(User $user, $affectation, $dateTournee=null, $changeDate=false){
		$this->loadElt();
		if( ($affectation == self::DATE_OK || $affectation== self::DATE_NON_OK)
		 	&& $this->elt->statut != Commande::STATUS_VALIDATED
			&& $this->elt->statut != Commande::STATUS_SHIPMENTONPROCESS
			&& $this->elt->statut != Commande::STATUS_CLOSED) {
			return -5;	// on ne peux pas affecter un élément non validé
		}

		if( $dateTournee != null ){
			if( $dateTournee != $this->elt->date_livraison){
				if( $affectation == self::DATE_OK) $affectation = self::DATE_NON_OK;
				if( $affectation == self::NON_AFFECTE_DATE_OK ) $affectation = self::NON_AFFECTE;
			} else {
				if( $affectation == self::DATE_NON_OK) $affectation = self::DATE_OK;
				if( $affectation == self::NON_AFFECTE ) $affectation = self::NON_AFFECTE_DATE_OK;
			}
		}

		// si affectation -> vérification que n'est pas déjà affecté ailleurs
		if( $affectation == self::DATE_OK or $affectation == self::DATE_NON_OK){
			$result=$this->dejaAffecte();
			if( $result === -1) return -3; //erreur lors de la vérif si déjà affectée
			if( $result === true ) return -4; //impossible d'affecter: déjà affecté!
		}

		$this->statut=$affectation;
		$result = $this->update($user);
		if( $result <0 ) return -1;	// erreur lors de l'update

		if( $changeDate ){
			return $this->changeDateLivraison($user,$dateTournee);
		}

		return 1;

	}

	public function dejaAffecte(){
		$sql = 'SELECT t.fk_commande, t.statut, t.rowid';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' WHERE t.fk_commande = '. $this->fk_commande;
		$sql .= ' AND ( t.statut = ' . self::DATE_OK . ' OR t.statut = ' . self::DATE_NON_OK . ')';

		dol_syslog(get_class($this)."::dejaAffecte()", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result) { // query sql succés
				$num = $this->db->num_rows($result);
				$this->db->free($result);

				if( $num > 0) {	// cette commande est déjà affectée
					return true;
				} else {
					return false;
				}

		} else {
			dol_print_error($this->db);
			return -1;
		}
	}

	public function changeDateLivraison(User $user, $date, $estDateTournee=true){
		$elt=$this->loadElt();
		$elt->date_livraison = $date;
		$this->getTimestamp(true);	// pour actualiser le timestamp de $this
		$result = $elt->set_date_livraison($user, $date);

		if( $result > 0){ // OK
			if($estDateTournee){
				if($this->statut==self::NON_AFFECTE) $this->statut=self::NON_AFFECTE_DATE_OK;
				if($this->statut==self::DATE_NON_OK) $this->statut=self::DATE_OK;
			} else {
				if($this->statut==self::NON_AFFECTE_DATE_OK) $this->statut=self::NON_AFFECTE;
				if($this->statut==self::DATE_OK) $this->statut=self::DATE_NON_OK;
			}
			$this->update($user);
			return $result;
		} else { // KO
			return $result;
		}
	}

	public function affectationAuto(User $user, $date, $AE_dateOK, $AE_1cmdeFuture, $AE_1eltParCmde, $AE_changeAutoDate){
		// si 1elt par commande
		if( $AE_1eltParCmde == 1 ){
			$line_fact=null;
			$line_livr=null;
			foreach ($this->lines as $line) {
				if( $line->statut != TourneeUnique_lines_cmde_elt::INUTILE
					&& $line->statut != TourneeUnique_lines_cmde_elt::AUTRE_AFFECTATION
					&& $line->type_element == 'shipping'
					&& $line->loadElt()->date_delivery == $date ){
						$line->changeAffectation($user,TourneeUnique_lines_cmde_elt::DATE_OK,$date);
				}
				if( $line->statut != TourneeUnique_lines_cmde_elt::INUTILE){
					if( $line->type_element == 'shipping' && $line->statut != TourneeUnique_lines_cmde_elt::INUTILE){
						if($line_livr==null ) $line_livr=$line;
						else $line_livr='NON';
					} else if( $line->type_element == 'facture'){
						if($line_fact==null ) $line_fact=$line;
						else $line_fact='NON';
					}
				}
			}
			if( $line_livr != null && $line_livr != 'NON' && $line_livr->statut != TourneeUnique_lines_cmde_elt::AUTRE_AFFECTATION){
				$line_livr->changeAffectation($user,TourneeUnique_lines_cmde_elt::DATE_OK,$date);
			}
			if( $line_fact != null && $line_fact != 'NON' && $line_fact->statut != TourneeUnique_lines_cmde_elt::AUTRE_AFFECTATION){
				$line_fact->changeAffectation($user,TourneeUnique_lines_cmde_elt::DATE_OK,$date);
			}
		}

		return 1;
	}

	public function getEltById($elt_type, $elt_lineid){
		foreach ($this->lines as $line) {
			if( $line->id==$elt_lineid && $line->type_element) return $line;
		}
		return null;
	}

	public function getNbEltParStatut($elt_type){
		$nb=array(TourneeUnique_lines_cmde::DATE_NON_OK=>0, TourneeUnique_lines_cmde::DATE_OK=>0,
			TourneeUnique_lines_cmde::INUTILE=>0,
			TourneeUnique_lines_cmde::AUTRE_AFFECTATION=>0,
			TourneeUnique_lines_cmde::NON_AFFECTE=>0, TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK=>0
		);
		foreach ($this->lines as $lelt) {
			if( $lelt->type_element == $elt_type) $nb[$lelt->statut]++;
		}
		return $nb;
	}

	function getTotalWeightVolume($type="shipping"){
		if( $this->statut != self::DATE_OK && $this->statut != self::DATE_NON_OK){
			return array('weight'=>0, 'volume'=>0, 'ordered'=>0, 'toship'=>0);
		}
		$totalWeight=0;
		$totalVolume=0;
		$totalOrdered=0;
		$totalToShip=0;
		if($type == "shipping" || $type=="facture"){
			foreach ($this->lines as $line) {
				$ret=$line->getTotalWeightVolume($type);
				$totalWeight += $ret['weight'];
				$totalVolume += $ret['volume'];
				$totalOrdered += $ret['ordered'];
				$totalToShip += $ret['toship'];
			}
		} else if($type == "commande"){
			$this->loadElt();

			$ret=$this->elt->getTotalWeightVolume($type);
			$totalWeight = $ret['weight'];
			$totalVolume = $ret['volume'];
			$totalOrdered = $ret['ordered'];
			$totalToShip = $ret['toship'];
		}
		return array('weight'=>$totalWeight, 'volume'=>$totalVolume, 'ordered'=>$totalOrdered, 'toship'=>$totalToShip);
	}

	function getNbColis(){
		$nb=0;
		foreach ($this->lines as $line) {
			$nb+=$line->getNbColis();
		}
		return $nb;
	}

	function checkStatut(){
		$cmde=new Commande($this->db);

		$sql = 'SELECT t.rowid';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $cmde->table_element . ' as t';
		$sql .= ' WHERE t.rowid = '. $this->fk_commande;

		dol_syslog(get_class($this)."::checkStatut()", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result) { // query sql succés
				$num = $this->db->num_rows($result);
				$this->db->free($result);

				if( $num == 0) {	// aucune commande avec cette référence => elle a été supprimé
					$this->statut==self::INUTILE;
					return false;
				} else {
					return true;
				};

		} else {
			dol_print_error($this->db);
			return -1;
		}

	}

	// retourne le chemin vers le pdf de l'objet si il a été généré sinon retourne ''

	function getPDF(){
		$elt=$this->loadElt();
		if($elt == null) return '';

		$ref = dol_sanitizeFileName($elt->ref);
		$file = '';

		if( $conf->commande->dir_output && !empty($elt->ref)){
					$dir = $conf->commande->dir_output . "/" . $ref ;
					$file = $conf->commande->dir_output . "/" . $ref . "/" . $ref . ".pdf";
		}

		if (file_exists($file)){
			return $file;
		}
		return '';
	}

}
