<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
dol_include_once('/tourneesdelivraison/class/tourneeobject.class.php');


class Carton{
	public $qty;
	public $product_id;
	public $product_name;
	public $colisage;

	public function __construct($qty, $product_id, $product_name, $colisage=0){
		$this->qty=$qty;
		$this->product_id=$product_id;
		$this->product_name=$product_name;
		$this->colisage=$colisage;
	}
}

/**
 * Class for TourneeUnique_clients_commandes
 */
class TourneeUnique_lines_cmde_elt extends TourneeObject
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
	public $element = 'tourneeunique_lines_cmde_elt';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneeunique_lines_cmde_elt';

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
	public $picto = 'tourneeunique_lines_cmde_elt@tourneesdelivraison';


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
		'fk_elt' => array('type'=>'integer:Commande:commande/class/commande.class.php', 'label'=>'Commande', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>1,),
		'type_element' => array('type'=>'varchar(128)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'fk_tournee_lines_cmde' => array('type'=>'integer:TourneeUnique:tourneesunique/class/tourneeunique.class.php', 'label'=>'TourneeUnique', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>1,),
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
	public $fk_elt;
	public $type_element;
	public $fk_tournee_lines_cmde;
	public $rang;
	public $fk_parent_line;
	// END MODULEBUILDER PROPERTIES




	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_tournee_lines_cmde_elt';



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
	// phpcs:enable
	if (empty($this->labelstatus))
	{
		global $langs;
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

	public function loadElt(){
		if( empty($this->elt)){
			if( $this->type_element == 'commande') $this->elt=new Commande($this->db);
			elseif( $this->type_element == 'shipping') $this->elt=new Expedition($this->db);
			elseif( $this->type_element == 'facture') $this->elt=new Facture($this->db);
			else $this->elt=null;

			if( $this->elt != null) $this->elt->fetch($this->fk_elt);
		}

		return $this->elt;
	}

	// retourne le timestamp de la date de livraison
	public function getTimestamp(){
		$this->loadElt();
		if( $this->type_element == 'shipping') $date=$this->elt->date_delivery;
		else if( $this->type_element=='commande') $date=$this->elt->date_livraison;
		else return -1;
		return mktime(0,0,0,substr($date,0,4),substr($date,5,2), substr($date,8,2));
	}

	public function changeAffectation(User $user, $affectation, $dateTournee=null, $changeDate=false){
		if( $this->type_element != 'shipping' && $this->type_element != 'facture' && $this->type_element != 'commande') {
			return -6;
		}

		$this->loadElt();

		if( ($affectation == self::DATE_OK || $affectation== self::DATE_NON_OK)
			&& ($this->type_element == 'shipping' && $this->elt->statut != Expedition::STATUS_VALIDATED
			|| $this->type_element == 'facture' && $this->elt->statut != Facture::STATUS_VALIDATED
			|| $this->type_element == 'commande' && $this->elt->statut != Commande::STATUS_VALIDATED
			)
		) {
			return -5;	// on ne peux pas affecter un élément non validé*
		}

		if( $dateTournee != null && $this->type_element=='shipping'){
			if( $dateTournee != $this->elt->date_delivery){
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

		$old_affectation=$this->statut;
		$this->statut=$affectation;

		$result = $this->update($user);
		if( $result <0 ) return -1;

		$this->getParent();

		$this->elt->fetchObjectLinked();

		if(!empty($this->elt->linkedObjectsIds['tourneesdelivraison'])
			&& in_array($this->parent->id,$this->elt->linkedObjectsIds['tourneesdelivraison'])) {
				$liaison=1;
		}

		if(($affectation == self::DATE_OK || $affectation == self::DATE_NON_OK) && empty($liaison)){	// si affectation à cette tournée et pas de liaison
			$this->elt->add_object_linked('tourneesdelivraison',$this->parent->id); // on crée une liaison
		}

		if($affectation != self::DATE_OK && $affectation != self::DATE_NON_OK && !empty($liaison)){ // si plus affecté à cet objet et liaison
			// on supprime la liaison
			$this->elt->deleteObjectLinked($this->parent->id, 'tourneesdelivraison');
		}


		if( $changeDate ){
			return $this->changeDateLivraison($user,$dateTournee);
		}

		return 1;
	}

	public function dejaAffecte(){

		$sql = 'SELECT t.fk_elt, t.type_element, t.statut, t.rowid';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' WHERE t.fk_elt = '. $this->fk_elt;
		$sql .= ' AND t.type_element = \'' . $this->type_element .'\'';
		$sql .= ' AND ( t.statut = ' . self::DATE_OK . ' OR t.statut = ' . self::DATE_NON_OK . ')';

		dol_syslog(get_class($this)."::dejaAffecte()", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result) { // query sql succés
				$num = $this->db->num_rows($result);
				$this->db->free($result);

				if( $num > 0) {	// cet lément est déjà affectée
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
		if( $this->type_element=='shipping' ){
			$this->loadElt();
			$this->elt->date_delivery = $date;
			$this->getTimestamp();

			$result = $this->elt->set_date_livraison($user, $date);

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
		return 0;
	}

	function getTotalWeightVolume($type="shipping"){
		$totalWeight=0;
		$totalVolume=0;
		$totalOrdered=0;
		$totalToShip=0;

		if( $this->type_element == $type ){
			$this->loadElt();

			$ret=$this->elt->getTotalWeightVolume();
			$totalWeight += $ret['weight'];
			$totalVolume += $ret['volume'];
			$totalOrdered += $ret['ordered'];
			$totalToShip += $ret['toship'];
		}

		return array('weight'=>$totalWeight, 'volume'=>$totalVolume, 'ordered'=>$totalOrdered, 'toship'=>$totalToShip);
	}

	public function getMenuStatut($visible=false,$morehtml=''){
		global $langs;

		if( $this->type_element == 'shipping') $elt = new Expedition($this->db);
		else if ($this->type_element == 'facture') $elt=new Facture($this->db);
		else return '';
		$elt->fetch($this->fk_elt);

		$tournee=$this->getTournee();
		$tourneeLine=$this->getParent()->getParent();
		if( $this->type_element == 'shipping' ) $date_livraison_elt=$elt->date_delivery;
		else if( $this->type_element == 'facture' ) $date_livraison_elt==$tournee->date_tournee;

		$out = '<div id="divMenuStatutLine'.ucfirst($this->type_element) . '_' . $this->rowid . '" class="divMenuStatutLine" style="position:relative;">';

		$out .= '<a href="javascript:onClickMenuStatut(\'menuStatutLine'.ucfirst($this->type_element) . '_' . $this->rowid . '\');">';
		$out .= $this->getLibStatut(3);
		$out .= '</a>';
		$out .= $elt->getNomUrl();

		$out .= '<div id="menuStatutLine'.ucfirst($this->type_element) . '_' . $this->rowid . ($visible?'':'" style="display:none;"') . ' class="menuStatutLine">';

		if( $this->type_element=='shipping' && $date_livraison_elt != $tournee->date_tournee ) {
			$out .= $langs->trans('MsgAttentionDateDifferente');
			$out .= '<div class="inline-block divButAction tourneeBoutons">
			<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
				'?id=' . $this->getTournee()->id .
				'&amp;action=ask_changedateelt' .
				'&amp;elt_type=' . $this->type_element .
				'&amp;elt_lineid='. $this->id .
				'&amp;elt_id=' . $this->fk_elt .
				'&amp;lineid=' . $tourneeLine->id . '">' .
				$langs->trans("ChangeDateLivraison") .
			'</a>
			</div>';
		}

		$out .= '<ul style="list-style-type:none;">
		';
		if( $this->statut != self::AUTRE_AFFECTATION) {
			$out .= '<li>
				<div class="inline-block divButAction tourneeBoutons">
				<a class="butAction" href="' . $_SERVER['PHP_SELF'] .
					'?id=' . $this->getTournee()->id .
					'&amp;action=ask_changestatutelt' .
					'&amp;elt_lineid='. $this->id .
					'&amp;elt_type=' . $this->type_element .
					'&amp;statut=' . self::AUTRE_AFFECTATION .
					'&amp;elt_id=' . $this->fk_elt .
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
					'&amp;elt_type=' . $this->type_element .
					'&amp;statut=' . ($date_livraison_elt != $tournee->date_tournee?self::DATE_NON_OK:self::DATE_OK) .
					'&amp;elt_id=' . $this->fk_elt .
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
					'&amp;elt_type=' . $this->type_element .
					'&amp;statut=' . ($date_livraison_elt != $tournee->date_tournee?self::NON_AFFECTE:self::NON_AFFECTE_DATE_OK) .
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
			$this->parent=new TourneeUnique_lines_cmde($this->db);;
			$this->parent->fetch($this->fk_tournee_lines_cmde);
		}
		return $this->parent;
	}

	public function getNbColis(){
		$this->makeListeCartons();
		return count($this->cartons);
	}

	public function getCartonNum($num){
		$this->makeListeCartons();
		if( $this->type_element=='shipping'){
			if($num<=count($this->cartons) && $num>0) return $this->cartons[$num];	// $num > $nb de cartons => erreur
			else return -1;
		} else return -1;
	}

	function makeListeCartons(){
		if( empty($this->cartons)) {
			$this->cartons=array();

			if( $this->type_element=='shipping'){

				$exp=new Expedition($this->db);
				$exp->fetch($this->fk_elt);
				$exp->fetch_optionals();

				$nb=0;
				foreach ($exp->lines as $lexp) {
					$product = new Product($this->db);
					$product->fetch($lexp->fk_product);
					$product->fetch_optionals();

					if( !empty($product->array_options['options_est_cache_bordereau_livraison'])) continue;

					if( ! empty($product->array_options['options_colisage'])){
						$qty=floor($lexp->qty_shipped/$product->array_options['options_colisage'])+
							($lexp->qty_shipped % $product->array_options['options_colisage']!=0?1:0);

						for ($i=1; $i <= $qty; $i++) {
							$nb++;
							if( $i == $qty && ($lexp->qty_shipped % $product->array_options['options_colisage'])!=0 )
								$this->cartons[$nb] = new Carton($lexp->qty_shipped % $product->array_options['options_colisage'],
																								$product->id,
																								$product->ref,
																								$product->array_options['options_colisage']
																							);
								/* $this->cartons[$nb]=array('product'=>$product->ref,
										'qty'=>$lexp->qty_shipped % $product->array_options['options_colisage']); */
							else
								$this->cartons[$nb] = new Carton($product->array_options['options_colisage'],
																							$product->id,
																							$product->ref,
																							$product->array_options['options_colisage']
																						);
								//$this->cartons[$nb]=array('product'=>$product->ref, 'qty'=>$product->array_options['options_colisage']);
						}

					} else {
						$nb++;
						$this->cartons[$nb]= new Carton($lexp->qty_shipped, $product->id, $product->ref, 0);
						//$this->cartons[$nb]=array('product'=>$product->ref, 'qty'=>$lexp->qty_shipped);
					}
				}
			}
		}
	}

	public function printListeCarton($select=-1){
		$this->makeListeCartons();

		for($i=1;$i<=count($this->cartons);$i++) {
			$n=0;
			$r=0;
			while($i<count($this->cartons) && $this->cartons[$i]->product_id == $this->cartons[$i+1]->product_id){
				if( $this->cartons[$i]->qty == $this->cartons[$i]->colisage ) $n++;
				else $r=$r+$this->cartons[$i]->qty;

				$i++;
			}
			if( $this->cartons[$i]->qty == $this->cartons[$i]->colisage ) $n++;
			else $r=$r+$this->cartons[$i]->qty
			;

			if( !empty($this->cartons[$i]->colisage )){
				$n=$n+floor($r/$this->cartons[$i]->colisage);
				$r=$r%$this->cartons[$i]->colisage;
			}

			$product=new Product($this->db);
			$product->fetch($this->cartons[$i]->product_id);

			print "<tr class=\"oddeven\"><td>".($r!=0?$r ." + ":"").$n." x ". $this->cartons[$i]->colisage . " " . $product->getNomUrl() . "</td></tr>";
		}
	}

	public function getHash(){
		if( $this->type_element=='shipping'){

			$exp=new Expedition($this->db);
			$exp->fetch($this->fk_elt);
			$exp->fetch_optionals();

			$nb=0;
			$txt='';
			foreach ($exp->lines as $lexp) {
				$product = new Product($this->db);
				$product->fetch($lexp->fk_product);
				$product->fetch_optionals();


				if( !empty($product->array_options['options_est_cache_bordereau_livraison'])) continue;

				$txt.=$lexp->qty_shipped.$product->ref.'\n';
			}
			return hash('crc32',$txt);

		} else return 0;
	}

	function checkStatut(){
		if( $this->type_element == 'shipping') $elt=new Expedition($this->db);
		else if($this->type_element == 'commande') $elt=new Commande($this->db);
		else if($this->type_element == 'facture') $elt=new Facture($this->db);
		else return false;

		$sql = 'SELECT t.rowid';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $elt->table_element . ' as t';
		$sql .= ' WHERE t.rowid = '. $this->fk_elt;

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

	function getPDF(){
		global $conf;

		$elt=$this->loadElt();

		if($elt == null) return '';

		$ref = dol_sanitizeFileName($elt->ref);
		$file = '';

		if( !empty($elt->ref)){

			if( $this->type_element == 'shipping'){
				if (! empty($conf->expedition->dir_output)) {
					$dir = $conf->expedition->dir_output . '/sending/' . $ref ;
					$file = $dir . '/' . $ref . '.pdf';
				}
			} else if( $this->type_element == 'facture'){
					if ($conf->facture->dir_output) {
						$dir = $conf->facture->dir_output . "/" . $ref;
						$file = $conf->facture->dir_output . "/" . $ref . "/" . $ref . ".pdf";
					}
			}else if( $this->type_element == 'commande'){
				if ($conf->commande->dir_output)
				{
					$dir = $conf->commande->dir_output . "/" . $ref ;
					$file = $conf->commande->dir_output . "/" . $ref . "/" . $ref . ".pdf";
				}
			}
		} else return '';

		if (file_exists($file)){
			return $file;
		}
		return '';
	}
}
