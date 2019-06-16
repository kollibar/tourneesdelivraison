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
class pdf_palette40 extends ModelePDFTourneesdelivraison
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
		$this->name = "palette40";
		$this->description = $langs->trans('PDFPalette40Description');
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

		$this->option_logo = 0;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_escompte = 0;                // Affiche si il y a eu escompte
		$this->option_credit_note = 0;             // Support credit notes
		$this->option_freetext = 0;				   // Support add of a personalised text
		$this->option_draft_watermark = 0;		   // Support add of a watermark on drafts

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined

		// Define position of columns
    $largeur=$this->page_largeur - $this->marge_gauche - $this->marge_droite;

    $this->nb_colonne=4;
    $this->nb_ligne=10;
    $this->nb_case=$this->nb_colonne*$this->nb_ligne;
    $this->marge_g=0; // marge à gauche de la planche
    $this->marge_d=0; // marge à droite de la planche
    $this->marge_h=0; // marge en haut de la planche
    $this->marge_b=0; // marge en bas de la planche
    $this->marge_v=0; // marge verticale entre les étiquettes
    $this->marge_h=0; // marge horizontale entre les étiquettes

    // marge d'impression
    $this->marge_haute=4;
    $this->marge_basse=4;
    $this->marge_droite=4;
    $this->marge_gauche=4;

    $this->marge_case=2;

    $this->largeur_case=($this->page_largeur-$this->marge_g-$this->marge_d)/$this->nb_colonne;
    $this->hauteur_case=($this->page_hauteur-$this->marge_b-$this->marge_h)/$this->nb_ligne;
    $this->marge_hb=max($this->marge_case, $this->marge_basse-$this->marge_b, $this->marge_haute-$this->marge_h);
    $this->marge_gd=max($this->marge_case, $this->marge_droite-$this->marge_d, $this->marge_gauche-$this->marge_g);

    $this->largeur=$this->largeur_case-2*$this->marge_gd;
    $this->hauteur=$this->hauteur_case-2*$this->marge_hb;

    $this->pos=0;

	}

  function getXYP($pos){
    return array( 'X'=>$this->marge_gd+($pos%$this->nb_colonne)*($this->largeur+2*$this->marge_gd),
                  'Y'=>$this->marge_hb+(floor($pos/$this->nb_colonne)%$this->nb_ligne)*($this->hauteur+2*$this->marge_hb),
                  'page'=>floor($pos/$this->nb_case)+1);
  }

  function addPage(&$pdf){
    $pdf->addPage();
    $pdf->setPageOrientation('', 1, 0);
  }


  function _write_case(&$pdf, $pos, $url, $thirdparty, $num, $tot, $qty, $product, $outputlangs, $default_font_size){
    global $user,$conf,$langs,$hookmanager;

    $XYP=$this->getXYP($pos);
    $curX=$XYP['X'];
    $curY=$XYP['Y'];
    $curP=$XYP['page'];
    //$this->printRect($pdf,$curX, $curY, $this->largeur, $this->hauteur, $hidetop, $hidebottom);	// Rect prend une longueur en 3eme param et 4eme param
    if( $pdf->getPage() < $XYP['page'] ) {
      $this->addPage($pdf);
    }
    $style = array(
      'border' => 0,
      'vpadding' => 1,
      'hpadding' => 0,
      'fgcolor' => array(0,0,0),
      'bgcolor' => false, //array(255,255,255)
      'module_width' => 1, // width of a single module in points
      'module_height' => 1 // height of a single module in points
    );

    if( ! empty($conf->global->TOURNEESDELIVRAISON_URL_ORIGIN) && ! empty ($conf->global->TOURNEESDELIVRAISON_URL_REPLACE)){
      $url=str_replace($conf->global->TOURNEESDELIVRAISON_URL_ORIGIN, $conf->global->TOURNEESDELIVRAISON_URL_REPLACE, $url);
    }

    $pdf->write2DBarcode($url, 'QRCODE,L', $curX, $curY, 18, 18, $style, 'N');

    $carac_client_name = pdfBuildThirdpartyName($thirdparty, $outputlangs);
    $pdf->SetXY($curX+18,$curY);
    $pdf->SetFont('','B', $default_font_size*0.9);

    $pdf->MultiCell($this->largeur-18, 2, pdf_reduceStringTo($pdf,$carac_client_name,round(($this->largeur-23)*1.6,0)), 0, 'L');

    $pdf->SetXY($curX+18,$curY+11);
    $pdf->SetFont('','B', $default_font_size*2);
    $pdf->MultiCell($this->largeur-18, 3, $num, 0, 'L');


    $X=20+$pdf->GetStringWidth($num);

    $pdf->setXY($curX+1+$X,$curY+14.5);
    $pdf->SetFont('','', $default_font_size*0.9);
    $pdf->MultiCell($this->largeur-$X-1, 3, '/'.$tot, 0, 'L');

    $pdf->SetXY($curX+18,$curY+8);
    $pdf->SetFont('','', $default_font_size*0.9);
    //$pdf->MultiCell($this->largeur-22, 3, $qty.'x '.(!empty($product->array_options['options_codecarton'])?$product->array_options['options_codecarton']:$product->ref), 0, 'L');
    $pdf->Cell($this->largeur-20, 1, $qty.'x '.$product->ref, 0, 'L');

  }

  function _add_case(&$pdf, $url, $thirdparty, $num, $tot, $qty, $product, $outputlangs, $default_font_size){
    $this->_write_case($pdf, $this->pos, $url, $thirdparty, $num, $tot, $qty, $product, $outputlangs, $default_font_size);
    $this->pos++;
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
				$file = $dir . "/" . $expref . ".etiquettes.pdf";
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
        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
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


        $url=str_replace("tourneeunique_card.php","livraison_card.php",(!empty($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['SERVER_NAME'].$_SERVER["PHP_SELF"]);

				// Loop on each lines
				for ($i = 0; $i < $nblignes; $i++)
				{

          $thirdparty = new Societe($this->db);
          $thirdparty->fetch($object->lines[$i]->fk_soc);
          $thirdparty->fetch_optionals();

          if(empty($object->lines[$i]->etiquettes) || $object->lines[$i]->etiquettes ==0 ) continue;

          if( !empty($object->lines[$i]->fk_adresselivraison)){
            $contact = new Contact($this->db);
            $contact->fetch($object->lines[$i]->fk_adresselivraison);
            $contact->fetch_optionals();
          }

          $expedition=array();
          foreach($object->lines[$i]->lines_cmde as $lcmde){
            if( $lcmde->statut==TourneeUnique_lines_cmde::DATE_OK || $lcmde->statut==TourneeUnique_lines_cmde::DATE_NON_OK){  // si cmde affecté
              foreach ($lcmde->lines as $lelt) {
                if( $lelt->type_element == 'shipping' && ($lelt->statut==TourneeUnique_lines_cmde_elt::DATE_OK || $lelt->statut==TourneeUnique_lines_cmde_elt::DATE_NON_OK)){
                  $exp=new Expedition($this->db);
                  $exp->fetch($lelt->fk_elt);
                  $exp->fetch_optionals();
                  $expedition[$lelt->id]=$exp;
                }
              }
            }
          }

          $nbColis=$object->lines[$i]->getNbColis();
          $num=1;

          foreach ($expedition as $leltid => $exp) {
            $lelt=new TourneeUnique_lines_cmde_elt($this->db);
            $lelt->fetch($leltid);

            foreach ($exp->lines as $lexp) {
              $product = new Product($this->db);
              $product->fetch($lexp->fk_product);
              $product->fetch_optionals();

              if( !empty($product->array_options['options_est_cache_bordereau_livraison'])) continue;

              if( ! empty($product->array_options['options_colisage'])){
                for($j=0;$j<$lexp->qty_shipped;$j+=$product->array_options['options_colisage']){
                  $param = "?h=".$lelt->getHash()."&le=".$leltid."&c=".$num;
                  $this->_add_case($pdf, $url . $param, $thirdparty, $num, $nbColis, min($product->array_options['options_colisage'],$lexp->qty_shipped-$j), $product, $outputlangs, $default_font_size);
                  $num++;
                }
              } else {
                $param = "?h=".$lelt->getHash()."&le=".$leltid."&c=".$num;
                $this->_add_case($pdf, $url . $param, $thirdparty, $num, $nbColis, $lexp->qty_shipped, $product, $outputlangs, $default_font_size);
                $num++;
              }
            }
          }
				}

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



}
