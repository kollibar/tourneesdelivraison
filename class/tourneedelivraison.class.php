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
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneedelivraison_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneedelivraison_lines_contacts.class.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');

/**
 * Class for TourneeDeLivraison
 */
class TourneeDeLivraison extends TourneeGeneric
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'tourneedelivraison';
	public $nomelement = 'TourneeDeLivraison';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneedelivraison';

	/**
	 * @var int Field with ID of this object key in the child table
	 */
	public $fk_element = 'fk_tournee';

	public $table_element_line = 'tourneedelivraison_lines';

	public $class_element_line = 'TourneeDeLivraison_lines';

	//protected $childtables=array('tourneedelivraison_lines');
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
	public $picto = 'tourneedelivraison@tourneesdelivraison';


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
		'date_prochaine' => array('type'=>'date', 'label'=>'DateProchaineTournee', 'enabled'=>1, 'visible'=>1, 'position'=>53, 'notnull'=>-1,),
		'dureeTrajet' => array('type'=>'integer', 'label'=>'DureeTrajet', 'enabled'=>1, 'visible'=>1, 'position'=>51, 'notnull'=>-1,),
		'nb_tourneeunique' => array('type'=>'integer', 'label'=>'NbTourneeUnique', 'enabled'=>1, 'visible'=>+-1, 'position'=>200, 'notnull'=>-1,),
		'ae_datelivraisonidentique' => array('type'=>'integer', 'label'=>'AffecteAutoDateLivraisonOK', 'enabled'=>1, 'visible'=>1, 'position'=>80, 'notnull'=>1,'default'=>'-1','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'ae_1ere_future_cmde' => array('type'=>'integer', 'label'=>'AffecteAuto1ereFutureCmde', 'enabled'=>1, 'visible'=>1, 'position'=>81, 'notnull'=>1,'default'=>'-1','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'ae_1elt_par_cmde' => array('type'=>'integer', 'label'=>'AffectationAutoSi1EltParCmde', 'enabled'=>1, 'visible'=>1, 'position'=>82, 'notnull'=>1,'default'=>'-1','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Oui',)),
		'change_date_affectation' => array('type'=>'integer', 'label'=>'ChangeAutoDateLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>83, 'notnull'=>1,'default'=>'-1','arrayofkeyval'=>array('0'=>'Defaut','1'=>'Non', '2'=>'Manuelle seulement', '4'=>'manuelle et automatique',)),
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
	public $nb_tourneeunique;
	public $ae_datelivraisonidentique;
	public $ae_1ere_future_cmde;
	public $ae_1elt_par_cmde;
	public $change_date_affectation;
	public $model_pdf;
	public $date_prochaine;
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
		$line=new TourneeDeLivraison_lines($this->db, $parent);
		return $line;
	}

	public function getNumeroTourneeUniqueSuivante(User $user){
		if(empty($this->nb_tourneeunique)) {
			$r=0;$this->nb_tourneeunique=1;
		} else  $r=$this->nb_tourneeunique++;

		$this->update($user);
		return $r;
	}

	public function createTourneeUnique(User $user){
		if( $this->statut == TourneeGeneric::STATUS_VALIDATED){
			global $langs, $hookmanager, $extrafields;
			$error = 0;

			dol_syslog(__METHOD__, LOG_DEBUG);

			$object = new TourneeUnique($this->db);

			$this->db->begin();

			// Copie des champs
			$object->ref = $this->ref . $this->getNumeroTourneeUniqueSuivante($user);
			$object->label = $object->label;
			$object->fk_tourneedelivraison=$this->id;

			$object->description = $this->description;
			$object->note_public = $this->note_public;
			$object->note_private = $this->note_private;

			$object->statut=TourneeGeneric::STATUS_VALIDATED;

			$object->km=$this->km;
			$object->dureeTrajet = $this->dureeTrajet;
			$object->date_tournee = $this->date_prochaine;

			$object->ae_1elt_par_cmde=$this->ae_1elt_par_cmde;
			$object->ae_1ere_future_cmde=$this->ae_1ere_future_cmde;
			$object->ae_datelivraisonidentique=$this->ae_datelivraisonidentique;
			$object->change_date_affectation=$this->change_date_affectation;


			$this->update($user, $notrigger);

			// Rang to use
			$object->rang = $object->line_max($fk_parent_line)+1;

			// 	Create clone
			$object->context['createfrom'.$this->element] = 'createfrom'.$this->element;
			$result = $object->create($user,$notrigger);
			if ($result < 0) {
				$error++;
				$this->error = $object->error;
				$this->errors = $object->errors;
			} else {
				// clone des lignes
				$tabFait=array();
				$tabFait['soc']=array();
				$tabFait['tournee']=array($this->rowid=>$this->rowid);
				$tabFait=$this->createLineTourneeUnique($user, $result, $tabFait);

				// clone des catégories
				$object->copyCategorieFromObject($user, $this);
			}
			unset($object->context['createfrom'.$this->element]);


			// End
			if (!$error) {
				$this->db->commit();
				return $result;
			} else {
				$this->db->rollback();
				return -1;
			}
		}else{
			$this->error=get_class($this)."::createTourneeUnique ".$this->nomelement." status makes operation forbidden";
			$this->errors=array($this->nomelement.'StatusMakeOperationForbidden');
			return -2;
		}
	}

	public function createLineTourneeUnique($user, $tourneeunique, $tabFait, $fk_parent_line=0){
		foreach($this->lines as $line){
			if($line->type == TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $line->type == TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR){
				if( ! in_array($line->fk_soc,$tabFait['soc'])) {

					$soc = new Societe($this->db);
					$soc->fetch($line->fk_soc);

					if( $soc->status != 0){	//si le compte n'est pas clos
						$line->createLineTourneeUnique($user,$tourneeunique,$fk_parent_line);
					}
					$tabFait['soc'][$line->fk_soc]=$line->fk_soc;
				}
			} else if( $line->type == TourneeGeneric_lines::TYPE_TOURNEE ){
				if( ! in_array($line->fk_tournee_incluse,$tabFait['tournee']) ){
					$tab['tournee'][$line->fk_tournee_incluse]=$line->tournee_incluse;

					$obj=new TourneeDeLivraison($this->db);
					$obj->fetch($line->fk_tournee_incluse);

					$tabFait=$obj->createLineTourneeUnique($user,$tourneeunique, $tabFait,$fk_parent_line);
				}
			} else  {
				// erreur de type
			}

		}
		echo 'ok';
		return $tabFait;
	}


	public function showLinkedTourneeUnique(){
		/*
		print '<!-- showLinkedTourneeUnique -->';
		print load_fiche_titre($langs->trans('RelatedObjects'), $morehtmlright, '', 0, 0, 'showlinkedobjectblock');


		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder allwidth" data-block="showLinkedObject" data-element="'.$object->element.'"  data-elementid="'.$object->id.'"   >';

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Type").'</td>';
		print '<td>'.$langs->trans("Ref").'</td>';
		print '<td align="center"></td>';
		print '<td align="center">'.$langs->trans("Date").'</td>';
		print '<td align="right">'.$langs->trans("AmountHTShort").'</td>';
		print '<td align="right">'.$langs->trans("Status").'</td>';
		print '<td></td>';
		print '</tr>';

		$tu=new TourneeUnique($this->db);

		$sql = 'SELECT t.rowid, t.'.$this->fk_element;
		$sql .= ' FROM '.MAIN_DB_PREFIX.$tu->table_element.' as t';
		$sql .= ' WHERE t.'.$this->fk_element.' = '.$this->id;

		dol_syslog(get_class($this)."::fetch_lines", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);

			$i = 0;
			while ($i < $num) {
				$objp = $this->db->fetch_object($result);
				$tu->fetch($objp->rowid);
				$tu->fetch_optionals();
				$i++;


				global $linkedObjectBlock;
								$linkedObjectBlock = $objects;


								// Output template part (modules that overwrite templates must declare this into descriptor)
								$dirtpls=array_merge($conf->modules_parts['tpl'],array('/'.$tplpath.'/tpl'));
								foreach($dirtpls as $reldir)
								{
									if ($nboftypesoutput == ($nbofdifferenttypes - 1))    // No more type to show after
									{
										global $noMoreLinkedObjectBlockAfter;
										$noMoreLinkedObjectBlockAfter=1;
									}

									$res=@include dol_buildpath($reldir.'/'.$tplname.'.tpl.php');
									if ($res)
									{
										$nboftypesoutput++;
										break;
									}
								}


			}

			$this->db->free($result);

			$r=1;
			$this->error=$this->db->error();
		} else {
			$r=-3;
		}
		print "</table>
		</div>";
		return $r;
		*/
	}




}

?>
