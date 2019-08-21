<?php


// (éventuelles) Annulation des avertissements éventuels
if( substr($action,0,4)=='ask_' && $conf->global->{'TOURNEESDELIVRAISON_ASK_'.mb_strtoupper(substr($action,4))}){
  $action='confirm_'.substr($action,4);
  $confirm='yes';
}

 ?>
