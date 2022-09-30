<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

dol_include_once('/tourneesdelivraison/class/tourneeobject.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines_contacts.class.php');

class TourneeGeneric extends TourneeObject
{
	/**
	 * Canceled status
	 */
	const STATUS_CANCELED = -1;
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;

	/**
	 * Closed (Sent, billed or not)
	 */
	const STATUS_CLOSED = 3;


	public $element = 'tourneegeneric';
	public $nomelement = 'TourneeGeneric';





	/**
	* Actualise les info générales de la tournée
	*
	* A FAIRE (par exemple calcul d'itinéraire, temps, nb de km ...)
	*
	*	@return		int						<0 if KO, >0 if OK
	*/
	public function actualise_infoG()
	{
		return 1;
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
		if ($this->statut == self::STATUS_DRAFT)
		{
			$this->db->begin();

			$sql = 'SELECT l.rowid, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.$this->table_element_line.' as l';
			$sql.= " WHERE l.rowid = ".$lineid;

			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);

				if ($obj)
				{

					// Delete line
					$line = $this->getNewLine();

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
		else
		{
			$this->error='ErrorDeleteLineNotAllowedByObjectStatus';
			return -1;
		}
	}


	/**
	 *  Add a line in database
	 *
	 *  @param    	int				$rowid            	Id of line to update
	 * 	@param		int				$type				Type of line (1=Tiers, 1=Tournee incluse)
	 *	@param		int				$fk_soc				id societe (0 si pas )
	 *	@param		int				$fk_tournee_incluse				id tournee de livraison incluse (0 si pas )
	 *	@param		int				$BL				nb de BL à imprimer
	 *	@param		int				$facture				nb de facture à imprimer
	 *	@param		int				$tempstheorique		temps théorique
	 *	@param		str				$infolivraison			info livraison
	 *	@param		int				$rang				rang
	 *	@param		str				$note_public			note public
	 *	@param		str				$note_private			note private
	 *  @param 		int       $force_email_soc   si true force l'envoi d'un email à soc
	 * 	@param		int				$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
	 * 	@param		int				$notrigger			disable line update trigger
	 *  @return   	int              					< 0 if KO, > 0 if OK
	 */
	function addline($type, $fk_soc=0, $fk_tournee_incluse=0, $BL=1, $facture=1, $etiquettes=1, $tempstheorique=0, $infolivraison='', $force_email_soc=0, $note_public='', $note_private='', $rang=-1, $fk_parent_line=0, $notrigger=0)
	{
		global $mysoc, $conf, $langs, $user;

		dol_syslog(get_class($this)."::addline type=$type, fk_soc=$fk_soc, fk_tournee_incluse=$fk_tournee_incluse, BL=$BL, facture=$facture, etiquettes=$etiquettes, infolivraison=$infolivraison, force_email_soc=$force_email_soc, fk_parent_line=$fk_parent_line, ");

		if ($this->statut == self::STATUS_DRAFT)
		{
			if(empty($BL)) $BL=0;
			if(empty($facture)) $facture=0;
			if(empty($etiquettes)) $etiquettes=0;
			if(empty($fk_soc)) $fk_soc=0;
			if(empty($fk_tournee_incluse)) $fk_tournee_incluse=0;
			if(empty($infolivraison)) $infolivraison='';
			if(empty($tempstheorique)) $tempstheorique=0;
			if(empty($rang)) $rang=-1;
			if(empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line=0;
			if(empty($force_email_soc)) $force_email_soc=0;

			$fk_tourneedelivraison_origine=0;

			// Check parameters
			if (
					$type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT && ($fk_soc==0 || $fk_soc==-1)
				|| $type==TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR && ($fk_soc==0 || $fk_soc==-1)
				|| $type == TourneeGeneric_lines::TYPE_TOURNEE && ($fk_tournee_incluse==0 or $fk_tournee_incluse==-1)
				|| $type != TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT && $type != TourneeGeneric_lines::TYPE_TOURNEE && $type != TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR
			) {
				$this->error=get_class($this)."::addline ".$langs->Trans('ErreurTdLAddLineType');
							$this->errors=array('ErreurTypeNonRemplitOuTypeNonValide');
							return -1;
			}

			if ($type !=TourneeGeneric_lines::TYPE_TOURNEE) $fk_tournee_incluse=0;
			elseif ($type != TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT && $type != TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR ) $fk_soc=0;

			if($type == TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $type == TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR){	// ligne de type client
				// le client ajouté ne doit pas être déjà présent dans la tournée
				$sql = 'SELECT l.rowid, l.type, l.fk_soc, l.rang, l.'.$this->fk_element.'  FROM ' . MAIN_DB_PREFIX . $this->table_element_line.' as l';
				$sql .= ' WHERE l.' . $this->fk_element . ' = ' . $this->id . ' AND ( l.type = ' . TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT . ' OR l.TYPE = ' . TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR . ')';

				$result = $this->db->query($sql);
				$error=0;
				if ($result) {
					$num = $this->db->num_rows($result);

					$i = 0;
					while ($i < $num) {
						$objp = $this->db->fetch_object($result);

						if( $objp->fk_soc == $fk_soc ){	// il y a déjà une ligne avec le même client
							$this->error=get_class($this)."::addline ".$langs->Trans('ErreurTdLAddLineRedondanceClient');
							$this->errors=array('ErreurTypeRedondanceClient');
							return -4;
						}
						$i++;
					}
				}
			}

			if( $type == TourneeGeneric_lines::TYPE_TOURNEE){
				// la tournée ajoutée ne doit pas être déjà présent dans la tournée
				$sql = 'SELECT l.rowid, l.type, l.fk_tournee_incluse, l.rang, l.'.$this->fk_element.'  FROM '.MAIN_DB_PREFIX.$this->table_element_line.' as l';
				$sql.= ' WHERE l.'.$this->fk_element.' = '.$this->id.' AND l.type = '.TourneeGeneric_lines::TYPE_TOURNEE;

				$result = $this->db->query($sql);
				$error=0;
				if ($result) {
					$num = $this->db->num_rows($result);

					$i = 0;
					while ($i < $num) {
						$objp = $this->db->fetch_object($result);

						if( $objp->fk_tournee_incluse == $fk_tournee_incluse ){	// il y a déjà une ligne avec le même client
							$this->error=get_class($this)."::addline ".$langs->Trans('ErreurTdLAddLineRedondanceTournee');
							$this->errors=array('ErreurTypeRedondanceTournee');
							return -4;
						}
						$i++;
					}
				}

				// ajouter vérification récursives
			}

			// Rang to use
			$rangtouse = $rang;
			if ($rangtouse == -1)
			{
				$rangmax = $this->line_max($fk_parent_line);
				$rangtouse = $rangmax + 1;
			}

			$this->db->begin();

			// Insert line
			$this->line=$this->getNewLine();

			$this->line->context = $this->context;

			$this->line->fk_tournee=$this->id;

			$this->line->type=$type;
			$this->line->fk_soc=$fk_soc;
			$this->line->fk_tournee_incluse=$fk_tournee_incluse;
			$this->line->BL=$BL;
			$this->line->facture=$facture;
			$this->line->etiquettes=$etiquettes;
			$this->line->tpstheorique=$tempstheorique;
			$this->line->infolivraison=$infolivraison;
			$this->line->note_public=$note_public;
			$this->line->note_private=$note_private;
			$this->line->fk_parent_line=$fk_parent_line;
			$this->line->rang = $rangtouse;
			$this->line->force_email_soc= $force_email_soc;

			$result=$this->line->create($user,$notrigger);

			if ($result > 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');	// A FAIRE

				// Mise a jour informations denormalisees au niveau de la commande meme
				$result=$this->actualise_infoG();
				if ($result > 0)
				{
					$this->db->commit();
					return $this->line->id;
				}
				else
				{
					$this->db->rollback();
					return -1;
				}
			}
			else
			{
				$this->error=$this->line->error;
				dol_syslog(get_class($this)."::addline error=".$this->error, LOG_ERR);
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			dol_syslog(get_class($this)."::addline status of ".$this->nomelement." must be Draft to allow use of ->addline()", LOG_ERR);
			return -3;
		}
	}

	/**
	 *  Update a line in database
	 *
	 *  @param    	int				$rowid            	Id of line to update
	 * 	@param		int				$type				Type of line (1=Tiers, 1=Tournee incluse)
	 *	@param		int				$fk_soc				id societe (0 si pas )
	 *	@param		int				$fk_tournee_incluse				id tournee de livraison incluse (0 si pas )
	 *	@param		int				$BL					nb de BL à imprimer (0/1/2)
	 *	@param		int				$facture				nb de facture à imprimer (1/0)
	 *	@param		int				$etiquettes			impression de planches d'étiquettes (1/0)
	 *	@param		int				$tempstheorique		temps théorique
	 *	@param		str				$infolivraison			info livraison
	 *	@param		int				$rang				rang
	 *	@param		str				$note_public			note public
	 *	@param		str				$note_private			note private
	 *  @param 		int       $force_email_soc   si true force l'envoi d'un email à soc
	 * 	@param		int				$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
	 * 	@param		int				$notrigger			disable line update trigger
	 *  @return   	int              					< 0 if KO, > 0 if OK
	 */
	function updateline($rowid, $type=0, $fk_soc=0,$fk_tournee_incluse=0,$BL=1,$facture=1, $etiquettes=1, $tempstheorique=0, $infolivraison='', $force_email_soc=0, $note_public='', $note_private='',  $rang=-1, $fk_parent_line=0, $notrigger=0)
	{
		global $conf, $mysoc, $langs, $user;

		dol_syslog(get_class($this)."::updateline id=$rowid, type=$type, fk_soc=$fk_soc, fk_tournee_incluse=$fk_tournee_incluse, BL=$BL, facture=$facture, fk_parent_line=$fk_parent_line, ");

		if ($this->statut == self::STATUS_DRAFT)
		{
			$this->db->begin();

			if (empty($BL)) $BL=0;
			if (empty($facture)) $facture=0;
			if (empty($etiquettes)) $etiquettes=0;
			if(empty($fk_soc)) $fk_soc=0;
			if(empty($fk_tournee_incluse)) $fk_tournee_incluse=0;
			if(empty($infolivraison)) $infolivraison='';
			if(empty($tempstheorique)) $tempstheorique=0;
			if(empty($rang)) $rang=-1;
			if(empty($note_public)) $note_public='';
			if(empty($note_private)) $note_private='';
			if(empty($force_email_soc)) $force_email_soc=0;



			// Check parameters
			if ($type==0 and ($fk_soc==0 or $fk_soc==-1) or $type == 1 and ($fk_tournee_incluse==0 or $fk_tournee_incluse==-1) or $type <0 or $type>1) return -1;
			if ($type==0){
				$fk_tournee_incluse=0;
			} else {
				$fk_soc=0;
			}

			//Fetch current line from the database and then clone the object and set it in $oldline property
			$line = $this->getNewLine();
			$line->fetch($rowid);

			if( $type != $line->type || $type==0 && $line->fk_soc != $fk_soc || $type==1 && $line->fk_tournee_incluse != $fk_tournee_incluse)
			{	// la ligne change de type ou de destinataire ou de tounrée incluse. Les lignes contact de cette ligne ne peuvent plus être valable, ils faut les supprimer
				$line->deletelines($user);
			}

			$staticline = clone $line;

			$line->oldline = $staticline;
			$this->line = $line;
			$this->line->context = $this->context;

			// Reorder if fk_parent_line change
			if (! empty($fk_parent_line) && ! empty($staticline->fk_parent_line) && $fk_parent_line != $staticline->fk_parent_line)
			{
				$rangmax = $this->line_max($fk_parent_line);
				$this->line->rang = $rangmax + 1;
			} else $this->line->rang=$rang;

			$this->line->fk_rowid = $fk_rowid;
			$this->line->type=$type;
			$this->line->fk_soc=$fk_soc;
			$this->line->fk_tournee_incluse=$fk_tournee_incluse;
			$this->line->BL=$BL;
			$this->line->facture=$facture;
			$this->line->etiquettes=$etiquettes;
			$this->line->tpstheorique=$tempstheorique;
			$this->line->infolivraison=$infolivraison;
			$this->line->note_public=$note_public;
			$this->line->note_private=$note_private;
			$this->line->fk_parent_line = $fk_parent_line;
			$this->line->force_email_soc= $force_email_soc;

			$result=$this->line->update($user, $notrigger);
			if ($result > 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

				// Mise a jour info denormalisees
				$this->actualise_infoG();

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
			$this->error=get_class($this)."::updateline ".$this->nomelement." status makes operation forbidden";
			$this->errors=array($this->nomelement.'StatusMakeOperationForbidden');
			return -2;
		}
	}

	public function addcontactline($lineid, $fk_soc=0, $fk_people=0, $no_email=0, $sms=0, $note_public='', $note_private='', $fk_parent_line=0, $notrigger=0)
	{
		global $conf, $mysoc, $langs, $user;

		if ($this->statut == self::STATUS_DRAFT)
		{
			$this->db->begin();

			//Fetch current line from the database
			$line = $this->getNewLine();
			$line->fetch($lineid);

			// transmet à la ligne la demande d'ajout
			$result = $line->addline($fk_soc, $fk_people, $no_email, $sms, $note_public, $note_private, $fk_parent_line, $notrigger);

			$this->db->commit();
		}
	}

	public function updatecontactline($contactrowid, $fk_soc=0, $fk_people=0, $no_email=0, $sms=0, $note_public='', $note_private='',  $fk_parent_line=0, $notrigger=0)
	{
		global $conf, $mysoc, $langs, $user;

		if ($this->statut == self::STATUS_DRAFT)
		{
			$this->db->begin();

			//Fetch current line from the database
			$line = $this->getNewLine();
			$line->fetch($lineid);

			// transmet à la ligne la demande d'ajout
			$result = $line->updateline($fk_soc, $fk_people, $note_public, $note_private, $no_email, $sms, $fk_parent_line, $notrigger);

			$this->db->commit();
		}
	}

	public function deletecontactline(User $user=null, $contactlineid)
	{
		if ($this->statut == self::STATUS_DRAFT)
		{
			$this->db->begin();

			$sql = 'SELECT l.rowid, l.'.$this->fk_element.'_lines';
			$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element.'_lines_contacts'.' as l';
			$sql.= " WHERE l.rowid = ".$contactlineid;

			$result = $this->db->query($sql);
			if ($result) {
				$obj = $this->db->fetch_object($result);

				if ($obj) {
					// Delete line
					$line = $this->getNewLine();

					// For triggers
					$line->fetch($obj->{$this->fk_element});

					if ( $line->deleteline($user,$contactlineid) > 0){	// si suppression ok
						$this->db->commit();
						return 1;
					} else {
						$this->db->rollback();
						$this->error=$line->error;
						return -1;
					}
				} else {
					$this->db->rollback();
					return 0;
				}
			} else {
				$this->db->rollback();
				$this->error=$this->db->lasterror();
				return -1;
			}
		} else {
			$this->error='ErrorDeleteLineNotAllowedByObjectStatus';
			return -1;
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
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->id=0;
		$this->rowid=0;
		$this->ref='specimen';
		$this->label='SPECIMEN';
		$this->description='Specimen de tournée de livraison';
		$this->note_public='';
		$this->note_private='';
		$this->statut=self::STATUS_VALIDATED;
		$this->km=150;
		$this->dureeTrajet=300;
		$this->nb_tourneeunique=0;
		$this->date_tournee=date('Y-m-d');
		$this->ae_datelivraisonidentique=1;
		$this->ae_1ere_future_cmde=1;
		$this->ae_1elt_par_cmde=1;
		$this->change_date_affectation=1;

		for ($i=0; $i < 3; $i++) {
			$line=$this->getNewLine();
			$line-initAsSpecimen();
			$this->lines[]=$line;
		}

	}

	function getRights(){
	 global $user;

	 $rights=$user->rights->tourneesdelivraison->{$this->element};
	 if( !isset($rights->note)) $rights->note=$rights->ecrire;
	 return $rights;
	}


	/**
	 *	Return HTML table for object lines
	 *	TODO Move this into an output class file (htmlline.class.php)
	 *	If lines are into a template, title must also be into a template
	 *	But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
	 *
	 *	@param	string		$action				Action code
	 *	@param  string		$seller            	Object of seller third party
	 *	@param	int			$selected		   	Object line selected
	 *	@param  int	    	$dateSelector      	1=Show also date range input fields
	 *	@return	void
	 */

	function printTourneeLines($action, $seller, $selected=0, $dateSelector=0, $ligneVide=false)
	{
		global $conf, $hookmanager, $langs, $user;

		// Define usemargins
		$usemargins=0;
		if (! empty($conf->margin->enabled) && ! empty($this->element) && in_array($this->element,array('facture','propal','commande'))) $usemargins=1;

		$num = count($this->lines);

		// Line extrafield
		require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafieldsline = new ExtraFields($this->db);
		$extralabelslines=$extrafieldsline->fetch_name_optionals_label($this->table_element_line);

		$parameters = array('num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline);
		$reshook = $hookmanager->executeHooks('printTourneeLineTitle', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks

		if (empty($reshook))
		{
			// Title line
		    print "<thead>\n";

			print '<tr class="liste_titre nodrag nodrop">';

			// colone select
			print '<td class="linecolselect" align="center" width="5">&nbsp;</td>';

			print '<td class="linecolmove" width="10"></td>';

			// Adds a line numbering column
			print '<td class="linecolnum" align="center" width="5">&nbsp;</td>';

			// Client
			print '<td class="linecolclient">'.$langs->trans('Customer').' // '.$langs->trans($this->nomelement).'</td>';


			// BL, facture, etiquettes
			if( $this->element != 'tourneeunique' || $this->statut == TourneeGeneric::STATUS_DRAFT){
				print '<td class="linecoldocs">'.$langs->trans('DocumentsLivraison').'</td>';
			}
			// cmde, livraison, factures...
			if( $this->element == 'tourneeunique' && $this->statut != TourneeGeneric::STATUS_DRAFT){
				print '<td class="linecolcmde">'.$langs->trans('Order').' // '. $langs->trans('Sending') . ' // ' . $langs->trans('Invoice') .'</td>';
			}

			/*
			// BL
			print '<td class="linecolbl">'.$langs->trans('BL').'</td>';

			// facture
			print '<td class="linecolfacture">'.$langs->trans('Facture').'</td>';

			// facture
			print '<td class="linecoletiquettes">'.$langs->trans('Etiquettes').'</td>';*/

			// tpsTheorique
			print '<td class="linecoltpstheo">'.$langs->trans('TempsTheorique').'</td>';

			// infoLivraison
			// print '<td class="linecolinfolivraison">'.$langs->trans('InfoLivraison').'</td>';
			print '<td class="linecolnote">'.$langs->trans('NotePublic').' // '.$langs->trans('NotePrivate').'</td>';


			// contact
			if( empty($conf->global->TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE)){
				print '<td class="linecolcontact">'.$langs->trans('Contact').'</td>';
			}


			/*print '<td class="linecoledit"></td>';  // No width to allow autodim

			print '<td class="linecoldelete" width="10"></td>';*/
			print '<td class="linecoldelete_edit" width="10"></td>';

			if($action == 'selectlines')
			{
			    print '<td class="linecolcheckall" align="center">';
			    print '<input type="checkbox" class="linecheckboxtoggle" />';
			    print '<script type="text/javascript">$(document).ready(function() {$(".linecheckboxtoggle").click(function() {var checkBoxes = $(".linecheckbox");checkBoxes.prop("checked", this.checked);})});</script>';
			    print '</td>';
			}

			print "</tr>\n";
			print "</thead>\n";
		}

		$var = true;
		$i	 = 0;

		print "<tbody>\n";
		foreach ($this->lines as $line)
		{
			// masquage des lignes suivant $this->masque_ligne
			$c=$line->getCategories();

			if( empty($line->note_public) && ( ! is_array($c) || count($c)==0 ) ){ // si pas de note plublic ni de tag
				if( $this->element == 'tourneeunique' && $this->statut != TourneeGeneric::STATUS_DRAFT
				 		&& ( $this->masque_ligne >= TourneeUnique::MASQUE_PASDECMDE && $line->aucune_cmde
							|| $this->masque_ligne >= TourneeUnique::MASQUE_SANSCMDE && count($line->lines_cmde) == 0
							)){
					continue;
				}
				if( $this->element == 'tourneeunique' && $this->statut != TourneeGeneric::STATUS_DRAFT
					&& $this->masque_ligne >=TourneeUnique::MASQUE_SANSCMDEAFF_OU_INC){
					$ok=1;
					foreach ($line->lines_cmde as $lcmde) {
						if( $lcmde->statut == TourneeUnique_lines_cmde::DATE_OK || $lcmde->statut == TourneeUnique_lines_cmde::DATE_NON_OK // il y a (au moins) une commande affectée
								|| $this->masque_ligne == TourneeUnique::MASQUE_SANSCMDEAFF_OU_INC && ($lcmde->statut==TourneeUnique_lines_cmde::NON_AFFECTE || $lcmde->statut == TourneeUnique_lines_cmde::NON_AFFECTE_DATE_OK)
							){
							$ok=0;
							break;
						}
					}
					if(!empty($ok)) continue;
				}
			}
			//Line extrafield
			$line->fetch_optionals();


			if (is_object($hookmanager))   // Old code is commented on preceding line.
			{
				if (empty($line->fk_parent_line))
				{
					$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline);
					$reshook = $hookmanager->executeHooks('printTourneeLine', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
				}
				else
				{
					$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline, 'fk_parent_line'=>$line->fk_parent_line);
					$reshook = $hookmanager->executeHooks('printTourneeSubLine', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
				}
			}
			if (empty($reshook))
			{
				$this->printTourneeLine($action,$line,$var,$num,$i,$dateSelector,$seller,$selected,$extrafieldsline, $ligneVide);
			}

			$i++;
			$var=!$var;

		}
		print "</tbody>\n";
	}

	/**
	 *	Return HTML content of a detail line
	 *	TODO Move this into an output class file (htmlline.class.php)
	 *
	 *	@param	string		$action				GET/POST action
	 *	@param CommonObjectLine $line		       	Selected object line to output
	 *	@param  string	    $var               	Is it a an odd line (true)
	 *	@param  int		    $num               	Number of line (0)
	 *	@param  int		    $i					I
	 *	@param  int		    $dateSelector      	1=Show also date range input fields
	 *	@param  string	    $seller            	Object of seller third party
	 *	@param	int			$selected		   	Object line selected
	 *  @param  int			$extrafieldsline	Object of extrafield line attribute
	 *	@return	void
	 */

	function printTourneeLine($action,$line,$var,$num,$i,$dateSelector,$seller,$selected=0,$extrafieldsline=0, $ligneVide=false)
	{
		global $conf,$langs,$user,$object,$hookmanager;
		global $form,$formtournee,$bc,$bcdd, $mysoc, $db;
		global $lineid;
		//global $permissionnote, $permissiontoadd, $permissioncreate, $permissiontodelete

		$object_rights = $this->getRights();

		$element=$this->element;

		$text=''; $description=''; $type=0;

		// Ligne en mode visu
		if ($action != 'editline' || $selected != $line->id)
		{

			// Output template part (modules that overwrite templates must declare this into descriptor)
			// Use global variables + $dateSelector + $seller and $buyer
			$dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl'));
			foreach($dirtpls as $reldir)
			{
				$tpl = dol_buildpath($reldir.'/tourneeline_view.tpl.php');
				if (empty($conf->file->strict_mode)) {
					$res=@include $tpl;
				} else {
					$res=include $tpl; // for debug
				}
				if ($res) break;
			}
		}

		// Ligne en mode update
		if ($this->statut == 0 && $action == 'editline' && $selected == $line->id)
		{
			$label = (! empty($line->label) ? $line->label : (($line->fk_product > 0) ? $line->product_label : ''));
			$placeholder=' placeholder="'.$langs->trans("Label").'"';

			$line->pu_ttc = price2num($line->subprice * (1 + ($line->tva_tx/100)), 'MU');

			// Output template part (modules that overwrite templates must declare this into descriptor)
			// Use global variables + $dateSelector + $seller and $buyer
			$dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl'));
			foreach($dirtpls as $reldir)
			{
				$tpl = dol_buildpath($reldir.'/tourneeline_edit.tpl.php');
				if (empty($conf->file->strict_mode)) {
					$res=@include $tpl;
				} else {
					$res=include $tpl; // for debug
				}
				if ($res) break;
			}
		}
	}

		/**
	 *	Show add free and predefined products/services form
	 *
	 *  @param	Societe			$seller				Object thirdparty who sell
	 *	@return	void
	 */

	function formAddTourneeLine($seller){

		global $conf,$user,$langs,$object,$hookmanager;
		global $form,$formtournee,$bcnd,$var;

		$object_rights = $this->getRights();

		// Line extrafield
		require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafieldsline = new ExtraFields($this->db);
		$extralabelslines=$extrafieldsline->fetch_name_optionals_label($this->table_element_line);

		// Output template part (modules that overwrite templates must declare this into descriptor)
		// Use global variables + $dateSelector + $seller and $buyer
		$dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl'));
		foreach($dirtpls as $reldir)
		{

			$tpl = dol_buildpath($reldir.'/tourneeline_create.tpl.php');
			if (empty($conf->file->strict_mode)) {
				$res=@include $tpl;
			} else {
				$res=include $tpl; // for debug
			}
			if ($res) break;
		}
	}

	/*
	listeSoc=array(
		'soc' => liste des soc incluse
		'tournee' => liste des tournees
		'mail' => liste des mails à envoyer
		'soc_contact' => array de clés fk_soc => [ liste des fk_contact ]
		'tournee_soc' => array de clés fk_tournee => [ lsite des fk_soc ]
		'tournee_tournee' => array de clés fk_tournee => [ liste des fk_tournee_incluse ]
	)*/

	public function getListeSoc($listeSoc=[], $params=[]){


		foreach (['soc','contact','mail','soc_contact','tournee_soc','tournee_tournee','tournee','soclineid', 'uniquesoclineid'] as $var) {
			if( ! array_key_exists($var, $listeSoc)) $listeSoc[$var]=[];
		}

		if( ! in_array($this->rowid, $listeSoc['tournee'])){
			$listeSoc['tournee'][] = $this->rowid;
			$listeSoc['tournee_soc'][$this->rowid]=[];

			foreach($this->lines as $line){
				$listeSoc=$line->getListeSoc($listeSoc, $params);

				if($line->type == TourneeGeneric_lines::TYPE_TOURNEE) $listeSoc['tournee_tournee'][$this->rowid][]= $line->fk_tournee_incluse;
				if($line->type == TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $line->type == TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR) $listeSoc['tournee_soc'][$this->rowid][]= $line->fk_soc;
			}
		}
		return $listeSoc;
	}

	public function genereMailToFromListeMail($listeSoc){
		global $mysoc;
		$i=0;
		$out='mailto:'.$mysoc->email;

		foreach($listeSoc['mail'] as $email){
			if( isValidEmail($email)){
				if( $i==0) $out.='?bcc='.$email;
				else $out.= ','.$email;
				//$out .= '?bcc='.$soc->email;
				$i++;
			}
		}
		return $out;
	}



	public function mailtoToAll(){
		$liste=$this->getListeSoc();
		return $this->genereMailToFromListeMail($liste);
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

		if ($action != 'edit_'.$key && $this->statut == TourneeGeneric::STATUS_DRAFT && $editable)
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


	/**
		 * Return HTML string to put an input field into a page
		 * Code very similar with showInputField of extra fields
		 *
		 * @param  array   		$val	       Array of properties for field to show
		 * @param  string  		$key           Key of attribute
		 * @param  string  		$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
		 * @param  string  		$moreparam     To add more parameters on html input tag
		 * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
		 * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
		 * @param  string|int		$morecss       Value for css to define style/length of field. May also be a numeric.
		 * @return string
		 */
		function showInputField($val, $key, $value, $moreparam='', $keysuffix='', $keyprefix='', $morecss=0, $nonewbutton = 0)
		{
			global $conf,$langs,$form;

			if (! is_object($form))
			{
				require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
				$form=new Form($this->db);
			}


			$out='';
	        $type='';
	        $param = array();
	        $param['options']=array();
	        $size =$val['size'];
	        // Because we work on extrafields
	        if(preg_match('/^integer:(.*):(.*)/i', $val['type'], $reg)){
	            $param['options']=array($reg[1].':'.$reg[2]=>'N');
	            $type ='link';
	        } elseif(preg_match('/^link:(.*):(.*)/i', $val['type'], $reg)) {
	            $param['options']=array($reg[1].':'.$reg[2]=>'N');
	            $type ='link';
	        } elseif(preg_match('/^sellist:(.*):(.*):(.*):(.*)/i', $val['type'], $reg)) {
	            $param['options']=array($reg[1].':'.$reg[2].':'.$reg[3].':'.$reg[4]=>'N');
	            $type ='sellist';
	        } elseif(preg_match('/varchar\((\d+)\)/', $val['type'],$reg)) {
	            $param['options']=array();
	            $type ='varchar';
	            $size=$reg[1];
	        } elseif(preg_match('/varchar/', $val['type'])) {
	            $param['options']=array();
	            $type ='varchar';
	        } elseif(is_array($val['arrayofkeyval'])) {
	            $param['options']=$val['arrayofkeyval'];
	            $type ='select';
	        } else {
	            $param['options']=array();
	            $type =$val['type'];
	        }

			$label=$val['label'];
			//$elementtype=$val['elementtype'];	// Seems not used
			$default=$val['default'];
			$computed=$val['computed'];
			$unique=$val['unique'];
			$required=$val['required'];

			$langfile=$val['langfile'];
			$list=$val['list'];
			$hidden=abs($val['visible'])!=1?1:0;

			$objectid = $this->id;


			if ($computed)
			{
				if (! preg_match('/^search_/', $keyprefix)) return '<span class="opacitymedium">'.$langs->trans("AutomaticallyCalculated").'</span>';
				else return '';
			}


			// Use in priority showsize from parameters, then $val['css'] then autodefine
			if (empty($morecss) && ! empty($val['css']))
			{
				$showsize = $val['css'];
			}
			if (empty($morecss))
			{
				if ($type == 'date')
				{
					$morecss = 'minwidth100imp';
				}
				elseif ($type == 'datetime')
				{
					$morecss = 'minwidth200imp';
				}
				elseif (in_array($type,array('int','integer','price')) || preg_match('/^double(\([0-9],[0-9]\)){0,1}/',$type))
				{
					$morecss = 'maxwidth75';
	                        }elseif ($type == 'url')
				{
					$morecss='minwidth400';
				}
				elseif ($type == 'boolean')
				{
					$morecss='';
				}
				else
				{
					if (round($size) < 12)
					{
						$morecss = 'minwidth100';
					}
					else if (round($size) <= 48)
					{
						$morecss = 'minwidth200';
					}
					else
					{
						$morecss = 'minwidth400';
					}
				}
			}

			if (in_array($type,array('date','datetime')))
			{
				$tmp=explode(',',$size);
				$newsize=$tmp[0];

				$showtime = in_array($type,array('datetime')) ? 1 : 0;

				// Do not show current date when field not required (see selectDate() method)
				if (!$required && $value == '') $value = '-1';

				// TODO Must also support $moreparam
				$out = $form->selectDate($value, $keyprefix.$key.$keysuffix, $showtime, $showtime, $required, '', 1, (($keyprefix != 'search_' && $keyprefix != 'search_options_') ? 1 : 0), 0, 1);
			}
			elseif (in_array($type,array('int','integer')))
			{
				$tmp=explode(',',$size);
				$newsize=$tmp[0];
				$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$newsize.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
			}
			elseif (preg_match('/varchar/', $type))
			{
				$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
			}
			elseif (in_array($type, array('mail', 'phone', 'url')))
			{
				$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.dol_escape_htmltag($value).'" '.($moreparam?$moreparam:'').'>';
			}
			elseif ($type == 'text')
			{
				if (! preg_match('/search_/', $keyprefix))		// If keyprefix is search_ or search_options_, we must just use a simple text field
				{
					require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
					$doleditor=new DolEditor($keyprefix.$key.$keysuffix,$value,'',200,'dolibarr_notes','In',false,false,false,ROWS_5,'90%');
					$out=$doleditor->Create(1);
				}
				else
				{
					$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.dol_escape_htmltag($value).'" '.($moreparam?$moreparam:'').'>';
				}
			}
			elseif ($type == 'html')
			{
				if (! preg_match('/search_/', $keyprefix))		// If keyprefix is search_ or search_options_, we must just use a simple text field
				{
					require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
					$doleditor=new DolEditor($keyprefix.$key.$keysuffix,$value,'',200,'dolibarr_notes','In',false,false,! empty($conf->fckeditor->enabled) && $conf->global->FCKEDITOR_ENABLE_SOCIETE,ROWS_5,'90%');
					$out=$doleditor->Create(1);
				}
				else
				{
					$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.dol_escape_htmltag($value).'" '.($moreparam?$moreparam:'').'>';
				}
			}
			elseif ($type == 'boolean')
			{
				$checked='';
				if (!empty($value)) {
					$checked=' checked value="1" ';
				} else {
					$checked=' value="1" ';
				}
				$out='<input type="checkbox" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.$checked.' '.($moreparam?$moreparam:'').'>';
			}
			elseif ($type == 'price')
			{
				if (!empty($value)) {		// $value in memory is a php numeric, we format it into user number format.
					$value=price($value);
				}
				$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> '.$langs->getCurrencySymbol($conf->currency);
			}
			elseif (preg_match('/^double(\([0-9],[0-9]\)){0,1}/',$type))
			{
				if (!empty($value)) {		// $value in memory is a php numeric, we format it into user number format.
					$value=price($value);
				}
				$out='<input type="text" class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> ';
			}
			elseif ($type == 'select')
			{
				$out = '';
				if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->MAIN_EXTRAFIELDS_USE_SELECT2))
				{
					include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
					$out.= ajax_combobox($keyprefix.$key.$keysuffix, array(), 0);
				}

				$out.='<select class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
	                if((! isset($val['default'])) ||($val['notnull']!=1))$out.='<option value="0">&nbsp;</option>';
				foreach ($param['options'] as $key => $val)
				{
					if ((string) $key == '') continue;
					list($val, $parent) = explode('|', $val);
					$out.='<option value="'.$key.'"';
					$out.= (((string) $value == (string) $key)?' selected':'');
					$out.= (!empty($parent)?' parent="'.$parent.'"':'');
					$out.='>'.$val.'</option>';
				}
				$out.='</select>';
			}
			elseif ($type == 'sellist')
			{
				$out = '';
				if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->MAIN_EXTRAFIELDS_USE_SELECT2))
				{
					include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
					$out.= ajax_combobox($keyprefix.$key.$keysuffix, array(), 0);
				}

				$out.='<select class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
				if (is_array($param['options']))
				{
					$param_list=array_keys($param['options']);
					$InfoFieldList = explode(":", $param_list[0]);
					$parentName='';
					$parentField='';
					// 0 : tableName
					// 1 : label field name
					// 2 : key fields name (if differ of rowid)
					// 3 : key field parent (for dependent lists)
					// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
					$keyList=(empty($InfoFieldList[2])?'rowid':$InfoFieldList[2].' as rowid');


					if (count($InfoFieldList) > 4 && ! empty($InfoFieldList[4]))
					{
						if (strpos($InfoFieldList[4], 'extra.') !== false)
						{
							$keyList='main.'.$InfoFieldList[2].' as rowid';
						} else {
							$keyList=$InfoFieldList[2].' as rowid';
						}
					}
					if (count($InfoFieldList) > 3 && ! empty($InfoFieldList[3]))
					{
						list($parentName, $parentField) = explode('|', $InfoFieldList[3]);
						$keyList.= ', '.$parentField;
					}

					$fields_label = explode('|',$InfoFieldList[1]);
					if (is_array($fields_label))
					{
						$keyList .=', ';
						$keyList .= implode(', ', $fields_label);
					}

					$sqlwhere='';
					$sql = 'SELECT '.$keyList;
					$sql.= ' FROM '.MAIN_DB_PREFIX .$InfoFieldList[0];
					if (!empty($InfoFieldList[4]))
					{
						// can use SELECT request
						if (strpos($InfoFieldList[4], '$SEL$')!==false) {
							$InfoFieldList[4]=str_replace('$SEL$','SELECT',$InfoFieldList[4]);
						}

						// current object id can be use into filter
						if (strpos($InfoFieldList[4], '$ID$')!==false && !empty($objectid)) {
							$InfoFieldList[4]=str_replace('$ID$',$objectid,$InfoFieldList[4]);
						} else {
							$InfoFieldList[4]=str_replace('$ID$','0',$InfoFieldList[4]);
						}
						//We have to join on extrafield table
						if (strpos($InfoFieldList[4], 'extra')!==false)
						{
							$sql.= ' as main, '.MAIN_DB_PREFIX .$InfoFieldList[0].'_extrafields as extra';
							$sqlwhere.= ' WHERE extra.fk_object=main.'.$InfoFieldList[2]. ' AND '.$InfoFieldList[4];
						}
						else
						{
							$sqlwhere.= ' WHERE '.$InfoFieldList[4];
						}
					}
					else
					{
						$sqlwhere.= ' WHERE 1=1';
					}
					// Some tables may have field, some other not. For the moment we disable it.
					if (in_array($InfoFieldList[0],array('tablewithentity')))
					{
						$sqlwhere.= ' AND entity = '.$conf->entity;
					}
					$sql.=$sqlwhere;
					//print $sql;

					$sql .= ' ORDER BY ' . implode(', ', $fields_label);

					dol_syslog(get_class($this).'::showInputField type=sellist', LOG_DEBUG);
					$resql = $this->db->query($sql);
					if ($resql)
					{
						$out.='<option value="0">&nbsp;</option>';
						$num = $this->db->num_rows($resql);
						$i = 0;
						while ($i < $num)
						{
							$labeltoshow='';
							$obj = $this->db->fetch_object($resql);

							// Several field into label (eq table:code|libelle:rowid)
							$notrans = false;
							$fields_label = explode('|',$InfoFieldList[1]);
							if (is_array($fields_label))
							{
								$notrans = true;
								foreach ($fields_label as $field_toshow)
								{
									$labeltoshow.= $obj->$field_toshow.' ';
								}
							}
							else
							{
								$labeltoshow=$obj->{$InfoFieldList[1]};
							}
							$labeltoshow=dol_trunc($labeltoshow,45);

							if ($value == $obj->rowid)
							{
								foreach ($fields_label as $field_toshow)
								{
									$translabel=$langs->trans($obj->$field_toshow);
									if ($translabel!=$obj->$field_toshow) {
										$labeltoshow=dol_trunc($translabel,18).' ';
									}else {
										$labeltoshow=dol_trunc($obj->$field_toshow,18).' ';
									}
								}
								$out.='<option value="'.$obj->rowid.'" selected>'.$labeltoshow.'</option>';
							}
							else
							{
								if (! $notrans)
								{
									$translabel=$langs->trans($obj->{$InfoFieldList[1]});
									if ($translabel!=$obj->{$InfoFieldList[1]}) {
										$labeltoshow=dol_trunc($translabel,18);
									}
									else {
										$labeltoshow=dol_trunc($obj->{$InfoFieldList[1]},18);
									}
								}
								if (empty($labeltoshow)) $labeltoshow='(not defined)';
								if ($value==$obj->rowid)
								{
									$out.='<option value="'.$obj->rowid.'" selected>'.$labeltoshow.'</option>';
								}
								if( $key=='ae_1elt_par_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE+1] .')';
								if( $key=='ae_1ere_future_cmde') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE+1] .')';
								if( $key=='ae_datelivraisonidentique') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK+1] .')';
								if( $key=='change_date_affectation') $val['arrayofkeyval'][0] .= ' ('. $val['arrayofkeyval'][$conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE+1] .')';
								if (!empty($InfoFieldList[3]) && $parentField)
								{
									$parent = $parentName.':'.$obj->{$parentField};
								}

								$out.='<option value="'.$obj->rowid.'"';
								$out.= ($value==$obj->rowid?' selected':'');
								$out.= (!empty($parent)?' parent="'.$parent.'"':'');
								$out.='>'.$labeltoshow.'</option>';
							}

							$i++;
						}
						$this->db->free($resql);
					}
					else {
						print 'Error in request '.$sql.' '.$this->db->lasterror().'. Check setup of extra parameters.<br>';
					}
				}
				$out.='</select>';
			}
			elseif ($type == 'checkbox')
			{
				$value_arr=explode(',',$value);
				$out=$form->multiselectarray($keyprefix.$key.$keysuffix, (empty($param['options'])?null:$param['options']), $value_arr, '', 0, '', 0, '100%');
			}
			elseif ($type == 'radio')
			{
				$out='';
				foreach ($param['options'] as $keyopt => $val)
				{
					$out.='<input class="flat '.$morecss.'" type="radio" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'');
					$out.=' value="'.$keyopt.'"';
					$out.=' id="'.$keyprefix.$key.$keysuffix.'_'.$keyopt.'"';
					$out.= ($value==$keyopt?'checked':'');
					$out.='/><label for="'.$keyprefix.$key.$keysuffix.'_'.$keyopt.'">'.$val.'</label><br>';
				}
			}
			elseif ($type == 'chkbxlst')
			{
				if (is_array($value)) {
					$value_arr = $value;
				}
				else {
					$value_arr = explode(',', $value);
				}

				if (is_array($param['options'])) {
					$param_list = array_keys($param['options']);
					$InfoFieldList = explode(":", $param_list[0]);
					$parentName='';
					$parentField='';
					// 0 : tableName
					// 1 : label field name
					// 2 : key fields name (if differ of rowid)
					// 3 : key field parent (for dependent lists)
					// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
					$keyList = (empty($InfoFieldList[2]) ? 'rowid' : $InfoFieldList[2] . ' as rowid');

					if (count($InfoFieldList) > 3 && ! empty($InfoFieldList[3])) {
						list ( $parentName, $parentField ) = explode('|', $InfoFieldList[3]);
						$keyList .= ', ' . $parentField;
					}
					if (count($InfoFieldList) > 4 && ! empty($InfoFieldList[4])) {
						if (strpos($InfoFieldList[4], 'extra.') !== false) {
							$keyList = 'main.' . $InfoFieldList[2] . ' as rowid';
						} else {
							$keyList = $InfoFieldList[2] . ' as rowid';
						}
					}

					$fields_label = explode('|', $InfoFieldList[1]);
					if (is_array($fields_label)) {
						$keyList .= ', ';
						$keyList .= implode(', ', $fields_label);
					}

					$sqlwhere = '';
					$sql = 'SELECT ' . $keyList;
					$sql .= ' FROM ' . MAIN_DB_PREFIX . $InfoFieldList[0];
					if (! empty($InfoFieldList[4])) {

						// can use SELECT request
						if (strpos($InfoFieldList[4], '$SEL$')!==false) {
							$InfoFieldList[4]=str_replace('$SEL$','SELECT',$InfoFieldList[4]);
						}

						// current object id can be use into filter
						if (strpos($InfoFieldList[4], '$ID$')!==false && !empty($objectid)) {
							$InfoFieldList[4]=str_replace('$ID$',$objectid,$InfoFieldList[4]);
						} else {
							$InfoFieldList[4]=str_replace('$ID$','0',$InfoFieldList[4]);
						}

						// We have to join on extrafield table
						if (strpos($InfoFieldList[4], 'extra') !== false) {
							$sql .= ' as main, ' . MAIN_DB_PREFIX . $InfoFieldList[0] . '_extrafields as extra';
							$sqlwhere .= ' WHERE extra.fk_object=main.' . $InfoFieldList[2] . ' AND ' . $InfoFieldList[4];
						} else {
							$sqlwhere .= ' WHERE ' . $InfoFieldList[4];
						}
					} else {
						$sqlwhere .= ' WHERE 1=1';
					}
					// Some tables may have field, some other not. For the moment we disable it.
					if (in_array($InfoFieldList[0], array ('tablewithentity')))
					{
						$sqlwhere .= ' AND entity = ' . $conf->entity;
					}
					// $sql.=preg_replace('/^ AND /','',$sqlwhere);
					// print $sql;

					$sql .= $sqlwhere;
					dol_syslog(get_class($this) . '::showInputField type=chkbxlst',LOG_DEBUG);
					$resql = $this->db->query($sql);
					if ($resql) {
						$num = $this->db->num_rows($resql);
						$i = 0;

						$data=array();

						while ( $i < $num ) {
							$labeltoshow = '';
							$obj = $this->db->fetch_object($resql);

							$notrans = false;
							// Several field into label (eq table:code|libelle:rowid)
							$fields_label = explode('|', $InfoFieldList[1]);
							if (is_array($fields_label)) {
								$notrans = true;
								foreach ( $fields_label as $field_toshow ) {
									$labeltoshow .= $obj->$field_toshow . ' ';
								}
							} else {
								$labeltoshow = $obj->{$InfoFieldList[1]};
							}
							$labeltoshow = dol_trunc($labeltoshow, 45);

							if (is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
								foreach ( $fields_label as $field_toshow ) {
									$translabel = $langs->trans($obj->$field_toshow);
									if ($translabel != $obj->$field_toshow) {
										$labeltoshow = dol_trunc($translabel, 18) . ' ';
									} else {
										$labeltoshow = dol_trunc($obj->$field_toshow, 18) . ' ';
									}
								}

								$data[$obj->rowid]=$labeltoshow;
							} else {
								if (! $notrans) {
									$translabel = $langs->trans($obj->{$InfoFieldList[1]});
									if ($translabel != $obj->{$InfoFieldList[1]}) {
										$labeltoshow = dol_trunc($translabel, 18);
									} else {
										$labeltoshow = dol_trunc($obj->{$InfoFieldList[1]}, 18);
									}
								}
								if (empty($labeltoshow))
									$labeltoshow = '(not defined)';

									if (is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
										$data[$obj->rowid]=$labeltoshow;
									}

									if (! empty($InfoFieldList[3]) && $parentField) {
										$parent = $parentName . ':' . $obj->{$parentField};
									}

									$data[$obj->rowid]=$labeltoshow;
							}

							$i ++;
						}
						$this->db->free($resql);

						$out=$form->multiselectarray($keyprefix.$key.$keysuffix, $data, $value_arr, '', 0, '', 0, '100%');
					} else {
						print 'Error in request ' . $sql . ' ' . $this->db->lasterror() . '. Check setup of extra parameters.<br>';
					}
				}
			}
			elseif ($type == 'link')
			{
				$param_list=array_keys($param['options']);				// $param_list='ObjectName:classPath'
				$showempty=(($required && $default != '')?0:1);
				$out=$form->selectForForms($param_list[0], $keyprefix.$key.$keysuffix, $value, $showempty);
			}
			elseif ($type == 'password')
			{
				// If prefix is 'search_', field is used as a filter, we use a common text field.
				$out='<input type="'.($keyprefix=='search_'?'text':'password').'" class="flat '.$morecss.'" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'>';
			}
			elseif ($type == 'array')
			{
				$newval = $val;
				$newval['type'] = 'varchar(256)';

				$out='';

				$inputs = array();
				if(! empty($value)) {
					foreach($value as $option) {
						$out.= '<span><a class="'.dol_escape_htmltag($keyprefix.$key.$keysuffix).'_del" href="javascript:;"><span class="fa fa-minus-circle valignmiddle"></span></a> ';
						$out.= $this->showInputField($newval, $keyprefix.$key.$keysuffix.'[]', $option, $moreparam, '', '', $showsize).'<br></span>';
					}
				}

				$out.= '<a id="'.dol_escape_htmltag($keyprefix.$key.$keysuffix).'_add" href="javascript:;"><span class="fa fa-plus-circle valignmiddle"></span></a>';

				$newInput = '<span><a class="'.dol_escape_htmltag($keyprefix.$key.$keysuffix).'_del" href="javascript:;"><span class="fa fa-minus-circle valignmiddle"></span></a> ';
				$newInput.= $this->showInputField($newval, $keyprefix.$key.$keysuffix.'[]', '', $moreparam, '', '', $showsize).'<br></span>';

				if(! empty($conf->use_javascript_ajax)) {
					$out.= '
						<script type="text/javascript">
						$(document).ready(function() {
							$("a#'.dol_escape_js($keyprefix.$key.$keysuffix).'_add").click(function() {
								$("'.dol_escape_js($newInput).'").insertBefore(this);
							});

							$(document).on("click", "a.'.dol_escape_js($keyprefix.$key.$keysuffix).'_del", function() {
								$(this).parent().remove();
							});
						});
						</script>';
				}
			}
			if (!empty($hidden)) {
				$out='<input type="hidden" value="'.$value.'" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'"/>';
			}
			/* Add comments
			 if ($type == 'date') $out.=' (YYYY-MM-DD)';
			 elseif ($type == 'datetime') $out.=' (YYYY-MM-DD HH:MM:SS)';
			 */
			return $out;
		}


		public function getLineById($lineid){
			foreach ($this->lines as $line) {
				if( $line->id == $lineid ) return $line;
			}
			return null;
		}

		/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force the model to using ('' to not force)
	 *  @param		Translate	$outputlangs	object lang to use for translations
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
		 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs,$hidedetails=0, $hidedesc=0, $hideref=0,$moreparams=null)
	{
		global $conf,$langs;

		$langs->load("tourneesdelivraison@tourneesdelivraison","other","sending");

		if (! dol_strlen($modele)) {

			$modele = 'fourgon';

			if ($this->modelpdf) {
				$modele = $this->modelpdf;
			} elseif (! empty($conf->global->TOURNEESDELIVRAISON_ADDON_PDF)) {
				$modele = $conf->global->TOURNEESDELIVRAISON_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/tourneesdelivraison/doc/";

		$this->fetch_origin();

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref,$moreparams);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
 	/**
 	 *	Read linked origin object
 	 *
 	 *	@return		void
 	 */
	function fetch_origin(){
		// phpcs:enable
		$this->origin=new $this->nomelement($this->db);
		$this->origin->fetch($this->origin_id);
	}

	public function getTournee(){
		return $this;
	}

	public function generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams=''){
		foreach ($modellist as $model) {
			$result = $this->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
			if($result <= 0 ) return $result;
		}
		return $result;
	}

	public function deleteAllDocuments(){
		// A FAIRE
		return;
	}


	public function printRecap(){
		global $conf, $hookmanager, $langs, $user, $form;

		print "<tr id=\"row-recap\" class=\"oddeven\">\n";

	// colone select
	print '<td class="linecolselect" align="center" width="5">&nbsp;</td>';

	print '<td class="linecolmove" width="10"></td>';

	// Adds a line numbering column
	print '<td class="linecolnum" align="center" width="5">&nbsp;</td>';

	// Client
	print '<td class="linecolclient">'.$langs->trans('Total').'</td>';


	// BL, facture, etiquettes
	if( $this->element != 'tourneeunique' || $this->statut == TourneeGeneric::STATUS_DRAFT){
		print '<td class="linecoldocs">&nbsp;</td>';
	}
	// cmde, livraison, factures...
	if( $this->element == 'tourneeunique' && $this->statut != TourneeGeneric::STATUS_DRAFT){
		print '<td class="linecolcmde">';
		print '<table>';
		print '<tr><td>'.$langs->trans('Order').'</td><td>'. $langs->trans('Sending') . '</td><td>' . $langs->trans('Invoice') .'</td></tr>';
		$totalCmde=$this->getTotalWeightVolume("commande");
		$totalExp=$this->getTotalWeightVolume("shipping");
		$totalFact=$this->getTotalWeightVolume("facture");
		print '<tr><td>';
		if (!empty($totalCmde['weight'])) print showDimensionInBestUnit($totalCmde['weight'], 0, "weight", $outputlangs) . '<br>';
		if (!empty($totalCmde['volume'])) print showDimensionInBestUnit($totalCmde['volume'], 0, "volume", $outputlangs);
		print '</td><td>';
		if (!empty($totalExp['weight'])) print showDimensionInBestUnit($totalExp['weight'], 0, "weight", $outputlangs) . '<br>';
		if (!empty($totalExp['volume'])) print showDimensionInBestUnit($totalExp['volume'], 0, "volume", $outputlangs);
		print '</td><td>';
		if (!empty($totalFact['weight'])) print showDimensionInBestUnit($totalFact['weight'], 0, "weight", $outputlangs) . '<br>';
		if (!empty($totalFact['volume'])) print showDimensionInBestUnit($totalFact['volume'], 0, "volume", $outputlangs);
		print '</td></tr>';
		print '<tr><td>';

		print '</td><td>';

		print '</td><td>';

		print '</td></tr>';
		print '</table>';

		print '</td>';
	}

	/*
	// BL
	print '<td class="linecolbl">'.$langs->trans('BL').'</td>';

	// facture
	print '<td class="linecolfacture">'.$langs->trans('Facture').'</td>';

	// facture
	print '<td class="linecoletiquettes">'.$langs->trans('Etiquettes').'</td>';*/

	// tpsTheorique
	print '<td class="linecoltpstheo">&nbsp;</td>';

	// infoLivraison
	// print '<td class="linecolinfolivraison">'.$langs->trans('InfoLivraison').'</td>';
	print '<td class="linecolnote">';
	// Categories
	if ($this->element == 'tourneeunique' && $this->statut!=TourneeGeneric::STATUS_DRAFT && count($this->lines) > 0 && ! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
		$langs->load('categories');
		print '<form name="supprimerTags" action="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="supprimerTags">';

		print '<input type="submit" class="button" value="' . $langs->trans('SupprimerTousLesTags') . '">';


		$cate_arbo = $form->select_all_categories($this->lines[0]->element, null, null, null, null, 1);
		print $form->multiselectarray('cats_suppr', $cate_arbo, array(), '', 0, '', 0, '90%');
		print '</form>';
	}
	print '</td>';

	// contact
	//print '<td class="linecolcontact">&nbsp;</td>';


	/*print '<td class="linecoledit"></td>';  // No width to allow autodim

	print '<td class="linecoldelete" width="10"></td>';*/
	print '<td class="linecoldelete_edit" width="10"></td>';


	print "</tr>\n";
	}

	public function supprimerCategoriesLines($categories){
		$err=0;
		foreach ($this->lines as $line) {
			if( $line->supprimerCategories($categories) <0 ) $err++;
		}
		return $err;
	}


}

?>
