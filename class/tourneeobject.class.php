<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
dol_include_once('/categories/class/categorie.class.php');
dol_include_once('/tourneesdelivraison/lib/tournee.lib.php');

class TourneeObject extends CommonObject
{

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db, $parent=null)
	{
		global $conf, $langs, $user;

		if( $parent != null){
			$this->parent=$parent;
		}

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
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		dol_syslog(get_class($this)."::fetch($id)", LOG_DEBUG);
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 ) $this->rowid=$id;
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetch_lines();
		return $result;
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
				$line = $this->getNewLine($this);
				$line->fetch($objp->rowid);
				$line->fetch_optionals();

				$this->lines[$i] = $line;

				$i++;
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
		 * Load list of objects in memory from the database.
		 *
		 * @param  string      $sortorder    Sort Order
		 * @param  string      $sortfield    Sort field
		 * @param  int         $limit        limit
		 * @param  int         $offset       Offset
		 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
		 * @param  string      $filtermode   Filter mode (AND or OR)
		 * @return array|int                 int <0 if KO, array of pages if OK
		 */
		public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter=array(), $filtermode='AND')
		{
			global $conf;

			dol_syslog(__METHOD__, LOG_DEBUG);

			$records=array();

			$sql = 'SELECT';
			$sql .= ' t.rowid';
			// TODO Get all fields
			$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
			$sql .= ' WHERE t.entity = '.$conf->entity;
			// Manage filter
			$sqlwhere = array();
			if (count($filter) > 0) {
				foreach ($filter as $key => $value) {
					if ($key=='t.rowid') {
						$sqlwhere[] = $key . '='. $value;
					}
					elseif (strpos($key,'date') !== false) {
						$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
					}
					elseif ($key=='customsql') {
						$sqlwhere[] = $value;
					}
					else {
						$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
					}
				}
			}
			if (count($sqlwhere) > 0) {
				$sql .= ' AND (' . implode(' '.$filtermode.' ', $sqlwhere).')';
			}

			if (!empty($sortfield)) {
				$sql .= $this->db->order($sortfield, $sortorder);
			}
			if (!empty($limit)) {
				$sql .=  ' ' . $this->db->plimit($limit, $offset);
			}

			$resql = $this->db->query($sql);
			if ($resql) {
				$num = $this->db->num_rows($resql);

				while ($obj = $this->db->fetch_object($resql))
				{
					$record = new self($this->db);

					$record->id = $obj->rowid;
					// TODO Get other fields

					$records[$record->id] = $record;
				}
				$this->db->free($resql);

				return $records;
			} else {
				$this->errors[] = 'Error ' . $this->db->lasterror();
				dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

				return -1;
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
		dol_syslog(get_class($this)."::delete()", LOG_DEBUG);
		//if (! empty($this->table_element_line)) $this->deletelines($user);
		$this->deletelines($user);
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}


	/**
	* Delete all lines
	*
	*	@return int >0 if OK, <0 if KO
	*/
	public function deletelines(User $user, $notrigger = false)
	{
		dol_syslog(get_class($this)."::deletelines", LOG_DEBUG);
		if (! empty($this->table_element_line)) {
			$num=count($this->lines);
			for($i=0;$i<$num;$i++){
				$this->lines[$i]->delete($user,$notrigger);
				unset($this->lines[$i]);
			}
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
		//$this->checkDefaut();
		return $this->createCommon($user, $notrigger);
	}



	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		//$this->checkDefaut();
		return $this->updateCommon($user, $notrigger);
	}


	public function checkDefaut(){
		foreach($this->fields as $key => $val){
			if($empty($this->{$key}) && ! empty($val['default']) ){
				$this->{$key}=$val['default'];
			}
		}
	}

		/**
		 *  Return a link to the object card (with optionaly the picto)
		 *
		 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
		 *	@param	string	$option						On what the link point to ('nolink', ...)
	     *  @param	int  	$notooltip					1=Disable tooltip
	     *  @param  string  $morecss            		Add more css on link
	     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
		 *	@return	string								String with URL
		 */
		function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1)
		{
			global $db, $conf, $langs, $hookmanager;
	        global $dolibarr_main_authentication, $dolibarr_main_demo;
	        global $menumanager;

	        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

	        $result = '';

	        $label = '<u>' . $langs->trans($this->nomelement) . '</u>';
	        $label.= '<br>';
	        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

	        $url = dol_buildpath('/tourneesdelivraison/'.$this->element.'_card.php',1).'?id='.$this->id;

	        if ($option != 'nolink')
	        {
		        // Add param to save lastsearch_values or not
		        $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
		        if ($save_lastsearch_value == -1 && preg_match('/list\.php/',$_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
		        if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
	        }

	        $linkclose='';
	        if (empty($notooltip))
	        {
	            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
	            {
	                $label=$langs->trans("Show".$this->nomelement);
	                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
	            }
	            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
	            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';

	            /*
	             $hookmanager->initHooks(array($this->element.'dao'));
	             $parameters=array('id'=>$this->id);
	             $reshook=$hookmanager->executeHooks('getnomurltooltip',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
	             if ($reshook > 0) $linkclose = $hookmanager->resPrint;
	             */
	        }
	        else $linkclose = ($morecss?' class="'.$morecss.'"':'');

			$linkstart = '<a href="'.$url.'"';
			$linkstart.=$linkclose.'>';
			$linkend='</a>';

			$result .= $linkstart;
			if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
			if ($withpicto != 2) $result.= $this->ref;
			$result .= $linkend;
			//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

			global $action,$hookmanager;
			$hookmanager->initHooks(array($this->element.'dao'));
			$parameters=array('id'=>$this->id, 'getnomurl'=>$result);
			$reshook=$hookmanager->executeHooks('getNomUrl',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook > 0) $result = $hookmanager->resPrint;
			else $result .= $hookmanager->resPrint;

			return $result;
		}

		/**
		 *  Return label of the status
		 *
		 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
		 *  @return	string 			       Label of status
		 */
		public function getLibStatut($mode=0)
		{
			return $this->LibStatut($this->statut, $mode);
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

		 $this->labelstatus[0] = $langs->trans('Draft');
		 $this->labelstatus[1] = $langs->trans('Enabled');
		 $this->labelstatus[2] = $langs->trans('Clos');
		 $this->labelstatus[-1] = $langs->trans('Annulee');

		 $this->labelpicto[0] = 'statut0';
		 $this->labelpicto[1] = 'statut4';
		 $this->labelpicto[2] = 'statut6';
		 $this->labelpicto[-1] = 'statut5';

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
		 return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
	 }
	 elseif ($mode == 3)
	 {
		 return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
	 }
	 elseif ($mode == 4)
	 {
		 return img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
	 }
	 elseif ($mode == 5)
	 {
		 return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
	 }
	 elseif ($mode == 6)
	 {
		 return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$this->labelpicto[$status], '', false, 0, 0, '', 'valignmiddle');
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

		/**
		 * Initialise object with example values
		 * Id must be 0 if object instance is a specimen
		 *
		 * @return void
		 */
		public function initAsSpecimen()
		{
			$this->initAsSpecimenCommon();
		}


		/**
		 * Action executed by scheduler
		 * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
		 *
		 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
		 */
		//public function doScheduledJob($param1, $param2, ...)
		public function doScheduledJob()
		{
			global $conf, $langs;

			//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

			$error = 0;
			$this->output = '';
			$this->error='';

			dol_syslog(__METHOD__, LOG_DEBUG);

			$now = dol_now();

			$this->db->begin();

			// ...

			$this->db->commit();

			return $error;
		}


		/**
	 * Sets object to supplied categories.
	 *
	 * Deletes object from existing categories not supplied.
	 * Adds it to non existing supplied categories.
	 * Existing categories are left untouch.
	 *
	 * @param 	int[]|int 	$categories 	Category ID or array of Categories IDs
	 * @return	int							<0 if KO, >0 if OK
	 */
	public function setCategories($categories)
	{
		global $conf, $user;
		if( empty($conf->categorie->enabled)  || empty($user->rights->categorie->lire) ) return 1;

		require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

		$type=$this->element;
		$type_id=$this->element;
		$type_text=$this->element;

		// Handle single category
		if (!is_array($categories)) {
			$categories = array($categories);
		}

		// Get current categories
		// if( ! checkCategoriePourObjet($type)) return 1;

		$c=new Categorie($this->db);
		$existing = $c->containing($this->id, $type_id, 'id');

		// Diff
		if (is_array($existing)) {
			$to_del = array_diff($existing, $categories);
			$to_add = array_diff($categories, $existing);
		} else {
			$to_del = array(); // Nothing to delete
			$to_add = $categories;
		}

		$error = 0;

		// Process
		foreach ($to_del as $del) {
			if ($c->fetch($del) > 0) {
				$c->del_type($this, $type_text);
			}
		}
		foreach ($to_add as $add) {
			if ($c->fetch($add) > 0)
			{
				$result = $c->add_type($this, $type_text);
				if ($result < 0)
				{
					$error++;
					$this->error = $c->error;
					$this->errors = $c->errors;
					break;
				}
			}
		}

		return $error ? -1 : 1;
	}

	/**
 * Sets object to supplied categories.
 *
 * Deletes object from existing categories not supplied.
 * Adds it to non existing supplied categories.
 * Existing categories are left untouch.
 *
 * @return 	int[] 	Array of Categories IDs
 */
public function getCategories()
{
	global $conf, $user;
	if( empty($conf->categorie->enabled)  || empty($user->rights->categorie->lire) ) return 1;

	require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

	// Get current categories
	// if( ! checkCategoriePourObjet($type)) return array();
	$c=new Categorie($this->db);
	$existing = $c->containing($this->id, $this->element, 'id');

	return $existing;
}

public function supprimerCategories($categories){
	$c=$this->getCategories();
	$nc=array();
	foreach ($c as $tag) {
		if( ! in_array($tag,$categories)){
			$nc[]=$tag;
		}
	}
	return $this->setCategories($nc);
}

public function copyCategorieFromObject(User $user, $object){

	if( $this->element == $object->element ){
		return $this->setCategories($object->getCategories());
	}

	$new_type=-1;
	$nc = new Categorie($this->db);
	foreach ($nc->MAP_ID_TO_CODE as $key => $value) {
		if( $value == $this->element ) {
			$new_type = $key;
			break;
		}
	}
	if( $new_type <0 ){	// object ne supportant pas une catégorie
		return $new_type;
	}

	$categories=$object->getCategories();
	$err=0;

	$new_categories=array();

	foreach ($categories as $c) {
		$cat=new Categorie($this->db);
		$cat->fetch($c);
		$cat->fetch_optionals();

		$nc=cloneCategorieToAnotherObject($user, $cat, $new_type);
		if( $nc>0){
			$new_categories[]=$nc;
		}
		else  $err++;
	}
	$this->setCategories($new_categories);

	if( $err > 0) return -1;
	return 1;

}


/**
 * Clone and object into another one
 *
 * @param  	User 	$user      	User that creates
 * @param  	int 	$fromid     Id of object to clone
 * @return 	mixed 				New object created, <0 if KO
 */
	public function createFromClone(User $user, $fromid, $fk_parent=null, $parentid=0){
		global $langs, $hookmanager, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		//$object = new {$this->nomelement}($this->db);
		if( $this->nomelement=="TourneeDeLivraison") $object = new TourneeDeLivraison($this->db);
		else if( $this->nomelement=="TourneeDeLivraison_lines") $object = new TourneeDeLivraison_lines($this->db);
		else if( $this->nomelement=="TourneeDeLivraison_lines_contacts") $object = new TourneeDeLivraison_lines_contacts($this->db);
		else if( $this->nomelement=="TourneeUnique") $object = new Tourneeunique($this->db);
		else if( $this->nomelement=="TourneeUnique_lines") $object = new Tourneeunique_lines($this->db);
		else if( $this->nomelement=="TourneeUnique_lines_contacts") $object = new Tourneeunique_lines_contacts($this->db);

		$this->db->begin();

		// Load source object
		$object->fetchCommon($fromid);
		// récupération des tags
		$c=$object->getCategories();

		// Reset some properties
		unset($object->lines);
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		if( $this->nomelement == "TourneeUnique"){
			unset($object->masque_ligne);
		}
		if( $this->nomelement == "TourneeUnique_lines"){
			unset($object->aucune_cmde);
		}

		// ajout du parent
		if( !empty($fk_parent)  && !empty($parentid)){
			$object->{$fk_parent}=$parentid;
		}

		// Clear fields
		if( $this->nomelement == "TourneeUnique" && !empty($object->fk_tourneedelivraison)){
			$object->ref=$object->getTourneeDeLivraison()->ref . $object->getTourneeDeLivraison()->getNumeroTourneeUniqueSuivante($user);
		} else  $object->ref = "copy_of_".$object->ref;

		if( ! empty($this->title)) $object->title = $langs->trans("CopyOf")." ".$object->title;
		if( ! empty($this->label)) $object->label = $langs->trans("CopyOf")." ".$object->label;

		if( !empty($object->date_tournee)){
			$object->date_tournee="";
		}
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->element);
			foreach($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (! empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
				{
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

		// copie des tags
		$object->setCategories($c);

		// copie des lignes
		if( !empty($this->table_element_line) ){
			$sql = 'SELECT t.rowid, t.rang, t.'.$this->fk_element;
			$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element_line . ' as t';
			$sql .= ' WHERE t.'.$this->fk_element.' = '.$this->id;
			$sql .= ' ORDER BY t.rang';

			dol_syslog(get_class($this)."::createFromClone", LOG_DEBUG);
			$result = $this->db->query($sql);
			if ($result)
			{
				$num = $this->db->num_rows($result);

				$i = 0;
				while ($i < $num) {
					$objp = $this->db->fetch_object($result);
					$line = $this->getNewLine();

					$line->fetch($objp->rowid);
					$line->fetch_optionals();

					$new_line=$line->createFromClone($user, $objp->rowid, $object->fk_element, $object->id);

					if (is_object($new_line)) {
						$object->lines[]=$new_line;
					}

					$i++;
				}
				$this->db->free($result);
			} else {
				$this->error=$this->db->error();
				$error++;
			}
		}

		// End
		if (!$error) {
				$this->db->commit();
				return $object;
		} else {
				$this->db->rollback();
				return -1;
		}
	}

	public function field_view($key, $editable=false){
		global $langs,$form, $_SERVER, $action, $conf;
		$val=$this->fields[$key];
		$value=$this->$key;


		if( $key=='ae_1elt_par_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE + 1] .')';
		if( $key=='ae_1ere_future_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE + 1] .')';
		if( $key=='ae_datelivraisonidentique') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK + 1] .')';
		if( $key=='change_date_affectation') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE + 1] .')';

		print '<tr><td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans($val['label']);
		print '</td>';

		if ($action != 'edit_'.$key && $editable)
			print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit_'.$key.'&amp;id=' . $this->id . '">' . img_edit($langs->trans($val['label']), 1) . '</a></td>';

		print '</tr></table>';
		print '</td><td>';
		if ($action == 'edit_' . $key) {
			print '<form name="set_'.$key.'" action="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
			print '<input type="hidden" name="action" value="set_'.$key.'">';

			if( $val['type']=='date') {
				if( empty($value)) print $form->selectDate('', $key, '', '', '', "set_".$key,1,1);
				else print $form->selectDate($value, $key, '', '', '', "set_".$key);
			}
			else print $this->showInputField($val, 'label', $value, '', '', '', 0);

			print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
			print '</form>';
		} else {
			if( $val['type']=='date') print $value ? dol_print_date($value, 'day') : '&nbsp;';
			else print $this->showOutputField($val, $key, $value, '', '', '', 0);
		}
		print '</td>';
		print '</tr>';
	}



	public function field_create($key, $mode='create', $width=''){
		global $langs,$form, $_SERVER, $action,$conf;
		$val=$this->fields[$key];

		if (abs($val['visible']) != 1) return;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! verifCond($val['enabled'])) return;	// We don't want this field

		if( array_key_exists('default',$val) && $val['visible']==-1){
			print '<input type="hidden" value="'.$val['default'].'">';
			return;
		}

		if( $key=='ae_1elt_par_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE + 1] .')';
		if( $key=='ae_1ere_future_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE + 1] .')';
		if( $key=='ae_datelivraisonidentique') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK + 1] .')';
		if( $key=='change_date_affectation') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE + 1] .')';


		print '<tr id="field_'.$key.'">';
		print '<td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '"';
		if( ! empty($width)) print ' style="width:'.$width.';"';
		print '>';
		print $langs->trans($val['label']);
			if(!empty($val['help'])){
					print $form->textwithpicto('',$langs->trans($val['help']));
			}
		print '</td>';
		print '<td>';
		if( $mode == 'create'){
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
			else $value = GETPOST($key, 'alpha');
		} else {
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOSTISSET($key)?GETPOST($key, 'int'):$this->$key;
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOSTISSET($key)?GETPOST($key,'none'):$this->$key;
			else $value = GETPOSTISSET($key)?GETPOST($key, 'alpha'):$this->$key;
		}

		print $this->showInputField($val, $key, $value, '', '', '', 0);
		print '</td>';
		print '</tr>';
	}






}
