<?php
/* Copyright (C) 2004-2018 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018	   Nicolas ZABOURI 	<info@inovea-conseil.com>
 * Copyright (C) 2019 Thomas Kolli <thomas@brasserieteddybeer.fr>
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
 * 	\defgroup   tourneesdelivraison     Module TourneesDeLivraison
 *  \brief      TourneesDeLivraison module descriptor.
 *
 *  \file       htdocs/tourneesdelivraison/core/modules/modTourneesDeLivraison.class.php
 *  \ingroup    tourneesdelivraison
 *  \brief      Description and activation file for module TourneesDeLivraison
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module TourneesDeLivraison
 */
class modTourneesDeLivraison extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 445405;		// TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'tourneesdelivraison';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "crm";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

		// Module label (no space allowed), used if translation string 'ModuleTourneesDeLivraisonName' not found (TourneesDeLivraison is name of module).
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleTourneesDeLivraisonDesc' not found (TourneesDeLivraison is name of module).
		$this->description = "TourneesDeLivraisonDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "TourneesDeLivraison description (Long)";

		$this->editor_name = 'SCOP Au-delà des nuages';
		$this->editor_url = 'https://www.brasserieteddybeer.fr';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';

        //Url to the file with your last numberversion of this module
        //$this->url_last_version = 'http://www.example.com/versionmodule.txt';
		// Key used in llx_const table to save module status enabled/disabled (where TOURNEESDELIVRAISON is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='generic';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
		    'triggers' => 1,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
			'login' => 0,                                    	// Set this to 1 if module has its own login method file (core/login)
			'substitutions' => 1,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
			'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
			'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
		    'tpl' => 1,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
			'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'models' => 1,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
			'css' => array('/tourneesdelivraison/css/tourneesdelivraison.css.php'),	// Set this to relative path of css file if module has its own css file
	 		'js' => array('/tourneesdelivraison/js/tourneesdelivraison.js.php'),          // Set this to relative path of js file if module must load a js on all pages
			'hooks' => array('category', 'rowinterface'), 	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
			//'hooks' => array('data'=>array('constructCategory'), 'entity'=>'0'), 	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
			'moduleforexternal' => 0							// Set this to 1 if feature of module are opened to external users
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/tourneesdelivraison/temp","/tourneesdelivraison/subdir");
		$this->dirs = array("/tourneesdelivraison/temp",
												"/tourneesdelivraison/tourneeunique",
												"/tourneesdelivraison/tourneedelivraison");

		// Config pages. Put here list of php page, stored into tourneesdelivraison/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@tourneesdelivraison");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array("QRcodeScanneur"=>"QRcodeScanneur");		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->requiredby = array();	// List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();	// List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("tourneesdelivraison@tourneesdelivraison");
		//$this->phpmin = array(5,4);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(14,0);		// Minimum version of Dolibarr required by module
		$this->warnings_activation = array();			// Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array();		// Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'TourneesDeLivraisonWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('TOURNEESDELIVRAISON_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('TOURNEESDELIVRAISON_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		// Constants
		$this->const = array();
		$r=0;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "fourgon";
		$this->const[$r][3] = 'Nom du gestionnaire de generation des bordereaux de tournées en PDF';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "0";
		$this->const[$r][3] = "Change automatiquement la date de livraison d'un élément lors de son affectation dans une tournée";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = "0";
		$this->const[$r][3] = "Affecte automatique un élément (livraison/facture) s'il est le seul liée à une commande déjà affectée";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE";
		$this->const[$r][1] = "ouinon";
		$this->const[$r][2] = "0";
		$this->const[$r][3] = "Affecte automatique la première future commande d'un client lié à une tournée";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK";
		$this->const[$r][1] = "ouinon";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = "Affecte automatique un élément (commande/livraison) si la date de livraison est celle de la tournée";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_POIDS_BL";
		$this->const[$r][1] = "ouinon";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = "Affiche le poids sur les BL de tournée de livraison";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE";
		$this->const[$r][1] = "ouinon";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = "Affiche les contacts en dessous du nom du magasin (au lieu de la colonne tout à droite)";
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_DELETE","ouinon","0","Validation avant de supprimer une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CANCEL","ouinon","0","Validation avant d'annuler' une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CLONE","ouinon","0","Validation avant de cloner une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CLOSE","ouinon","0","Validation avant de clore une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_VALIDATE","ouinon","0","Validation avant de valider une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_GENERERDOCS","ouinon","0","Validation avant de générer TOUS les docs d'une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_UNVALIDATE","ouinon","0","Validation avant de repasser une Tournée à l'état brouillon",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_AFFECTATIONAUTO","ouinon","0","Validation avant de faire les affectations automatique des éléments d'une tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_REOPEN","ouinon","0","Validation avant de réouvrir une Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CHANGESTATUTELT","ouinon","0","Validation avant de changer le statut d'un élément",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CHANGEDATEELT","ouinon","0","Validation avant de changer la date d'un élément",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_DELETELINE","ouinon","0","Validation avant de supprimer une ligne de Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_DELETECONTACT","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_EXP_SHIPPED","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_EXP_REOPEN","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CMDE_REOPEN","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CMDE_SHIPPED","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CMDE_CLASSIFYBILLED","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_ASK_CMDE_CLASSIFYUNBILLED","ouinon","0","Validation avant de supprimer le contact d'une ligne Tournée",0);
		$r++;
		$this->const[$r]=array("TOURNEESDELIVRAISON_SMS","ouinon","0","Active la gestion des sms",0);
		$r++;


		$this->const[$r]=array('TOURNEESDELIVRAISON_DRAFT_WATERMARK', 'chaine', 'brouillon', 'Watermark brouillon sur les tournées de livraison', 1, 'allentities', 1);
		$r++;

		$this->const[$r]=array('TOURNEESDELIVRAISON_FREE_TEXT', 'chaine', '', 'Texte libre sur les bordereau de tournées de livraison', 1, 'allentities', 1);
		$r++;

		$this->const[$r]=array('TOURNEESDELIVRAISON_URL_REPLACE', 'chaine', '', 'à remplacer par:', 0); // à retirer // A FAIRE
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_URL_ORIGIN', 'chaine', '', 'élément de l\'url à remplacer', 0);	// A retirer // A FAIRE
		$r++;

		$this->const[$r]=array('TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE', 'chaine', '1', 'désactive l\'autoupdate des documents', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE', 'chaine', '0', 'désactive l\'autoupdate des documents', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_OPENROUTESERVICE_APIKEY', 'chaine', '', 'Openrouteservice.org API KEY', 1, 'allentities', 1);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_COMMANDE', 'chaine', '', 'liste de catégories à supprimer après ajout de commande sur une ligne de tournée', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CATEGORIES_CLIENT_A_NE_PAS_AFFICHER', 'chaine', '', 'liste de catégories clients à afficher (dans l\'affichage des tournées)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_NOTE_SUPPRIMER_ENTRE_CROCHET_COMMANDE', 'ouinon', '1', 'Supprimer les éléments entre crochet à l\'ajout d\'une commande ', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CATEGORIES_FOURNISSEUR_A_NE_PAS_AFFICHER', 'chaine', '1', 'liste de catégories fournisseurs à afficher (dans l\'affichage des tournées)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CATEGORIES_CONTACT_A_NE_PAS_AFFICHER', 'chaine', '1', 'liste de catégories contact à afficher (dans l\'affichage des tournées)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_AFFICHER_INFO_FACTURES', 'ouinon', '0', 'Afficher des infos sur l\'état des factures', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG', 'ouinon', '1', 'Autoriser l\'édition des tags tiers/contact dans les pages tournées', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CHARGER_PAGE_VIDE', 'ouinon', '0', 'Charger une page vide puis la remplir petit à petit (page tournée unique)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_1ER_CHARGEMENT_SANS_TAG', 'ouinon', '0', '1er chargement sans tags clients/contacts (page tournée unique)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_CATEGORIES_A_SUPPRIMER_BORDEREAU', 'chaine', '', 'Catégories (ou groupe de catégories) à masquer sur les bordereau de tournée de livraison', 0);
		$r++;

		$this->const[$r]=array('TOURNEESDELIVRAISON_TAG_CLIENT_GESTION_BL', 'chaine', '', 'Catégorie (ou groupe de catégorie) de client pour gestion Bl (pas de BL, BL simple, BL double)', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_TAG_CLIENT_FACTURE_MAIL', 'chaine', '', 'Catégorie (ou groupe de catégorie) de client pour lesquels les factures se font par mail', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_ACTIVER_SIGNATURE_ELECTRONIQUE', 'ouinon', '0', 'Activer la signature électronique à la livraison', 0);
		$r++;
		$this->const[$r]=array('TOURNEESDELIVRAISON_TAG_CLIENT_SANS_SIGNATURE_ELECTRONIQUE', 'chaine', '', 'Catégorie (ou groupe de catégorie) de client pour lesquels la signature DOIT se faire par papier (pas de signature électronique)', 0);
		$r++;

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (! isset($conf->tourneesdelivraison) || ! isset($conf->tourneesdelivraison->enabled))
		{
			$conf->tourneesdelivraison=new stdClass();
			$conf->tourneesdelivraison->enabled=0;
		}


		// Array to add new pages in new tabs
        $this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@tourneesdelivraison:$user->rights->tourneesdelivraison->read:/tourneesdelivraison/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@tourneesdelivraison:$user->rights->othermodule->read:/tourneesdelivraison/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
        //
        // Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view


        // Dictionaries
		$this->dictionaries=array();
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@tourneesdelivraison',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->tourneesdelivraison->enabled,$conf->tourneesdelivraison->enabled,$conf->tourneesdelivraison->enabled)												// Condition to show each dictionary
        );
        */


        // Boxes/Widgets
		// Add here list of php file(s) stored in tourneesdelivraison/core/boxes that contains class to show a widget.
        $this->boxes = array(
        	//0=>array('file'=>'tourneesdelivraisonwidget1.php@tourneesdelivraison','note'=>'Widget provided by TourneesDeLivraison','enabledbydefaulton'=>'Home'),
        	//1=>array('file'=>'tourneesdelivraisonwidget2.php@tourneesdelivraison','note'=>'Widget provided by TourneesDeLivraison'),
        	//2=>array('file'=>'tourneesdelivraisonwidget3.php@tourneesdelivraison','note'=>'Widget provided by TourneesDeLivraison')
        );


		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//0=>array('label'=>'MyJob label', 'jobtype'=>'method', 'class'=>'/tourneesdelivraison/class/tourneedelivraison.class.php', 'objectname'=>'TourneeDeLivraison', 'method'=>'doScheduledJob', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->tourneesdelivraison->enabled', 'priority'=>50)
		);
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->tourneesdelivraison->enabled', 'priority'=>50),
		//                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->tourneesdelivraison->enabled', 'priority'=>50)
		// );


		// Permissions
		$this->rights = array();		// Permission array used by this module

		$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Voir les Tournées de livraison';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneedelivraison';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'lire';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Creer/Modifier les Tournées de livraison';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneedelivraison';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'ecrire';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Effacer les Tournées de livraison';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneedelivraison';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'effacer';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = '"Mailer" les tournées de livraison';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneedelivraison';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'mailer';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Lire les tournées unique';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneeunique';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'lire';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Créer/Modifier les tournées uniques';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneeunique';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'ecrire';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Effacer les tournées uniques';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneeunique';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'effacer';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = '"mailer" les tournées uniques';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'tourneeunique';				// In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)
		$this->rights[$r][5] = 'mailer';				    // In php code, permission will be checked by test if ($user->rights->tourneesdelivraison->level1->level2)


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		$this->menu[$r++]=array(
        				'fk_menu'=>'fk_mainmenu=commercial',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
						'type'=>'left',			                // This is a Left menu entry
						'titre'=>'TourneeDeLivraison',
						'mainmenu'=>'commercial',
						'leftmenu'=>'tourneesdelivraison',
						'url'=>'/tourneesdelivraison/tourneedelivraison_list.php',
						'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=>1100+$r,
						'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
						'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
						'target'=>'',
						'user'=>0);

			$this->menu[$r++]=array(
            				'fk_menu'=>'fk_mainmenu=commercial,fk_leftmenu=tourneesdelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
							'type'=>'left',			                // This is a Left menu entry
							'titre'=>'New TourneeDeLivraison',
							'mainmenu'=>'commercial',
							'leftmenu'=>'tourneesdelivraison',
							'url'=>'/tourneesdelivraison/tourneedelivraison_card.php?action=create',
							'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
							'position'=>1100+$r,
							'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
							'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
							'target'=>'',
							'user'=>0);

		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++]=array('fk_menu'=>'',			                // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',			                // This is a Top menu entry
								'titre'=>'TourneesDeLivraison',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'',
								'url'=>'/tourneesdelivraison/tourneesdelivraisonindex.php',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1000+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',	// Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both

		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU TOURNEEDELIVRAISON
		$this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=tourneesdelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List TourneeDeLivraison',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneedelivraison_list',
								'url'=>'/tourneesdelivraison/tourneedelivraison_list.php',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1000+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=tourneesdelivraison,fk_leftmenu=tourneesdelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New TourneeDeLivraison',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneedelivraison_new',
								'url'=>'/tourneesdelivraison/tourneedelivraison_page.php?action=create',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1000+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		*/

		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=tourneesdelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List TourneeDeLivraison',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneedelivraison',
								'url'=>'/tourneesdelivraison/tourneedelivraison_list.php',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=tourneesdelivraison,fk_leftmenu=tourneesdelivraison_tourneedelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New TourneeDeLivraison',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneedelivraison',
								'url'=>'/tourneesdelivraison/tourneedelivraison_card.php?action=create',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=tourneesdelivraison',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List TourneeUnique',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneeunique',
								'url'=>'/tourneesdelivraison/tourneeunique_list.php',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=tourneesdelivraison,fk_leftmenu=tourneesdelivraison_tourneeunique',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New TourneeUnique',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneeunique',
								'url'=>'/tourneesdelivraison/tourneeunique_card.php?action=create',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->tourneesdelivraison->enabled',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=tourneesdelivraison,fk_leftmenu=tourneesdelivraison_tourneeunique',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'Tags Tournee',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneeunique',
								'url'=>'/tourneesdelivraison/categorie.php?leftmenu=cat&type=tourneeunique',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'($conf->tourneesdelivraison->enabled && $conf->categorie->enabled)',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=tourneesdelivraison,fk_leftmenu=tourneesdelivraison_tourneeunique',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'Tags Ligne Tournee',
								'mainmenu'=>'tourneesdelivraison',
								'leftmenu'=>'tourneesdelivraison_tourneeunique',
								'url'=>'/tourneesdelivraison/categorie.php?leftmenu=cat&type=tourneeunique_lines',
								'langs'=>'tourneesdelivraison@tourneesdelivraison',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'($conf->tourneesdelivraison->enabled && $conf->categorie->enabled)',  // Define condition to show or hide menu entry. Use '$conf->tourneesdelivraison->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->tourneesdelivraison->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);

		/* END MODULEBUILDER LEFTMENU TOURNEEDELIVRAISON */


		// Exports
		$r=1;

		/* BEGIN MODULEBUILDER EXPORT TOURNEEDELIVRAISON */
		/*
		$langs->load("tourneesdelivraison@tourneesdelivraison");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='TourneeDeLivraisonLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='tourneedelivraison@tourneesdelivraison';
		$keyforclass = 'TourneeDeLivraison'; $keyforclassfile='/mymobule/class/tourneedelivraison.class.php'; $keyforelement='tourneedelivraison';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='tourneedelivraison'; $keyforaliasextra='extra'; $keyforelement='tourneedelivraison';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'tourneedelivraison as t';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('tourneedelivraison').')';
		$r++; */
		/* END MODULEBUILDER EXPORT TOURNEEDELIVRAISON */
	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories
	 *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function init($options='')
	{
		$result=$this->_load_tables('/tourneesdelivraison/sql/');
		if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')

		// Create extrafields
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		$result1=$extrafields->addExtraField('colisage', "Colisage", 'int', 1,  3, 'product',   0, 0, 'null', '', 1, '', 1, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		$result2=$extrafields->addExtraField('est_cache_bordereau_livraison', "CacheSurBL", 'boolean', 1,  1, 'product',   0, 0, '0', '', 1, '', 1, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		$result2=$extrafields->addExtraField('codecarton', "CodeCarton", 'varchar', 1,  5, 'product',   0, 0, '0', '', 1, '', 1, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		//$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		//$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		//$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');
		//$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'tourneesdelivraison@tourneesdelivraison', '$conf->tourneesdelivraison->enabled');

		$sql = array();

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}
}
