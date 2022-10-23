<?php


 ?>
 <!-- BEGIN PHP TEMPLATE TOURNEE_AJAX - Script to enable loading of individual line on tourneelines -->


<!--script au chargement -->
 <script>

<?php if( $ajaxActif ){ ?>

 $(document).ready(function(){
   auChargementNouvelleLigne($('body'));

   $(".tournee-row-reload").each(function(){
     id=$(this).attr('id');
     params=$(this).attr('data');

     lineid=id.slice(4);
     url=$(location).attr('href').slice($(location).attr('href').indexOf($(location).attr('pathname')));
     if( url.indexOf('#') != -1 ){
       suffix=url.slice(url.indexOf('#'));
       url=url.slice(0,url.indexOf('#'));
     } else {
       suffix='';
     }
     if( url.indexOf('?') != -1 ){
       url=url+'&';
     } else {
       url=url+'?';
     }
     url = url + 'lineid='+lineid + '&' + params + suffix;
     url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');


     console.log("GET :"+url);
     //$.get(url, ajaxable_callback);
     getAjaxable(url);
   })
 });

 <?php } ?>

 </script>
