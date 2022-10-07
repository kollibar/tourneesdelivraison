<?php



	if ( $action =='addcontact' && !empty($permissiontoadd) ){
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

		$result = $object->addcontactline($lineid, 0, $contactid, $no_email, $sms);

		if ($result > 0) {
			$ret = $object->fetch($object->id); // Reload to get new records
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
		$action='';
	}

	else if ( $action =='confirm_deletecontact' && !empty($permissiontoadd) ){

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

		$result = $object->deletecontactline($user, $contactid);


		if ($result > 0) {
			$ret = $object->fetch($object->id); // Reload to get new records
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
		$action='';
	}
