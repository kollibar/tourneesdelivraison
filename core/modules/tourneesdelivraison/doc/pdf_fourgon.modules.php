<?php
/* Copyright (C) 2004-2014	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2008		Raphael Bertrand		<raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2014	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012		Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012		Cédric Salvador		<csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014	Raphaël Doursenaud	<rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015		Marcos García		<marcosgdf@gmail.com>
 * Copyright (C) 2017-2018	Ferran Marcet		<fmarcet@2byte.es>
 * Copyright (C) 2018       Frédéric France     <frederic.france@netlogic.fr>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/facture/doc/pdf_crabe.modules.php
 *	\ingroup    facture
 *	\brief      File of class to generate customers invoices from crabe model
 */

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

dol_include_once('/tourneesdelivraison/core/modules/tourneesdelivraison/modules_tourneesdelivraison.php');
dol_include_once('/tourneesdelivraison/class/tourneeunique.class.php');
dol_include_once('/tourneesdelivraison/class/tourneesdelivraison.class.php');

/**
 *	Class to manage PDF invoice template Crabe
 */
class pdf_fourgon extends ModelePDFTourneesdelivraison
{
     /**
     * @var DoliDb Database handler
     */
    public $db;

	/**
     * @var string model name
     */
    public $name;

	/**
     * @var string model description (short text)
     */
    public $description;

    /**
     * @var int 	Save the name of generated file as the main doc when generating a doc with this template
     */
    public $update_main_doc_field;

	/**
     * @var string document type
     */
    public $type;

	/**
     * @var array() Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 5.4 = array(5, 4)
     */
	public $phpmin = array(5, 4);

	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';

	/**
     * @var int page_largeur
     */
    public $page_largeur;

	/**
     * @var int page_hauteur
     */
    public $page_hauteur;

	/**
     * @var array format
     */
    public $format;

	/**
     * @var int marge_gauche
     */
	public $marge_gauche;

	/**
     * @var int marge_droite
     */
	public $marge_droite;

	/**
     * @var int marge_haute
     */
	public $marge_haute;

	/**
     * @var int marge_basse
     */
	public $marge_basse;

	/**
	 * Issuer
	 * @var Company object that emits
	 */
	public $emetteur;

	/**
	 * @var bool Situation invoice type
	 */
	public $situationinvoice;

	/**
	 * @var float X position for the situation progress column
	 */
	public $posxprogress;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Translations
		$langs->loadLangs(array("main", "tourneesdelivraison@tourneesdelivraison","products"));

		$this->db = $db;
		$this->name = "fourgon";
		$this->description = $langs->trans('PDFFourgonDescription');
		$this->update_main_doc_field = 1;		// Save the name of generated file as the main doc when generating a doc with this template

		// Dimensiont page
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_escompte = 0;                // Affiche si il y a eu escompte
		$this->option_credit_note = 0;             // Support credit notes
		$this->option_freetext = 0;				   // Support add of a personalised text
		$this->option_draft_watermark = 1;		   // Support add of a watermark on drafts

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined

		// Define position of columns
    $largeur=$this->page_largeur - $this->marge_gauche - $this->marge_droite;

    if( !empty($conf->global->TOURNEESDELIVRAISON_POIDS_BL)){
      $this->posxdest=$this->marge_gauche+1;
      $this->largdest=intval($largeur*4/18);
      $this->posxnote=$this->posxdest+$this->largdest;
      $this->largnote=intval($largeur*4/18);
      $this->posxprod=$this->posxnote+$this->largnote;
      $this->largprod=intval($largeur*3/18);
      $this->posxpoids=$this->posxprod+$this->largprod;
      $this->largpoids=intval($largeur*2/18);
      $this->posxretour=$this->posxpoids+$this->largpoids;
      $this->largretour=intval($largeur*3/18);
      $this->posxsign=$this->posxretour+$this->largretour;
      $this->largsign=intval($largeur*2/18);
    } else {
      $this->posxdest=$this->marge_gauche+1;
      $this->largdest=intval($largeur*4/16);
      $this->posxnote=$this->posxdest+$this->largdest;
      $this->largnote=intval($largeur*4/16);
      $this->posxprod=$this->posxnote+$this->largnote;
      $this->largprod=intval($largeur*3/16);
      $this->posxretour=$this->posxprod+$this->largprod;
      $this->largretour=intval($largeur*3/16);
      $this->posxsign=$this->posxretour+$this->largretour;
      $this->largsign=intval($largeur*2/16);
    }

