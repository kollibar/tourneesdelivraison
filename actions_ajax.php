<?php

if ( $action =='ajax_actualiseFormAddressLivraison'){
  $new_fk_soc=GETPOST('new_fk_soc','int');
  if($typetournee == 'tourneedelivraison'){
    $line=new TourneeDeLivraison_lines($db);
  } else{
    $line=new TourneeUnique_lines($db);
  }
  $line->fetch($line_id);
  $form->select_contacts($new_fk_soc, $line->fk_adresselivraison, 'adresselivraisonid', 1, '', '',0,'', 0,0,array(), false,'','');
}

 ?>
