<?php


require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique_lines.class.php');

class Tourneesdelivraison extends TourneeUnique_lines{
  public $date_livraison;
  public $thirdparty;

  public $table_element_line = '';
	public $class_element_line = '';

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
    $this->getTournee();
    $this->date_livraison = $this->parent->date_tournee;
    $this->thirdparty = new Societe($this->db);
    $this->thirdparty->fetch($this->fk_soc);
    $this->thirdparty->fetch_optionals();
    $this->element="tourneesdelivraison";
    return $result;
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
    return -1;
  }

  /**
	* Load object line in memory from the database
	*
	*	@return		int						<0 if KO, >0 if OK
	*/
	public function fetch_lines(){
    $this->lines=array();
    return 1;
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

    $this->getTournee();
    return $this->parent->getNomUrl($withpicto, $option, $notooltip, $morecss, $save_lastsearch_value);
  }

  /**
   *  Return label of the status
   *
   *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
   *  @return	string 			       Label of status
   */
  public function getLibStatut($mode=0)
  {
    $this->getTournee();
    return $this->parent->getLibStatut($mode);
  }

  /**
 	*  Return the status
 	*
 	*  @param	int		$status        Id status
 	*  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
 	*  @return string 			       Label of status
 	*/
  public function LibStatut($status, $mode=0)
  {
    $this->getTournee();
    return $this->parent->LibStatut($status, $mode);
  }
}


 ?>
