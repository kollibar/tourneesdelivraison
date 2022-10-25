<?php

if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_changedateelt'){
  $elt_type=GETPOST('elt_type', 'aZ09');
  $elt_id=GETPOST('elt_id', 'int');
  $elt_lineid=GETPOST('elt_lineid','int');

  print "elt_type=$elt_type|elt_id=$elt_id|elt_lineid=$elt_lineid|lineid=$lineid|";

  $result = $object->changeDateEltToDateTournee($user, $lineid, $elt_type, $elt_lineid);
  if ($result > 0)
  {
    setEventMessages($langs->trans('DateEltModifiee', $elt_type), null, 'mesgs');
    $action = 'view';
  }
  else
  {
    setEventMessages($object->error, $object->errors, 'errors');
    $action = 'view';
  }
}

else if ($action == 'setnocmde_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
  $line=$object->getLineById($lineid);
  if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
  else {
    if( count($line->lines_cmde == 0)){
      $line->aucune_cmde=1;
      $line->update($user);
      $line->ALAffectationCommande($user);
    } else { // A FAIRE -> générer erreur car déjà une commande sur la ligen on ne peut donc pas mettre Pas de Commande

    }
  }
  $action='view';
}

else if ($action == 'unsetnocmde_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
  $line=$object->getLineById($lineid);
  if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
  else {
    $line->aucune_cmde=0;
    $line->update($user);
  }
  $action='view';
}

else if ($action == 'settag_contact' && !empty($conf->global->TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG)
        && ! empty($user->rights->societe->contact->creer) && !empty($user->rights->categorie->lire)
        && ! GETPOST('cancel','alpha')) {

  $line=$object->getLineById($lineid);
  $linecontact=$line->getContactLineById(GETPOSTINT('contactlineid'));
  if( $linecontact==null) setEventMessages($object->error, $object->errors, 'errors');
  else {
    $c=new Categorie($db);
    $existing = $c->containing($line->fk_soc, 'contact', 'id');
    $existing = array_intersect($existing, $categoriesContactExclure);

  //Catégories
    // Categories association
    $categories = GETPOST( 'cats', 'array' );

    $staticcontact = new Contact($line->db);
    $staticcontact->id = $linecontact->fk_socpeople;

    $categories = array_merge($categories, $existing);

    $result3 = $staticcontact->setCategories($categories,'contact');
    //$result3 = $line->setCategories($categories);

    unset($staticcontact);

    if ($result3 < 0) {
      $error++;
      setEventMessages($object->error, $object->errors, 'errors');
    }

    if ($result3 >= 0){
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
  }
}


  else if ($action == 'settag_tiers' && !empty($conf->global->TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG)
        && ! empty($user->rights->societe->creer) && !empty($user->rights->categorie->lire)
        && ! GETPOST('cancel','alpha')) {

    $line=$object->getLineById($lineid);
    if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
    else {


      $c=new Categorie($db);
    	$existing = $c->containing($line->fk_soc, ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?'customer':'supplier', 'id');
      $existing = array_intersect($existing, ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?$categoriesClientExclure:$categoriesFournisseurExclure);


    //Catégories
      // Categories association
      $categories = GETPOST( 'cats', 'array' );

      $staticsociete = new Societe($line->db);
      $staticsociete->id = $line->fk_soc;

      $categories = array_merge($categories, $existing);


      $result3 = $staticsociete->setCategories($categories, ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?'customer':'supplier');
      //$result3 = $line->setCategories($categories);
      unset($staticsociete);

      if ($result3 < 0) {
        $error++;
        setEventMessages($object->error, $object->errors, 'errors');
      }



    if ($result3 >= 0){
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
  }
}





	else if ($action == 'setnote_elt' && ! empty($permissiontonote) && ! GETPOST('cancel','alpha')) {
		$line=$object->getLineById($lineid);
		if( $line==null) setEventMessages($object->error, $object->errors, 'errors');
		else {

		$result1=$line->update_note(dol_html_entity_decode(GETPOST('note_public_elt', 'none'), ENT_QUOTES),'_public');
		if ($result1 < 0) {
			$error++;
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$result2=$line->update_note(dol_html_entity_decode(GETPOST('note_private_elt', 'none'), ENT_QUOTES),'_private');
		if ($result2 < 0) {
			$error++;
			setEventMessages($object->error, $object->errors, 'errors');
		}

		//Catégories
		if (!empty($user->rights->categorie->lire))
		{
			// Categories association
			$categories = GETPOST( 'cats', 'array' );

			$result3 = $line->setCategories($categories, $object->element);
			//$result3 = $line->setCategories($categories);

			if ($result3 < 0) {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}



		if ($result1 >= 0 || $result2 >= 0 || $result3 >= 0){
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
	}
}

	else if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_changestatutelt'){
		$statut=GETPOST('statut', 'int');
		$elt_type=GETPOST('elt_type', 'aZ09');
		$elt_id=GETPOST('elt_id', 'int');
		$elt_lineid=GETPOST('elt_lineid','int');

		if( empty($object->change_date_affectation)||$object->change_date_affectation == 0){
			$change_date_affectation=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE>0);
		} else $change_date_affectation=($object->change_date_affectation>1);

		$result = $object->changeStatutElt($user, $lineid, $elt_type, $elt_lineid, $statut, $change_date_affectation);
		if ($result > 0)
		{
			setEventMessages($langs->trans('StatutEltModifiee', $elt_type), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'view';
		}
	}
