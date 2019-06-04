<?php

require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

dol_include_once('/tourneesdelivraison/class/tourneeobject.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines_contacts.class.php');


class TourneeGeneric_lines extends TourneeObject
{
	/**
	 * Type de ligne thirdparty
	 */
	const TYPE_THIRDPARTY = 0;

	/**
	 * Type de ligne tournée incluse
	 */
	const TYPE_TOURNEE = 1;




	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
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
				$line = $this->getNewContactLine();
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
	 *  Delete an order line
	 *
	 *	@param      User	$user		User object
	 *  @param      int		$lineid		Id of line to delete
	 *  @return     int        		 	>0 if OK, 0 if nothing to do, <0 if KO
	 */
	function deleteline($user=null, $lineid=0)
	{
		$this->db->begin();

		$sql = 'SELECT l.rowid, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.$this->table_element_line.' as l';
		$sql.= " WHERE l.rowid = ".$lineid;

		$result = $this->db->query($sql);
		if ($result)
		{
			$obj = $this->db->fetch_object($result);

			if ($obj) {
				// Delete line
				$line = $this->getNewContactLine();

				// For triggers
				$line->fetch($lineid);

				if ( $line->delete($user) > 0)	// si suppression ok
				{
					$this->db->commit();
					return 1;
				}
				else
				{
					$this->db->rollback();
					$this->error=$line->error;
					return -1;
				}
			}
			else
			{
				$this->db->rollback();
				return 0;
			}
		}
		else
		{
			$this->db->rollback();
			$this->error=$this->db->lasterror();
			return -1;
		}
	}

