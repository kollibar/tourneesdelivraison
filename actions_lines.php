<?php

if( $action == 'addline' && !empty($permissiontoadd) ) {
   $langs->load('errors');
   $error = 0;

   // Extrafields
   $extrafieldsline = new ExtraFields($db);
   $extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
   $array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
   // Unset extrafield
   if (is_array($extralabelsline)) {
     // Get extra fields
     foreach ($extralabelsline as $key => $value) {
       unset($_POST["options_" . $key]);
     }
   }


   $type=-1;
   if( GETPOST('tournee_line_type_tournee') == 'tournee' )
   {
     $tourneeincluseid=GETPOST('tourneeincluseid', 'int');
     if( ! empty($tourneeincluseid) ) $type=TourneeGeneric_lines::TYPE_TOURNEE;
   }

   if( GETPOST('tournee_line_type_thirdparty_client') == 'client' )
   {
     $socid_client=GETPOST('socid_client', 'int');
     if( ! empty($socid_client) )  {
       $type=TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT;
       $socid=$socid_client;
     }
   }
   if( GETPOST('tournee_line_type_thirdparty_fournisseur') == 'fournisseur' )
   {
     $socid_fournisseur=GETPOST('socid_fournisseur', 'int');
     if( ! empty($socid_fournisseur) ) {
       $type=TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR;
       $socid=$socid_fournisseur;
     }
   }

   if($type >= 0){
     $BL=0;
     $BL1=GETPOST('BL1');
     $BL2=GETPOST('BL2');
     if( $BL1=='BL1') $BL++;
     if( $BL2=='BL2') $BL++;


     $facture=GETPOST('facture');
     if( $facture=='facture') $facture=1;
     else $facture=0;

     $etiquettes=GETPOST('etiquettes');
     if( $etiquettes=='etiquettes') $etiquettes=1;
     else $etiquettes=0;

     $infolivraison=GETPOST('infolivraison');
     $tempstheorique=GETPOST('tempstheorique');

     $fk_adresselivraison=GETPOST('adresselivraisonid');
     if( empty($fk_adresselivraison)) $fk_adresselivraison=0;

     $force_email_soc = GETPOST('force_email_soc');
     if( empty($force_email_soc)) $force_email_soc = 0;

     $note_public=GETPOST('note_public');
     $note_private=GETPOST('note_private');

     $result = $object->addline($type, $socid, $tourneeincluseid, $BL, $facture, $etiquettes, $tempstheorique, $infolivraison, $fk_adresselivraison, $force_email_soc, $note_public, $note_private);

     if ($result > 0) {
       $ret = $object->fetch($object->id); // Reload to get new records

       //Catégories
       $cats_line = GETPOST('cats_line', 'array');
       $line=$object->getLineById($result);
       $result = $line->setCategories($cats_line);
       if ($result < 0)
       {
         $error++;
         setEventMessages($object->error, $object->errors, 'errors');
       }

       if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
         $object->deleteAllDocuments();
       }

       if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
         // Define output language
         $outputlangs = $langs;
         $newlang = GETPOST('lang_id', 'alpha');
         if (! empty($newlang)) {
           $outputlangs = new Translate("", $conf);
           $outputlangs->setDefaultLang($newlang);
         }

         $object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
       }
     }
     else
     {
       setEventMessages($object->error, $object->errors, 'errors');
     }
   }

   unset($_POST['tournee_line_type_thirdparty_client']);
   unset($_POST['tournee_line_type_thirdparty_fournisseur']);
   unset($_POST['tournee_line_type_tournee']);
   unset($_POST['socid_client']);
   unset($_POST['socid_fournisseur']);
   unset($_POST['tourneeincluseid']);
   unset($_POST['BL1']);
   unset($_POST['BL2']);
   unset($_POST['facture']);
   unset($_POST['etiquettes']);
   unset($_POST['infolivraison']);
   unset($_POST['note_public']);
   unset($_POST['note_private']);
   unset($_POST['tempstheorique']);
   unset($_POST['cats_line']);
 }

