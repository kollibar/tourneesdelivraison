<?php


 ?>
 <!-- BEGIN PHP TEMPLATE TOURNEE_AJAX - Script to enable loading of individual line on tourneelines -->


<!--script au chargement -->
 <script>

<?php if( $ajaxActif ){ ?>

 $(document).ready(function(){
   auChargementNouvelleLigne($('body'));

   reload();
 });

 <?php } ?>

 </script>
