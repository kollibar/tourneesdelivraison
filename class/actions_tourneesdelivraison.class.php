<?php
/* Copyright (C) 2019 Thomas Kolli <thomas@brasserieteddybeer.fr>
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
 * \file    tourneesdelivraison/class/actions_tourneesdelivraison.class.php
 * \ingroup tourneesdelivraison
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsTourneesDeLivraison
 */
class ActionsTourneesDeLivraison
{
    /**
     * @var DoliDB Database handler.
     */
    public $db;

    /**
     * @var string Error code (or message)
     */
    public $error = '';

    /**
     * @var array Errors
     */
    public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
	    $this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	function getNomUrl($parameters,&$object,&$action)
	{
		global $db,$langs,$conf,$user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
	    if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))	    // do something only for the context 'somecontext1' or 'somecontext2'
	    {
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
		}

		if (! $error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user, $langs;

	    $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
	    if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
	    {
	        foreach($parameters['toselect'] as $objectid)
	        {
	            // Do action on each object id
	        }
	    }

	    if (! $error) {
	        $this->results = array('myreturn' => 999);
	        $this->resprints = 'A text to show';
	        return 0; // or return 1 to replace standard code
	    } else {
	        $this->errors[] = 'Error message';
	        return -1;
	    }
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user, $langs;

	    $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
	    if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
	    {
	        $this->resprints = '<option value="0"'.($disabled?' disabled="disabled"':'').'>'.$langs->trans("TourneesDeLivraisonMassAction").'</option>';
	    }

	    if (! $error) {
	        return 0; // or return 1 to replace standard code
	    } else {
	        $this->errors[] = 'Error message';
	        return -1;
	    }
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters		Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs=$langs;

		$ret=0; $deltemp=array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{

		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters		Array of parameters
	 * @param   Object	$pdfhandler   	PDF builder handler
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs=$langs;

		$ret=0; $deltemp=array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{

		}

		return $ret;
	}

	/* Add here any other hooked methods... */


  public function constructCategory(&$parameters, &$object){

    global $hookmanager;

	if( empty($hookmanager->resArray) ) $hookmanager->resArray=[];

	$hookmanager->resArray[]=array(
			'id'=>445409, // 110
			'code'=>'tourneeunique',
			'cat_fk'=>'tourneeunique',
			'cat_table'=>'tourneeunique',
			'obj_class'=>'TourneeUnique',
			'obj_table'=>'tourneeunique');
	$hookmanager->resArray[]=array(
			'id'=>445410, // 111
			'code'=>'tourneeunique_lines',
			'cat_fk'=>'tourneeunique_lines',
			'cat_table'=>'tourneeunique_lines',
			'obj_class'=>'TourneeUnique_lines',
			'obj_table'=>'tourneeunique_lines');
	$hookmanager->resArray[]=array(
			'id'=>445405, // 112
			'code'=>'tourneedelivraison',
			'cat_fk'=>'tourneedelivraison',
			'cat_table'=>'tourneedelivraison',
			'obj_class'=>'TourneeDeLivraison',
			'obj_table'=>'tourneedelivraison');
	$hookmanager->resArray[]=array(
			'id'=>445406, // 113
			'code'=>'tourneedelivraison_lines',
			'cat_fk'=>'tourneedelivraison_lines',
			'cat_table'=>'tourneedelivraison_lines',
			'obj_class'=>'TourneeDeLivraison_lines',
			'obj_table'=>'tourneedelivraison_lines');

    return 1;

  }

  public function checkRowPerms(&$parameters, &$row, &$action){
    global $hookmanager, $user;

    if( empty($hookmanager->resArray) ) $hookmanager->resArray=[];

    if( $parameters["table_element_line"] == "tourneeunique_lines"){
      if( $user->rights->tourneesdelivraison->tourneeunique->ecrire ) $hookmanager->resArray['perm']=1;
      return 1;
    }
    if( $parameters["table_element_line"] == "tourneedelivraison_lines"){
      if( $user->rights->tourneesdelivraison->tourneedelivraison->ecrire ) $hookmanager->resArray['perm']=1;
      return 1;
    }
  }

}