else if( $action == 'confirm_deleteline' && $confirm == 'yes' && !empty($permissiontoadd) )
{
  $result = $object->deleteline($user, $lineid);

  if ($result > 0) {
    // Define output language
    $outputlangs = $langs;
    $newlang = '';

    if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09'))
      $newlang = GETPOST('lang_id','aZ09');
    if ($conf->global->MAIN_MULTILANGS && empty($newlang))
      $newlang = $object->thirdparty->default_lang;
    if (! empty($newlang)) {
      $outputlangs = new Translate("", $conf);
      $outputlangs->setDefaultLang($newlang);
    }
    if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
      $object->deleteAllDocuments();
    }
    if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {
      $ret = $object->fetch($object->id); // Reload to get new records
      $object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
    }

    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
    exit;

  } else {
    setEventMessages($object->error, $object->errors, 'errors');
  }
}

else if( ($action == 'updateline'|| substr($action,0,9) == 'editline_') && !empty($permissiontoadd) ) {
  $langs->load('errors');
  $error = 0;

  // Extrafields
  $extrafieldsline = new ExtraFields($db);
  $extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
  $array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
  // Unset extrafield
  if (is_array($extralabelsline)) {
    // Get extra fields
    foreach ($extralabelsline as $key => $value) {
      unset($_POST["options_" . $key]);
    }
  }



  if(! empty($lineid)){

    $line=$object->getLineById($lineid);

    if( $line > 0){ // la ligne à modifier existe bien et appartient bien à cette objet
      $BL=0;
      $BL1=GETPOST('BL1');
      $BL2=GETPOST('BL2');
      if( $BL1=='BL1') $BL++;
      if( $BL2=='BL2') $BL++;


      $facture=GETPOST('facture');
      if( $facture=='facture') $facture=1;
      else $facture=0;

      $etiquettes=GETPOST('etiquettes');
      if( $etiquettes=='etiquettes') $etiquettes=1;
      else $etiquettes=0;

      $infolivraison=GETPOST('infolivraison');
      $tempstheorique=GETPOST('tempstheorique');

      $fk_adresselivraison=GETPOST('adresselivraisonid');
      if( empty($fk_adresselivraison)) $fk_adresselivraison=0;

      $note_public=GETPOST('note_public');
      $note_private=GETPOST('note_private');

      $force_email_soc = GETPOST('force_email_soc');
      if( empty($force_email_soc)) $force_email_soc = 0;

      $result = $object->updateline($lineid, $line->type, $line->fk_soc, $line->fk_tournee_incluse, $BL, $facture, $etiquettes, $tempstheorique, $infolivraison, $fk_adresselivraison, $force_email_soc, $note_public, $note_private, $line->rang);

      if ($result > 0) {
        $ret = $object->fetch($object->id); // Reload to get new records

        // Catégories
        // Prevent categorie's emptying if a user hasn't rights $user->rights->categorie->lire (in such a case, post of 'custcats' is not defined)
        if (!empty($user->rights->categorie->lire))
        {
          $line=$object->getLineById($result);
          // Categories association
          $categories = GETPOST( 'cats_line', 'array' );
          $result = $line->setCategories($categories, $object->element);
          if ($result < 0)
          {
            $error++;
            setEventMessages($object->error, $object->errors, 'errors');
          }
        }
        if( empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTODELETE)){
          $object->deleteAllDocuments();
        }
        if (empty($conf->global->TOURNEESDELIVRAISON_DISABLE_PDF_AUTOUPDATE)) {	// génération de pdf désactivé
          // Define output language
          $outputlangs = $langs;
          $newlang = GETPOST('lang_id', 'alpha');
          if (! empty($newlang)) {
            $outputlangs = new Translate("", $conf);
            $outputlangs->setDefaultLang($newlang);
          }

          $object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref);
        }
      }
      else
      {
        setEventMessages($object->error, $object->errors, 'errors');
      }
    }
    else
    {	// la ligne à modifier n'existe pas ou n'appartient pas à cette objet

    }
  }
  $action='';

  unset($_POST['tournee_line_type_thirdparty']);
  unset($_POST['tournee_line_type_tournee']);
  unset($_POST['socid']);
  unset($_POST['tourneeincluseid']);
  unset($_POST['BL1']);
  unset($_POST['BL2']);
  unset($_POST['facture']);
  unset($_POST['etiquettes']);
  unset($_POST['infolivraison']);
  unset($_POST['note_private']);
  unset($_POST['note_public']);
  unset($_POST['tempstheorique']);
  unset($_POST['cats_line']);
}

 ?>