    $this->nb_colis=0;
    $this->nonplein=0;
    $this->inconnu=0;
    $this->poidsTotal=0;
    $this->volumeTotal=0;

	}


  /**
	 *	Function to build pdf onto disk
	 *
	 *	@param		Object		$object			Object expedition to generate (or id if old method)
	 *	@param		Translate	$outputlangs		Lang output object
     *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int			$hidedetails		Do not show line details
     *  @param		int			$hidedesc			Do not show desc
     *  @param		int			$hideref			Do not show ref
     *  @return     int         	    			1=OK, 0=KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$conf,$langs,$hookmanager;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->loadLangs(array("main", "tourneesdelivraison@tourneesdelivraison", "products", "dict", "companies"));

		$nblignes = count($object->lines);

    $this->posxpicture=$this->posxweightvol;

		if ($conf->tourneesdelivraison->dir_output)
		{
			// Definition de $dir et $file
			if ($object->specimen)
			{
				$dir = $conf->tourneesdelivraison->dir_output."/sending";
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$expref = dol_sanitizeFileName($object->ref);
				$dir = $conf->tourneesdelivraison->dir_output . "/" . $object->element ."/" . $expref;
				$file = $dir . "/" . $expref . ".BTL.pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

				// Set nblignes with the new facture lines content after hook
				$nblignes = count($object->lines);

				$pdf=pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs);
				$heightforinfotot = 8;	// Height reserved to output the info and total part
        $heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
        $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
        $pdf->SetAutoPageBreak(1,0);

        if (class_exists('TCPDF'))
        {
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
        }
        $pdf->SetFont(pdf_getPDFFont($outputlangs));
        // Set path to the background PDF File
        if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
        {
            $pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
            $tplidx = $pdf->importPage(1);
        }

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Shipment"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Shipment"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				$tab_top = 42;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:10);
				$tab_height = 220 - $tab_top;
				$tab_height_newpage = 220 - $tab_top_newpage;

				if (! empty($object->note_public) )
				{
					$tab_top = 88 + $height_incoterms;
					$tab_top_alt = $tab_top;

					$pdf->SetFont('','B', $default_font_size - 2);

					$tab_top_alt = $pdf->GetY();
					//$tab_top_alt += 1;

					// Notes
					if (! empty($object->note_public))
					{
						$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
						$pdf->writeHTMLCell(190, 3, $this->posxdesc-1, $tab_top_alt, dol_htmlentitiesbr($object->note_public), 0, 1);
					}

					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;

				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexP['Y'] = $tab_top + 2;
        $nexP['page']=$pdf->getPage();

				// Loop on each lines
				for ($i = 0; $i < $nblignes; $i++) {

          $thirdparty = new Societe($this->db);
          $thirdparty->fetch($object->lines[$i]->fk_soc);
          $thirdparty->fetch_optionals();

/*
          if( !empty($object->lines[$i]->fk_adresselivraison)){
            $contact = new Contact($this->db);
            $contact->fetch($object->lines[$i]->fk_adresselivraison);
            $contact->fetch_optionals();
            $usecontact=1;
          } else {
            $usecontact=0;
            $contact = null;
          }*/

          $objectLine=0;
          $lastContact=-5;

          $boucleAdresseDifferente=1;
          while($boucleAdresseDifferente){  // boucleAdresseDifferente
            $boucleAdresseDifferente=0;

            $expedition=array();
            //for($object->lines[$i]->lines_cmde as $lcmde){
            for(;$objectLine<count($object->lines[$i]->lines_cmde);$objectLine++){
              $lcmde=$object->lines[$i]->lines_cmde[$objectLine];

              if( $lcmde->statut==TourneeUnique_lines_cmde::DATE_OK || $lcmde->statut==TourneeUnique_lines_cmde::DATE_NON_OK){  // si cmde affecté

                $commande=new Commande($this->db);
                $commande->fetch($lcmde->fk_commande);
                $commande->fetch_optionals();

                $listeContact=$commande->liste_contact(-1,'external',1,'SHIPPING');

                if( count($listeContact) != 0 ){
                  $contactId=$listeContact[0];

                  $contact = new Contact($this->db);
                  $contact->fetch($contactId);
                  $contact->fetch_optionals();
                  $usecontact=1;
                } else {
                  $contactId=-1;
                  $usecontact=0;
                }

                if( $contactId != $lastContact && $lastContact >= -1) {
                  break;
                  $boucleAdresseDifferente=1;
                }
                $lastContact=$contactId;

                foreach ($lcmde->lines as $lelt) {
                  if( $lelt->type_element == 'shipping' && ($lelt->statut==TourneeUnique_lines_cmde_elt::DATE_OK || $lelt->statut==TourneeUnique_lines_cmde_elt::DATE_NON_OK)){
                    $exp=new Expedition($this->db);
                    $exp->fetch($lelt->fk_elt);
                    $exp->fetch_optionals();
                    $expedition[]=$exp;
                  }
                }
              }
            }

            $categorie=$object->lines[$i]->getCategories();

            if( count($expedition)==0 && empty($object->lines[$i]->note_public) && count($categorie)==0) continue;  // si pas de livraison, ni tag ni note -> on passe à la suivante



  					$curY = $nexP['Y']+4;
            $pdf->setPage($nexP['page']);

  					$pdf->setTopMargin($tab_top_newpage);
  					$pdf->setPageOrientation('', 1, $heightforfooter+$heightforfreetext+$heightforinfotot);	// The only function to edit the bottom margin of current page to set it.

  					$showpricebeforepagebreak=1;

  					$pdf->startTransaction();

            $boucle=1;
            $curP=$pdf->getPage();

            while($boucle>0){

              $pdf->setPage($nexP['page']);

              $pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
              $pdf->SetTextColor(0,0,0);
              // DESTINATAIRE
              $carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

              $carac_client=pdf_build_address($outputlangs,$this->emetteur,$thirdparty,($usecontact?$contact:''),$usecontact,'target',$object);

              // Show recipient name
              $pdf->SetXY($this->posxdest+2,$curY);
              $pdf->SetFont('','B', $default_font_size);
              $pdf->MultiCell($this->largdest-4, 2, $carac_client_name, 0, 'L');

              $posy = $pdf->getY();

              // Show recipient information
              $pdf->SetFont('','', $default_font_size - 1);
              $pdf->SetXY($this->posxdest+2,$posy);
              $pdf->MultiCell($this->largdest-4, 4, $carac_client, 0, 'L');

              $nexP = $this->_check_pos($pdf, $nexP);

              // NOTE
              $txt_note='';
              if( !empty($object->lines[$i]->note_public)) $txt_note=$object->lines[$i]->note_public;
              if( $txt_note != '' && count($categorie) != 0) $txt .= "\n\n";
              foreach ($categorie as $c) {
                $cat=new Categorie($this->db);
                $cat->fetch($c);
                $txt_note .= $cat->label . "\t";
              }
              $pdf->SetFont('','', $default_font_size - 1);
              $pdf->SetXY($this->posxnote+2,$curY);
              $pdf->MultiCell($this->largnote-4, 4, $outputlangs->convToOutputCharset($txt_note), 0, 'L');

              $nexP = $this->_check_pos($pdf, $nexP);

              // PRODUITS
              $totalWeight=0;
              $totalVolume=0;
              foreach ($expedition as $exp) {
                if( !empty($conf->global->TOURNEESDELIVRAISON_POIDS_BL)){
                  $tmparray=$exp->getTotalWeightVolume();
              		$totalWeight+=$tmparray['weight'];
              		$totalVolume+=$tmparray['volume'];


                  // Set trueVolume and volume_units not currently stored into database
                  if ($exp->trueWidth && $exp->trueHeight && $exp->trueDepth)
                  {
                      $exp->trueVolume=price(($exp->trueWidth * $exp->trueHeight * $exp->trueDepth), 0, $outputlangs, 0, 0);
                      $exp->volume_units=$exp->size_units * 3;
                  }

                  if ($totalWeight!='') $totalWeighttoshow=showDimensionInBestUnit($totalWeight, 0, "weight", $outputlangs);
                  if ($totalVolume!='') $totalVolumetoshow=showDimensionInBestUnit($totalVolume, 0, "volume", $outputlangs);
                  if ($exp->trueWeight) $totalWeighttoshow=showDimensionInBestUnit($exp->trueWeight, $exp->weight_units, "weight", $outputlangs);
                  if ($exp->trueVolume) $totalVolumetoshow=showDimensionInBestUnit($exp->trueVolume, $exp->volume_units, "volume", $outputlangs);

                  $curY2=$curY;

                  // Total Weight
                  if ($totalWeighttoshow)
                  {
                      $pdf->SetFont('','B', $default_font_size - 1);
                      $pdf->SetXY($this->posxpoids, $curY2);
                      $pdf->MultiCell($this->largpoids, $tab2_hl, $totalWeighttoshow, 0, 'C');

                      $curY2=$pdf->getY();
                      $index++;
                  }
                  if ($totalVolumetoshow)
                  {
                      $pdf->SetFont('','B', $default_font_size - 1);
                      $pdf->SetXY($this->posxpoids, $curY2);
                      $pdf->MultiCell($this->largpoids, $tab2_hl, $totalVolumetoshow, 0, 'C');

                      $index++;
                  }
                  if (! $totalWeighttoshow && ! $totalVolumetoshow) $index++;
                }

                $curY2=$pdf->getY();



                $pdf->SetFont('','B', $default_font_size - 1);
                $pdf->SetXY($this->posxprod+2,$curY);
                $pdf->MultiCell($this->largprod-4, 4, $outputlangs->convToOutputCharset($exp->ref), 0, 'C');

                $curY=$pdf->getY();
                $txt_prod='';

                foreach ($exp->lines as $lexp) {
                  $product = new Product($this->db);
                  $product->fetch($lexp->fk_product);
                  $product->fetch_optionals();
                  if( !empty($product->array_options['options_est_cache_bordereau_livraison'])) continue;

                  if( $txt_prod != '') $txt_prod.="\n";
                  if( ! empty($product->array_options['options_colisage'])){
                    $txt_prod .=
                      ($lexp->qty_shipped/$product->array_options['options_colisage']>=1?intval($lexp->qty_shipped/$product->array_options['options_colisage']).'x'.$product->array_options['options_colisage']:'') .
                      (($lexp->qty_shipped % $product->array_options['options_colisage']!= 0 && $lexp->qty_shipped/$product->array_options['options_colisage']>=1)?'+':'') .
                      ($lexp->qty_shipped % $product->array_options['options_colisage']!=0?$lexp->qty_shipped % $product->array_options['options_colisage']:'');
                  } else {
                    $txt_prod .= $lexp->qty_shipped;
                  }
                  /*if( ! empty($product->array_options['options_codecarton'])){
                    $txt_prod .= ' '.$product->array_options['options_codecarton'];
                  } else {
                    $txt_prod .= ' '.$product->ref;
                  }*/
                  $txt_prod .= ' '.$product->ref;
                }

                $pdf->SetFont('','', $default_font_size - 1);
                $pdf->SetXY($this->posxprod+2,$curY);
                $pdf->MultiCell($this->largprod-4, 4, $outputlangs->convToOutputCharset($txt_prod), 0, 'C');

                $curY=max($pdf->getY(),$curY2);
              }


              $nexP = $this->_check_pos($pdf, $nexP);


              if ($i != ($nblignes-1)){
                $pdf->line($this->marge_gauche, $nexP['Y']+2, $this->page_largeur-$this->marge_droite, $nexP['Y']+2);
              }

              if( $boucle == 1 ){ // 1er passage
                if( $nexP['page'] > $curP || $nexP['Y'] > $this->page_hauteur - $heightforfooter - 4 - $heightforinfotot - $heightforinfotot){ // si saut de page on refait un passage
                  $pdf->rollbackTransaction(true);
                  $boucle=2;
                  if( $pdf->getPage()==1 ){
                    $this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
                  } else {
                    $this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1);
                  }
                  $this->_pagefoot($pdf,$object,$outputlangs,1);
                  $pdf->AddPage();
                  $pagenb++;
                  $pdf->setPage($pagenb);
                  $pdf->setPageOrientation('', 1, 0);
                  if (! empty($tplidx)) $pdf->useTemplate($tplidx);
                  $this->_pagehead($pdf, $object, 1, $outputlangs);
                  $nexP['Y']=$tab_top+4;
                  $curY=$nexP['Y'];
                  $nexP['page']=$pdf->getPage();
                  continue;
                }
              }
              $boucle=0;
              $pdf->commitTransaction();
            }
          } // boucleAdresseDifferente
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter,
          0, $outputlangs, 0, 0);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, 0, $bottomlasttab, $outputlangs);

				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;	// No error
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorConstantNotDefined","EXP_OUTPUTDIR");
			return 0;
		}
	}

  function _check_pos(&$pdf, $nexP){
    $pageposafter=$pdf->getPage();

    if( $pageposafter >= $nexP['page']){
      $nexP['page']=$pageposafter;
      $posyafter=$pdf->GetY();
      if( $posyafter > $nexP['Y'] ) $nexP['Y']=$posyafter;
    }
    return $nexP;
  }

	/**
	 *	Show total to pay
	 *
	 *	@param	PDF			$pdf           Object PDF
	 *	@param  Facture		$object         Object invoice
	 *	@param  int			$deja_regle     Montant deja regle
	 *	@param	int			$posy			Position depart
	 *	@param	Translate	$outputlangs	Objet langs
	 *	@return int							Position pour suite
	 */
	function _tableau_tot(&$pdf, $object, $deja_regle, $posy, $outputlangs)
	{
		global $conf,$mysoc;

    $sign=1;

    $default_font_size = pdf_getPDFFontSize($outputlangs);

		$tab2_top = $posy;
		$tab2_hl = 4;
		$pdf->SetFont('','B', $default_font_size - 1);

		// Tableau total

		$useborder=0;
		$index = 0;

		$totalWeighttoshow='';
		$totalVolumetoshow='';

		// Load dim data
		$tmparray=$object->getTotalWeightVolume();
		$totalWeight=$tmparray['weight'];
		$totalVolume=$tmparray['volume'];
		$totalOrdered=$tmparray['ordered'];
		$totalToShip=$tmparray['toship'];


		$totalColis=strval($this->nb_colis);
		if( $this->inconnu ) $totalColis.=" + ?";
		if( $this->nonplein ) $totalColis.="*: attention certain colis non pleins";


		// Set trueVolume and volume_units not currently stored into database
		if ($object->trueWidth && $object->trueHeight && $object->trueDepth)
		{
		    $object->trueVolume=price(($object->trueWidth * $object->trueHeight * $object->trueDepth), 0, $outputlangs, 0, 0);
		    $object->volume_units=$object->size_units * 3;
		}

		if ($totalWeight!='') $totalWeighttoshow=showDimensionInBestUnit($totalWeight, 0, "weight", $outputlangs);
		if ($totalVolume!='') $totalVolumetoshow=showDimensionInBestUnit($totalVolume, 0, "volume", $outputlangs);
		if ($object->trueWeight) $totalWeighttoshow=showDimensionInBestUnit($object->trueWeight, $object->weight_units, "weight", $outputlangs);
		if ($object->trueVolume) $totalVolumetoshow=showDimensionInBestUnit($object->trueVolume, $object->volume_units, "volume", $outputlangs);

    	$pdf->SetFillColor(255,255,255);
    	$pdf->SetXY($this->posxdest, $tab2_top + $tab2_hl * $index);
    	$pdf->MultiCell($this->largdest, $tab2_hl, $outputlangs->transnoentities("Total"), 0, 'C', 1);


    	$pdf->SetXY($this->posxprod, $tab2_top + $tab2_hl * $index);
    	$pdf->MultiCell($this->largprod, $tab2_hl, $totalColis, 0, 'C', 1);

    if( !empty($conf->global->TOURNEESDELIVRAISON_POIDS_BL)){
  		// Total Weight
  		if ($totalWeighttoshow)
  		{
      		$pdf->SetXY($this->posxpoids, $tab2_top + $tab2_hl * $index);
      		$pdf->MultiCell($this->largpoids, $tab2_hl, $totalWeighttoshow, 0, 'C', 1);

      		$index++;
  		}
  		if ($totalVolumetoshow)
  		{
      		$pdf->SetXY($this->posxpoids, $tab2_top + $tab2_hl * $index);
      		$pdf->MultiCell($this->largpoids, $tab2_hl, $totalVolumetoshow, 0, 'C', 1);

  		    $index++;
  		}
  		if (! $totalWeighttoshow && ! $totalVolumetoshow) $index++;
    }

		$pdf->SetTextColor(0,0,0);

		return ($tab2_top + ($tab2_hl * $index));
	}

	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		Hide top bar of array
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop=0, $hidebottom=0)
	{
		global $conf;

		// Force to disable hidetop and hidebottom
		$hidebottom=0;
		if ($hidetop) $hidetop=-1;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Amount in (at tab_top - 1)
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','',$default_font_size - 2);

		// Output Rect
		$this->printRect($pdf,$this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, $hidetop, $hidebottom);	// Rect prend une longueur en 3eme param et 4eme param

		$pdf->SetDrawColor(128,128,128);
		$pdf->SetFont('','', $default_font_size - 1);

		if (empty($hidetop))
		{
			$pdf->line($this->marge_gauche, $tab_top+5, $this->page_largeur-$this->marge_droite, $tab_top+5);

			$pdf->SetXY($this->posxdest-1, $tab_top+1);
			$pdf->MultiCell($this->largdest, 2, $outputlangs->transnoentities("Destinataire"), '', 'C');
		}

		$pdf->line($this->posxnote-1, $tab_top, $this->posxnote-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxnote-1, $tab_top+1);
			$pdf->MultiCell($this->largnote, 2, $outputlangs->transnoentities("Note"),'','C');
		}

    $pdf->line($this->posxprod-1, $tab_top, $this->posxprod-1, $tab_top + $tab_height);
    if (empty($hidetop))
    {
      $pdf->SetXY($this->posxprod, $tab_top+1);
      $pdf->MultiCell($this->largprod, 2, $outputlangs->transnoentities("Products"),'','C');
    }

    if( !empty($conf->global->TOURNEESDELIVRAISON_POIDS_BL)){
      $pdf->line($this->posxpoids-1, $tab_top, $this->posxpoids-1, $tab_top + $tab_height);
  		if (empty($hidetop))
  		{
  			$pdf->SetXY($this->posxpoids, $tab_top+1);
  			$pdf->MultiCell($this->largpoids, 2, $outputlangs->transnoentities("Poids"),'','C');
  		}
    }

    $pdf->line($this->posxretour-1, $tab_top, $this->posxretour-1, $tab_top + $tab_height);
    if (empty($hidetop))
    {
      $pdf->SetXY($this->posxretour, $tab_top+1);
      $pdf->MultiCell($this->largretour, 2, $outputlangs->transnoentities("Retour"),'','C');
    }

    $pdf->line($this->posxsign-1, $tab_top, $this->posxsign-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxsign, $tab_top+1);
			$pdf->MultiCell($this->largsign, 2, $outputlangs->transnoentities("Signature"),'','C');
		}

	}


	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf,$langs,$mysoc;

		$langs->load("orders");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		// Show Draft Watermark
		if($object->statut==0 && (! empty($conf->global->TOURNEESDELIVRAISON_DRAFT_WATERMARK)) )
		{
            		pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->TOURNEESDELIVRAISON_DRAFT_WATERMARK);
		}

		//Prepare la suite
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$w = 110;

		$posy=$this->marge_haute;
		$posx=$this->page_largeur-$this->marge_droite-$w;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
			    $height=pdf_getHeightForLogo($logo);
			    $tmp=dol_getImageSize($logo, $url);
			    $widthLogo=$tmp['width']*$height/$tmp['height'];
			    $pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				$widthLogo=$w;
			}
		}
		else
		{
			$text=$this->emetteur->name;
			$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		// Show barcode
		if (! empty($conf->barcode->enabled))
		{
			$posx=105;
		}
		else
		{
			$posx=$this->marge_gauche+3;
		}



		$posx=$this->page_largeur - $w - $this->marge_droite;
		$posy=$this->marge_haute;

		$pdf->SetFont('','B', $default_font_size + 2);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$title=$outputlangs->transnoentities("BLTournee");
		$pdf->MultiCell($w, 4, $title, '', 'R');

		$pdf->SetFont('','', $default_font_size + 1);

		$posy+=5;

		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell($w, 4, $outputlangs->transnoentities("RefTournee") ." : ".$object->ref, '', 'R');

		// Date planned delivery
		if ($object->element == "tourneeunique" && ! empty($object->date_tournee))
		{
    			$posy+=4;
    			$pdf->SetXY($posx,$posy);
    			$pdf->SetTextColor(0,0,60);
    			$pdf->MultiCell($w, 4, $outputlangs->transnoentities("DateDeliveryPlanned")." : ".dol_print_date($object->date_tournee,"day",false,$outputlangs,true), '', 'R');
		}


		$pdf->SetFont('','', $default_font_size + 3);
		$Yoff=25;

		$pdf->SetTextColor(0,0,0);
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
    $hidefreetext=1;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf,$outputlangs,'TOURNEESDELIVRAISON_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);
	}

}
