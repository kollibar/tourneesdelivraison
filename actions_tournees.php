<?php


	// Action to add record
	if ($action == 'add' && ! empty($permissiontoadd))
	{
		foreach ($object->fields as $key => $val)
		{
			if (in_array($key, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat', 'fk_user_modif', 'import_key'))) continue;	// Ignore special fields

			// Set value to insert
			if (in_array($object->fields[$key]['type'], array('text', 'html'))) {
				$value = GETPOST($key,'none');
			} elseif ($object->fields[$key]['type']=='date') {
				$value = dol_mktime(12, 0, 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='datetime') {
				$value = dol_mktime(GETPOST($key.'hour'), GETPOST($key.'min'), 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='price') {
				$value = price2num(GETPOST($key));
			} else {
				$value = GETPOST($key,'alpha');
			}
			if (preg_match('/^integer:/i', $object->fields[$key]['type']) && $value == '-1') $value='';		// This is an implicit foreign key field
			if (! empty($object->fields[$key]['foreignkey']) && $value == '-1') $value='';					// This is an explicit foreign key field

			$object->$key=$value;
			if ($val['notnull'] > 0 && $object->$key == '' && is_null($val['default']))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv($val['label'])), null, 'errors');
			}
		}

		if (! $error)
		{
			$result=$object->create($user);
			if ($result > 0)
			{
				// Creation OK
				$action='view';

				// Categories association
				$custcats = GETPOST('custcats', 'array');
				$result = $object->setCategories($custcats);
				if ($result < 0)
				{
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
			else
			{
				// Creation KO
				if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
				else  setEventMessages($object->error, null, 'errors');
				$action='create';
			}
		}
		else
		{
			$action='create';
		}
	}

	// Action to update record	// A FAIRE / MODIFIER
	else if ($action == 'update' && ! empty($permissiontoadd))
	{
		foreach ($object->fields as $key => $val) {
			if (! GETPOSTISSET($key)) continue;		// The field was not submited to be edited
			if (in_array($key, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat', 'fk_user_modif', 'import_key'))) continue;	// Ignore special fields

			// Set value to update
			if (in_array($object->fields[$key]['type'], array('text', 'html'))) {
				$value = GETPOST($key,'none');
			} elseif ($object->fields[$key]['type']=='date') {
				$value = dol_mktime(12, 0, 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='datetime') {
				$value = dol_mktime(GETPOST($key.'hour'), GETPOST($key.'min'), 0, GETPOST($key.'month'), GETPOST($key.'day'), GETPOST($key.'year'));
			} elseif ($object->fields[$key]['type']=='price') {
				$value = price2num(GETPOST($key));
			} else {
				$value = GETPOST($key,'alpha');
			}
			if (preg_match('/^integer:/i', $object->fields[$key]['type']) && $value == '-1') $value='';		// This is an implicit foreign key field
			if (! empty($object->fields[$key]['foreignkey']) && $value == '-1') $value='';					// This is an explicit foreign key field

			$object->$key=$value;
			if ($val['notnull'] > 0 && $object->$key == '' && is_null($val['default']))
			{
				$error++;
				setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv($val['label'])), null, 'errors');
			}
		}

		if (! $error)
		{
      if(!empty($object->fk_tourneedelivraison)){
        $tourneedelivraison =  new TourneeDeLivraison($object->db);
        $tourneedelivraison->fetch($object->fk_tourneedelivraison);

        $key='date_tournee';
        $object->ref=$tourneedelivraison->ref.GETPOST($key.'year').(GETPOST($key.'month')<10?'0':'').GETPOST($key.'month').(GETPOST($key.'day')<10?'0':'').GETPOST($key.'day');
      }

			$result=$object->update($user);
			if ($result > 0)
			{
				$action='view';

        if( !empty($object->fk_tourneedelivraison)){
          $tourneedelivraison->date_prochaine=$object->date_prochaine;
          $tourneedelivraison->update($user);
        }

				// Prevent categorie's emptying if a user hasn't rights $user->rights->categorie->lire (in such a case, post of 'custcats' is not defined)
				if (! $error && !empty($user->rights->categorie->lire))
				{
					// Categories association
					$categories = GETPOST( 'custcats', 'array' );
					$result = $object->setCategories($categories, $object->element);
					if ($result < 0)
					{
						$error++;
						setEventMessages($object->error, $object->errors, 'errors');
					}
				}
			}
			else
			{
				// Creation KO
				setEventMessages($object->error, $object->errors, 'errors');
				$action='edit';
			}
		}
		else
		{
			$action='edit';
		}
	}

	// Action to update one extrafield	// A FAIRE / MODIFIER
	else if ($action == "update_extras" && ! empty($permissiontoadd))
	{
		$object->fetch(GETPOST('id','int'));

		$attributekey = GETPOST('attribute','alpha');
		$attributekeylong = 'options_'.$attributekey;
		$object->array_options['options_'.$attributekey] = GETPOST($attributekeylong,' alpha');

		$result = $object->insertExtraFields(empty($triggermodname)?'':$triggermodname, $user);
		if ($result > 0)
		{
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			$action = 'view';
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'edit_extras';
		}
	}

	// Action to delete	// A FAIRE / MODIFIER
	else if ($action == 'confirm_delete' && ! empty($permissiontodelete))
	{
	    if (! ($object->id > 0))
	    {
		dol_print_error('', 'Error, object must be fetched before being deleted');
		exit;
	    }

		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".$backurlforlist);
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
	}

	// Action clone object	// A FAIRE / MODIFIER
	else if ($action == 'confirm_clone' && $confirm == 'yes' && ! empty($permissiontoadd))
	{
		if (1==0 && ! GETPOST('clone_content') && ! GETPOST('clone_receivers'))
		{
			setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
		}
		else
		{
			if ($object->id > 0)
			{
				// Because createFromClone modifies the object, we must clone it so that we can restore it later if error
				$orig = clone $object;

				$result=$object->createFromClone($user, $object->id);
				if ($result > 0)
				{
					$newid = 0;
					if (is_object($result)) $newid = $result->id;
					else $newid = $result;
					header("Location: ".$_SERVER['PHP_SELF'].'?id='.$newid."&action=edit");	// Open record of new object
					exit;
				}
				else
				{
					setEventMessages($object->error, $object->errors, 'errors');
					$object = $orig;
					$action='';
				}
			}
		}
	}

	// action validate / unvalidate / cancel / close
	else if( ($action=='confirm_validate' || $action=='confirm_unvalidate' || $action == 'confirm_cancel' || $action == 'confirm_close' || $action == 'confirm_reopen' ) && $confirm == 'yes' && !empty($permissiontoadd)){
		if( $object->id > 0) {
			if( $action == 'confirm_validate' && $object->statut == TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_VALIDATED;
			elseif( $action == 'confirm_unvalidate' && $object->statut == TourneeGeneric::STATUS_VALIDATED) $object->statut = TourneeGeneric::STATUS_DRAFT;
			elseif( $action == 'confirm_close' && $object->statut != TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_CLOSED;
			elseif( $action == 'confirm_reopen' && $object->statut != TourneeGeneric::STATUS_DRAFT) $object->statut = TourneeGeneric::STATUS_VALIDATED;
			elseif( $action == 'confirm_cancel' ) $object->statut = TourneeGeneric::STATUS_CANCELED;
			else {
				setEventMessages($object->error, $object->errors, 'errors');
				$action='';
			}

			if( $action != '')
			{
				$result=$object->update($user);

				if ($result > 0) {
					$action='view';
				} else{
					// Creation KO
					setEventMessages($object->error, $object->errors, 'errors');
					$action='view';
				}
			}
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action='';
		}

	}

if( (($action == 'set_ae_datelivraisonidentique'
          || $action == 'set_ae_1ere_future_cmde'
          || $action == 'set_ae_1elt_par_cmde'
          ||$action == 'set_change_date_affectation'
          || $action == 'set_date_tournee'
          || $action == 'set_label'
          || $action=='set_description')
        && $object->statut==TourneeGeneric::STATUS_DRAFT && !empty($permissiontoadd)
        ) || (
          $action == 'set_masque_ligne'
          && $typetournee == 'tourneeunique'
          )
      ){ // modification d'un paramètre

  if( $action == 'set_date_tournee'){
    $key=substr($action,4);
    $value=GETPOST($key.'year','int').'-'.GETPOST($key.'month','int').'-'.GETPOST($key.'day','int');
  } else {
    $key=substr($action,4);
    if( GETPOSTISSET($key)){
      $value=GETPOST($key,'aZ09');
    } else {
      $value=GETPOST('label', 'int');
    }
  }

  $object->{$key}=$value;

  $result = $object->update($user);

  if ($result > 0)
  {
    //setEventMessages($langs->trans($key.'Modifiee'), null, 'mesgs');
    $action = 'view';
  }
  else
  {
    setEventMessages($object->error, $object->errors, 'errors');
    $action = 'view';
  }

}

else if( $typetournee == 'tourneeunique' && $confirm == 'yes' &&  $action == 'confirm_genererdocs' && $permissioncreate){

  // Reload to get all modified line records and be ready for hooks
   $ret = $object->fetch($id);
   $ret = $object->fetch_thirdparty();


   $outputlangs = $langs;
   $newlang='';

   if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang=GETPOST('lang_id','aZ09');
   if ($conf->global->MAIN_MULTILANGS && empty($newlang) && isset($object->thirdparty->default_lang)) $newlang=$object->thirdparty->default_lang;  // for proposal, order, invoice, ...
   if ($conf->global->MAIN_MULTILANGS && empty($newlang) && isset($object->default_lang)) $newlang=$object->default_lang;                  // for thirdparty
   if (! empty($newlang))
   {
       $outputlangs = new Translate("",$conf);
       $outputlangs->setDefaultLang($newlang);
   }

   // To be sure vars is defined
   if (empty($hidedetails)) $hidedetails=0;
   if (empty($hidedesc)) $hidedesc=0;
   if (empty($hideref)) $hideref=0;
   if (empty($moreparams)) $moreparams=null;

      $result = $object->generateAllDocuments($modellist, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);

   if ($result <= 0){
     setEventMessages($object->error, $object->errors, 'errors');
     $action='';
   } else {
    if (empty($donotredirect))	// This is set when include is done by bulk action "Bill Orders"
    {
      setEventMessages($langs->trans("FileGenerated"), null);

      $urltoredirect = $_SERVER['REQUEST_URI'];
      $urltoredirect = preg_replace('/#builddoc$/', '', $urltoredirect);
      $urltoredirect = preg_replace('/action=confirm_genererdocs&?/', '', $urltoredirect);	// To avoid infinite loop

      header('Location: '.$urltoredirect.'#builddoc');
      exit;
    }
  }
}

else if($typetournee == 'tourneeunique' && $action=='supprimerTags' && $permissiontonote){
  //Catégories
  if (!empty($user->rights->categorie->lire))
  {
    // Categories association
    $categories = GETPOST( 'cats_suppr', 'array' );

    foreach ($categories as $c) {
      $cat=new Categorie($db);
      $cat->fetch($c);
      $f=$cat->get_filles();
      foreach ($f as $fc) {
        if( ! in_array($fc->id, $categories)) $categories[]=$fc->id;
      }
    }

    $object->supprimerCategoriesLines($categories);

    // A FAIRE
    $action='view';

  }
}

else if( $action=='createTourneeUnique' && $typetournee == 'tourneedelivraison' && $user->rights->tourneesdelivraison->{$typetournee}->lire && $user->rights->tourneesdelivraison->tourneeunique->ecrire){

  if (! ($object->id > 0)) {
    dol_print_error('', 'Error, object must be fetched before being create');
    exit;
  }
  $result=$object->createTourneeUnique($user);
  if ($result > 0)
  {
    // create tournee  unique OK
    setEventMessages("CreateTourneeUniqueFromTourneeDeLivraison", null, 'mesgs');
    header("Location: ".str_replace($typetournee,'tourneeunique',$_SERVER["PHP_SELF"]). '?action=edit&id=' . $result);
    exit;
  } else {
    if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
    else setEventMessages($object->error, null, 'errors');
  }
}

// Affectation Automatique
if( $typetournee=='tourneeunique' && $action == 'confirm_affectationauto' && $confirm == 'yes' && $object->statut == TourneeGeneric::STATUS_VALIDATED && $user->rights->tourneesdelivraison->tourneeunique->ecrire){
  if (! ($object->id > 0)) {
    dol_print_error('', 'Error, object must be fetched before');
    exit;
  }

  // paramètre généraux
  // 1 => actif   0 => inactif

  // sur l'objet
  // 0=> defaut 1=> inactif 2=> actif

  // en sortie
  // 1 => actif   0 => inactif

  if( empty($object->ae_1elt_par_cmde)||$object->ae_1elt_par_cmde == 0){
    $ae_1elt_par_cmde=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_SI_1ELT_PAR_CMDE==1?1:0);
  } else $ae_1elt_par_cmde=$object->ae_1elt_par_cmde-1;

  if( empty($object->ae_1ere_future_cmde)||$object->ae_1ere_future_cmde == 0){
    $ae_1ere_future_cmde=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_1ERE_FUTURE_CMDE==1?1:0);
  } else $ae_1ere_future_cmde=$object->ae_1ere_future_cmde-1;

  if( empty($object->ae_datelivraisonidentique)||$object->ae_datelivraisonidentique == 0){
    $ae_datelivraisonidentique=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_AFFECTAUTO_DATELIVRAISONOK==1?1:0);
  } else $ae_datelivraisonidentique=$object->ae_datelivraisonidentique-1;

  // paramètre généraux
  //  0 => inactif  1 => manuel  2 => manuel et auto
  // sur l'objet
  // 0=> defaut 1=> inactif 2=> manuel 3=> manuel et auto
  // en sortie
  //  0 => inactif  1 => manuel  2 => manuel et auto

  if( empty($object->change_date_affectation)||$object->change_date_affectation == 0){
    $change_date_affectation=($conf->global->TOURNEESDELIVRAISON_REGLES_AFFECTAUTO_CHANGEAUTODATE==2?1:0);
  } else $change_date_affectation=($object->change_date_affectation==3?1:0);

  $result=$object->affectationAuto($user,
    $ae_datelivraisonidentique,
    $ae_1ere_future_cmde,
    $ae_1elt_par_cmde,
    $change_date_affectation
  );
  if ($result > 0) {
    // Delete OK
    $action='';
  } else {
    if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
    else setEventMessages($object->error, null, 'errors');
  }
  $action='';
}