	/**
	* Delete all lines
	*
	*	@return int >0 if OK, <0 if KO
	*/
	public function deletelines($user=null)
	{
		$sql = 'SELECT l.rowid, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.$this->table_element_line.' as l';
		$sql.= ' WHERE l.'.$this->fk_element.' = '.$this->id;
		$sql .= ' ORDER BY l.rang, l.rowid';

		dol_syslog(get_class($this)."::fetch_lines", LOG_DEBUG);
		$result = $this->db->query($sql);
		$error=0;
		if ($result)
		{
			$num = $this->db->num_rows($result);

			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);
				$line = $this->getNewContactLine();
				$line->fetch($objp->rowid);

				if ( $line->delete($user) > 0)	// si suppression ok
				{
					$this->db->commit();
					return 1;
				}
				else
				{
					$this->db->rollback();
					$this->error=$line->error;
					$error-=1;
				}
			}
			$this->db->free($result);
			if ($error<0)
			{
				return $error;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			$this->error=$this->db->error();
			return -3;
		}
	}

	/**
	 *  Add a line in database
	 *
	 *  @param    	int				$rowid            	Id of line to update
	 *	@param		int				$fk_soc				id societe (0 si pas )
	 *	@param		int				$fk_people			id contact (0 si pas)
	 *	@param		str				$note_public			note public
	 *	@param		str				$note_private			note private
	 * 	@param		int				$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
	 * 	@param		int				$notrigger			disable line update trigger
	 *  @return   	int              					< 0 if KO, > 0 if OK
	 */
	function addline($fk_soc=0, $fk_people=0, $note_public='', $note_private='', $fk_parent_line=0, $notrigger=0)
	{
		global $mysoc, $conf, $langs, $user;

		dol_syslog(get_class($this)."::addline  fk_soc=$fk_soc, fk_people=$fk_people, fk_parent_line=$fk_parent_line, ");

		// Check parameters
		if(empty($fk_soc)) $fk_soc=0;
		if(empty($fk_people)) $fk_people=0;

		if ( $fk_soc==0 and $fk_people==0) return -1;

		if( empty($note_public)) $note_public='';
		if( empty($note_private)) $note_private='';


		$this->db->begin();


		// Insert line
		$this->line=$this->getNewContactLine($this->db);

		$this->line->context = $this->context;

		$this->line->fk_tournee_lines=$this->id;
		$this->line->fk_soc=$fk_soc;
		$this->line->fk_socpeople=$fk_people;
		$this->line->note_public=$note_public;
		$this->line->note_private=$note_private;
		$this->line->fk_parent_line=$fk_parent_line;

		$result=$this->line->create($user);

		if ($result > 0)
		{
			// Reorder if child line
			if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

			$this->db->commit();

			return $this->line->rowid;
		}
		else
		{
			$this->error=$this->line->error;
			dol_syslog(get_class($this)."::addline error=".$this->error, LOG_ERR);
			$this->db->rollback();
			return -2;
		}
	}

	/**
	 *  Update a line in database
	 *
	 *  @param    	int				$rowid            	Id of line to update
	 * 	@param		int				$type				Type of line (1=Tiers, 1=Tournee incluse)
	 *	@param		int				$fk_soc				id societe (0 si pas )
	 *	@param		int				$BL				nb de BL à imprimer
	 *	@param		int				$facture				nb de facture à imprimer
	 *	@param		str				$note_public			note public
	 *	@param		str				$note_private			note private
	 * 	@param		int				$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
	 * 	@param		int				$notrigger			disable line update trigger
	 *  @return   	int              					< 0 if KO, > 0 if OK
	 */
	function updateline($rowid, $fk_soc=0,$fk_people=0, $note_public='', $note_private='', $fk_parent_line=0, $notrigger=0)
	{
		global $conf, $mysoc, $langs, $user;

		dol_syslog(get_class($this)."::updateline id=$rowid, fk_soc=$fk_soc, fk_people=$fk_people, fk_parent_line=$fk_parent_line, ");

		if (! empty($this->brouillon))
		{
			$this->db->begin();

			// Check parameters
			if(empty($fk_soc)) $fk_soc=0;
			if(empty($fk_people)) $fk_people=0;

			if ( $fk_soc==0 and $fk_people==0) return -1;

			if( empty($note_public)) $note_public='';
			if( empty($note_private)) $note_private='';

			//Fetch current line from the database and then clone the object and set it in $oldline property
			$line = $this->getNewContactLine();
			$line->fetch($rowid);

			$staticline = clone $line;

			$line->oldline = $staticline;
			$this->line = $line;
			$this->line->context = $this->context;

			// Reorder if fk_parent_line change
			if (! empty($fk_parent_line) && ! empty($staticline->fk_parent_line) && $fk_parent_line != $staticline->fk_parent_line)
			{
				$rangmax = $this->line_max($fk_parent_line);
				$this->line->rang = $rangmax + 1;
			}

			$this->line->fk_rowid = $fk_rowid;
			$this->line->fk_soc=$fk_soc;
			$this->line->fk_people=$fk_people;
			$this->line->note_public=$note_public;
			$this->line->note_private=$note_private;
			$this->line->fk_parent_line = $fk_parent_line;

			$result=$this->line->update($user, $notrigger);
			if ($result > 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

				$this->db->commit();
				return $result;
			}
			else
			{
				$this->error=$this->line->error;

				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			$this->error=get_class($this)."::updateline Order status makes operation forbidden";
			$this->errors=array('OrderStatusMakeOperationForbidden');
			return -2;
		}
	}

	/**
	 * 	Create an array of order lines
	 *
	 * 	@return int		>0 if OK, <0 if KO
	 */
	function getLinesArray()
	{
		return $this->fetch_lines();
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

				//var_dump($record->id);
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

        $url = dol_buildpath('/tourneesdelivraison/'.$this->element.'_lines_card.php',1).'?id='.$this->id;

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
             $hookmanager->initHooks(array($this->element.'_linesdao'));
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
		$hookmanager->initHooks(array($this->element.'_linesdao'));
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
		return $this->LibStatut($this->status, $mode);
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
			$this->labelstatus[1] = $langs->trans('Enabled');
			$this->labelstatus[0] = $langs->trans('Disabled');
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
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
			elseif ($status == 0) return img_picto($this->labelstatus[$status],'statut5', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
		}
		elseif ($mode == 3)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4', '', false, 0, 0, '', 'valignmiddle');
			elseif ($status == 0) return img_picto($this->labelstatus[$status],'statut5', '', false, 0, 0, '', 'valignmiddle');
		}
		elseif ($mode == 4)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
			elseif ($status == 0) return img_picto($this->labelstatus[$status],'statut5', '', false, 0, 0, '', 'valignmiddle').' '.$this->labelstatus[$status];
		}
		elseif ($mode == 5)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4', '', false, 0, 0, '', 'valignmiddle');
			elseif ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5', '', false, 0, 0, '', 'valignmiddle');
		}
		elseif ($mode == 6)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4', '', false, 0, 0, '', 'valignmiddle');
			elseif ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5', '', false, 0, 0, '', 'valignmiddle');
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


	public function getBannerAddressSociete($htmlkey='bannerSociete')
	{
		$out = '<div id="'.$htmlkey.'">';
		$out .= '<div clear="both"></div>';

		$thirdparty=new Societe($this->db);
		$thirdparty->fetch($this->fk_soc);

		//$out .= '<div>'.$thirdparty->getFullName($langs).'</div>';
		$out .= '<div>'.$thirdparty->getNomUrl(1).'</div>';

		$out .= $thirdparty->getBannerAddress($hmlkey.'_address',$thirdparty);

		$out .= '</div>';

		return $out;
	}

	public function getBannerTourneeLivraison($htmlkey='bannerTourneeincluse')
	 {
		global $langs;
		$out = '<div id="'.$htmlkey.'">';
		$out .= '<div clear="both"></div>';

		$tournee=$this->getNewTournee();
		$tournee->fetch($this->fk_tournee_incluse);


		$out .= $langs->trans($tournee->nomelement).' : ' . $tournee->getNomUrl();
		$out .= '</a>';
		$out .= '</div>';

		return $out;
	}

	public function getBannerAddresseLivraison($htmlkey='bannerAdresseLivraison'){
		if( $this->fk_adresselivraison > 0){
			$contactLivraison=new Contact($this->db);
			$contactLivraison->fetch($this->fk_adresselivraison);


			$outdone=0;
			$coords = $contactLivraison->getFullAddress(1,', ',$conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT);
			if ($coords) {
				$out.=dol_print_address($coords, $htmlkey.'_'.$contactLivraison->id, $contactLivraison->element, $contactLivraison->id, 1, ', '); $outdone++;
			}

			return $out;
		} else return '';
	}


	public function getParent(){
		if( empty($this->parent)){
			$this->parent=$this->getNewTournee();
			$this->parent->fetch($this->fk_tournee);
		}
		return $this->parent;
	}

	public function getTournee(){
		return $this->getParent();
	}

	public function getSoc(){
		$soc=new Societe($this->db);
		$soc->fetch($this->fk_soc);

		return $soc;
	}


	public function getListeSoc($listeSoc=[], $params=[]) {

		if( $this->type == self::TYPE_THIRDPARTY){
			$listeSoc['soclineid'][]=$this->rowid;

			if( ! in_array($this->fk_soc,$listeSoc['soc'])) {
				$listeSoc['soc'][]=$this->fk_soc;	// ajout à la liste de soc
				$listeSoc['uniquesoclineid'][]=$this->rowid;
			}

			if( ! array_key_exists($fk_soc, $listeSoc['soc_contact']) )$listeSoc['soc_contact'][$fk_soc]=[];	// ajout à la liste des c

			if( count($this->lines)==0){
				$soc=new Societe($this->db);
				$soc->fetch($this->fk_soc);
				if( isValidEmail($soc->email) && ! in_array($soc->email, $listeSoc['mail']))  $listeSoc['mail'][]=$soc->email;
			} else {
				foreach ($this->lines as $line) {
					if( ! in_array($this->fk_people,$listeSoc['contact'])) $listeSoc['contact'][]= $this->fk_people;

					$contact=new Contact($this->db);
					$contact->fetch($line->fk_socpeople);

					if(! in_array($listeSoc['soc_contact'][$fk_soc], $line->fk_people)) $listeSoc['soc_contact'][$fk_soc][]= $line->fk_people;

					if( isValidEmail($contact->email) && ! in_array($contact->email, $listeSoc['mail'])) $listeSoc['mail'][]=$contact->email;
				}
			}
			return $listeSoc;

		} else if( $this->type == self::TYPE_TOURNEE){
			if( ! in_array($this->fk_tournee_incluse, $listeSoc['tournee']) ){
				$tournee=$this->getNewTournee();
				$tournee->fetch($this->fk_tournee_incluse);

				return $tournee->getListeSoc($listeSoc, $params);
			}
		}

		return $listeSoc;
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->id=0;
		$this->rowid=0;

		$this->note_public='note';
		$this->BL=rand(0,2);
		$this->facture=rand(0,1);
		$this->etiquettes=rand(0,1);
		$this->fk_tournee=0;
		$this->fk_tournee_incluse=0;
		$this->fk_soc=0;
		$this->fk_adresselivraison=0;
		$this->type=self::TYPE_THIRDPARTY;
		$this->tpstheorique=rand(0,50);
		$this->infolivraison='info livraison';
		$this->aucune_cmde=0;

	}

	public function derniereLivraisonLe(){
		$this->db->begin();

		$exp = new Expedition($this->db);

		$sql = 'SELECT t.rowid, t.date_delivery, t.fk_soc';
		$sql .= '  FROM '.MAIN_DB_PREFIX.$exp->table_element.' as t';
		$sql .= " WHERE t.fk_soc = " . $this->fk_soc;
		$sql .= " ORDER BY t.date_delivery DESC";

		dol_syslog(get_class($this)."::derniereLivraisonLe", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);

			$objp = $this->db->fetch_object($result);
			return $objp->date_delivery;
			/*
			$exp->fetch($objp->rowid);
			$exp->fetch_optionals();

			$this->db->free($result);

			return $exp->date_delivery;*/
		} else {
			return -1;
		}
	}

}

?>
