<?php
/**
 *	Class to manage generation of HTML components
 *	Only common components must be here.
 *
 *  TODO Merge all function load_cache_* and loadCache* (except load_cache_vatrates) into one generic function loadCacheTable
 */
class FormTourneesDeLivraison
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error='';

    /**
     * @var string[]    Array of error strings
     */
    public $errors = array();

	public $num;

	// Cache arrays

	/**
	 * Constructor
	 *
	 * @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 *  Output html form to select a tourneedelivraison
	 *
	 *	@param	string	$selected				Preselected type
	 *	@param	string	$htmlname       		Name of field in form
	 * 	@param	string	$filter				optional filters criteras (example: 's.rowid <> x', 's.client IN (1,3)')
	 *	@param	string	$showempty			Add an empty field (Can be '1' or text key to use on empty line like 'SelectThirdParty')
	 * 	@param	int		$forcecombo			Force to load all values and output a standard combobox (with no beautification)
	 * 	@param	array	$events				Ajax event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
	 *	@param	int		$limit				Maximum number of elements
	 *	@param	string	$morecss				Add more css styles to the SELECT component
	 *	@param  string	$moreparam      		Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
	 *	@param	string	$selected_input_value	Value of preselected input text (for use with ajax)
	 *	@param	int		$hidelabel			Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
	 *	@param	array	$ajaxoptions			Options for ajax_autocompleter
	 * 	@param  bool	$multiple					add [] in the name of element and add 'multiple' attribut (not working with ajax_autocompleter)
	 * 	@return	string			HTML string with select box for thirdparty.
	 */
	 public function select_tourneedelivraison($selected='', $htmlname='tourneeid', $filter='', $showempty='', $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array(), $multiple=false)
	{
		return $this->select_tournee('tourneedelivraison',$selected, $htmlname, $filter, $showempty, $forcecombo, $events, $limit, $morecss, $moreparam, $selected_input_value, $hidelabel, $ajaxoptions, $multiple);
	}
	public function select_tourneeunique($selected='', $htmlname='tourneeid', $filter='', $showempty='', $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array(), $multiple=false)
	{
		return $this->select_tournee('tourneeunique',$selected, $htmlname, $filter, $showempty, $forcecombo, $events, $limit, $morecss, $moreparam, $selected_input_value, $hidelabel, $ajaxoptions, $multiple);
	}


	public function select_tournee($typetournee, $selected='', $htmlname='tourneeid', $filter='', $showempty='', $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array(), $multiple=false)
	{
	// phpcs:enable

		global $conf,$user,$langs;

		if( empty($selected) || $selected==0) $selected='';


		$out='';
		if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && ! $forcecombo)
		{
			// No immediate load of all database
			$placeholder='';
			if ($selected && empty($selected_input_value))
			{
				dol_include_once('/tourneesdelivraison/class/'.$typetournee.'.class.php');
				if($typetournee=='tourneedelivraison') $tourneetmp = new TourneeDeLivraison($this->db);
				elseif($typetournee=='tourneeunique') $tourneetmp = new TourneeUnique($this->db);
				else die("Erreur: type d'objet non supporte: $typetournee");

				$tourneetmp->fetch($selected);
				$selected_input_value=$tourneetmp->name;
				unset($stourneetmp);
			}
			// mode 1
			$urloption='htmlname='.$htmlname.'&outjson=1&filter='.$filter.($showtype?'&showtype='.$showtype:'');
			// modifier la ligne suivante pour rendre automatique l'jout (ou non) de custom    et modifier le paramètre $conf->global->COMPANY_USE_SEARCH_TO_SELECT
			//$out.=  ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/custom/tourneedelivraison/ajax/tourneedelivraison.php', $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out.=  ajax_autocompleter($selected, $htmlname, dol_buildpath('/tourneesdelivraison/ajax/'.$typetournee.'.php'), $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out.='<style type="text/css">.ui-autocomplete { z-index: 250; }</style>';
			if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
			else if ($hidelabel > 1) {
				$placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
				if ($hidelabel == 2) {
					$out.=  img_picto($langs->trans("Search"), 'search');
				}
			}
			$out.= '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->{strtoupper($typetournee).'_SEARCH_AUTOFOCUS'}) ? 'autofocus' : '').' />';
			if ($hidelabel == 3) {
				$out.=  img_picto($langs->trans("Search"), 'search');
			}
		}
		else
		{
			$out.=$this->select_tournee_list($typetournee, $selected, $htmlname, $filter, $showempty, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam, $multiple);
		}

		return $out;
	}


	/**
	 *  Output html form to select a third party.
	 *  Note, you must use the select_company to get the component to select a third party. This function must only be called by select_company.
	 *
	 *	@param	string	$selected       Preselected type
	 *	@param  string	$htmlname       Name of field in form
	 *	@param  string	$filter         Optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
	 *	@param	string	$showempty		Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
	 * 	@param	int		$forcecombo		Force to use standard HTML select component without beautification
	 *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
	 *  @param	string	$filterkey		Filter on key value
	 *  @param	int		$outputmode		0=HTML select string, 1=Array
	 *  @param	int		$limit			Limit number of answers
	 *  @param	string	$morecss		Add more css styles to the SELECT component
	 *	@param  string	$moreparam      Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
	 *	@param  bool	$multiple       add [] in the name of element and add 'multiple' attribut
	 * 	@return	string					HTML string with
	 */
	function select_tourneedelivraison_list($selected='',$htmlname='tourneeid',$filter='',$showempty='', $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='', $multiple=false)
	{
		return $this->select_tournee_list('tourneedelivraison', $selected, $htmlname, $filter, $showempty, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam, $multiple);
	}

	function select_tourneeunique_list($selected='',$htmlname='tourneeid',$filter='',$showempty='', $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='', $multiple=false)
	{
		return $this->select_tournee_list('tourneeunique', $selected, $htmlname, $filter, $showempty, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam, $multiple);
	}

	function select_tournee_list($typetournee, $selected='',$htmlname='tourneeid',$filter='',$showempty='', $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='', $multiple=false)
	{
        // phpcs:enable

		global $conf,$user,$langs;

		$out='';
		$num=0;
		$outarray=array();

		if ($selected === '') $selected = array();
		else if (!is_array($selected)) $selected = array($selected);

		// Clean $filter that may contains sql conditions so sql code
		if (function_exists('testSqlAndScriptInject')) {
			if (testSqlAndScriptInject($filter, 3)>0) {
				$filter ='';
			}
		}


		dol_include_once('/tourneesdelivraison/class/'.$typetournee.'.class.php');

		// On recherche les tournee de livraison
		$sql = 'SELECT s.rowid, s.ref as name, s.label, s.statut, s.description';
		//$sql.= " FROM (".MAIN_DB_PREFIX . TourneeDeLivraison::table_element." as s )";
		$sql.= ' FROM (' . MAIN_DB_PREFIX . $typetournee . ' as s )';

		if ($filter) $sql.= " WHERE (".$filter.")";

		// Add criteria
		if ($filterkey && $filterkey != '')
		{
			if($filter) $sql .= ' AND ';
			else $sql.= " WHERE ";
      $sql .= '(';

			$prefix='%';	// Can use index
			// For natural search
			$scrit = explode(' ', $filterkey);
			$i=0;
			if (count($scrit) > 1) $sql.="(";
			foreach ($scrit as $crit) {
				if ($i > 0) $sql.=" AND ";
				$sql.="(s.label LIKE '".$this->db->escape($prefix.$crit)."%')";
				$i++;
			}
			if (count($scrit) > 1) $sql.=")";
			$sql .= ")";
		}
		$sql.=$this->db->order("label","ASC");
		$sql.=$this->db->plimit($limit, 0);
		// Build output string
		dol_syslog(get_class($this)."::select_".$typetournee."_list", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
		   	if (! $forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);	// A MODIFIER
			}

			// Construct $out and $outarray
			$out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparam?' '.$moreparam:'').' name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').'>'."\n";

			$textifempty='';
			// Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
			//if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
			if (! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))// A MODIFIER
			{
				if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
				else $textifempty.=$langs->trans("All");
			}
			if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);

					$label=$obj->name;

					if (empty($outputmode))
					{
						if (in_array($obj->rowid,$selected))
						{
							$out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
						}
						else
						{
							$out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
						}
					}
					else
					{
						array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));
					}

					$i++;
					if (($i % 10) == 0) $out.="\n";
				}
			}
			$out.= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		$this->result=array('nbof'.$typetournee=>$num);

		if ($outputmode) return $outarray;
		return $out;
	}

}
