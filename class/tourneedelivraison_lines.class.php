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
dol_include_once('/tourneesdelivraison/class/tourneedelivraison.class.php');
dol_include_once('/tourneesdelivraison/class/tourneegeneric_lines.class.php');
dol_include_once('/tourneesdelivraison/class/tourneedelivraison_lines_contacts.class.php');
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for TourneeDeLivraison_lines
 */
class TourneeDeLivraison_lines extends TourneeGeneric_lines
{


	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'tourneedelivraison_lines';
	public $nomelement = 'TourneeDeLivraison_lines';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'tourneedelivraison_lines';

	/**
	 * @var int Field with ID of this object key in the child table
	 */
	public $fk_element = 'fk_tournee_lines';


	public $table_element_line = 'tourneedelivraison_lines_contacts';

	public $class_element_line = 'TourneeDeLivraison_lines_contacts';
	//protected $childtables=array('tourneedelivraison_lines_contacts');
	public $lines = array();

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
	public $picto = 'tourneedelivraison_lines@tourneesdelivraison';


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
		'rang' => array('type'=>'integer', 'label'=>'Position', 'enabled'=>1, 'visible'=>-1, 'position'=>400, 'notnull'=>1,),
		'fk_tournee' => array('type'=>'integer:TourneeDeLivraison:tourneesdelivraison/class/tourneedelivraison.class.php', 'label'=>'TourneeDeLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>1,),
		'fk_tournee_incluse' => array('type'=>'integer:TourneeDeLivraison:tourneesdelivraison/class/tourneedelivraison.class.php', 'label'=>'TourneeDeLivraisonIncluse', 'enabled'=>1, 'visible'=>1, 'position'=>41, 'notnull'=>-1,),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToThirparty",),
		'type' => array('type'=>'integer', 'label'=>'Type', 'enabled'=>1, 'visible'=>1, 'position'=>39, 'notnull'=>1, 'arrayofkeyval'=>array('0'=>'ThirdParty', '1'=>'TourneeDeLivraison')),
		'tpstheorique' => array('type'=>'integer', 'label'=>'TempsTheorique', 'enabled'=>1, 'visible'=>1, 'position'=>55, 'notnull'=>-1,),
		'infolivraison' => array('type'=>'html', 'label'=>'InfoLivraison', 'enabled'=>1, 'visible'=>1, 'position'=>56, 'notnull'=>-1,),
		'fk_parent_line' => array('type'=>'integer', 'label'=>'ParentLine', 'enabled'=>1, 'visible'=>-1, 'position'=>80, 'notnull'=>-1,),
		'force_email_soc' => array('type'=> 'integer','label'=>'forceEmailSoc','enabled'=>1, 'visible'=>1, 'position'=>55, ),
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
	public $type;
	public $tpstheorique;
	public $infolivraison;
	public $fk_parent_line;
	public $force_email_soc;
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
		$line=new TourneeDeLivraison_lines_contacts($this->db, $parent);
		return $line;
	}

	public function getNewTournee(){
		$tournee= new TourneeDeLivraison($this->db);
		return $tournee;
	}


	public function createLineTourneeUnique(User $user,$fk_tourneeunique,$fk_parent_line=0){
		global $langs, $hookmanager, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		if( $this->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $this->type==TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR){

			$object = new TourneeUnique_lines($this->db);

			$this->db->begin();

			// Copie des champs
			$object->note_public = $this->note_public;
			$object->note_private = $this->note_private;
			$object->BL=$this->BL;
			$object->facture=$this->facture;
			$object->etiquettes=$this->etiquettes;
			$object->fk_soc=$this->fk_soc;
			$object->tpstheorique=$this->tpstheorique;
			$object->infolivraison=$this->infolivraison;

			$object->fk_parent_line=$fk_parent_line;
			$object->fk_tournee=$fk_tourneeunique;

			$object->type=$this->type;
			$object->fk_tournee_incluse=0;
			$object->aucune_cmde=0;
			$object->force_email_soc=$this->force_email_soc;
			$object->fk_tourneedelivraison_origine = $this->fk_tournee;

			// Rang to use
			$object->rang = $object->line_max($fk_parent_line)+1;

			// 	Create clone
			$object->context['createfrom'.$this->element] = 'createfrom'.$this->element;
			$result = $object->createCommon($user);
			if ($result < 0) {
				$error++;
				$this->error = $object->error;
				$this->errors = $object->errors;
			} else {
				// clone des lignes
				foreach($this->lines as $line){
					$line->createLineContactTourneeUnique($user,$result);
				}
			}

			unset($object->context['createfrom'.$this->element]);

			// End
			if (!$error) {
				$this->db->commit();
				return $object;
			} else {
				$this->db->rollback();
				return -1;
			}
		}else{	// erreur de type

		}
	}


}
