<?php


 ?>
 <!-- BEGIN PHP TEMPLATE TOURNEE_AJAX - Script to enable loading of individual line on tourneelines -->



<!-- scripts pour l'ajaxisation ! -->
<script>
function mettreAjaxPartout(){
  $(".askActionAjaxable").each(function(){
    if( $(this).closest(".tournee-row").length>0 ) $(this).attr("href", $(this).attr("href") + $(this).closest(".tournee-row").attr("data"));
    $(this).attr('onclick', "askActionAjaxable(this);return false;");
  });
  $(".askAjaxable").each(function(){
    if( $(this).closest(".tournee-row").length>0 ) $(this).attr("href", $(this).attr("href") + $(this).closest(".tournee-row").attr("data"));
    $(this).attr('onclick', "askAjaxable(this);return false;");
  });

  $(".ajaxable").each(function(){
    if( $(this).closest(".tournee-row").length>0){
      //$(this).attr('onclick', 'ajaxable(this);return false;');
      $(this).attr('onclick', 'return false;');
      $(this).click(ajaxable);
    }
  });

  $(".edit_note_elt").submit(function(event){
    id=$(this).closest(".tournee-row").attr('id');
    lineid=id.slice(4);
    valideFormulaire_edit_note_elt(lineid);
    event.preventDefault();
  });


  $('.edit_note_elt').find("input[name='']").click(function(){
    id=$(this).closest(".tournee-row").attr('id');
    $('#'+id).find("input[name='action']").attr('value',''); // met l'action à 0
  });
}

function valideFormulaire_edit_note_elt(lineid){
  if( $('#edit_note_elt-'+lineid).length != 0){
    url=$('#edit_note_elt-'+lineid).attr('action');

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

    url=url+$('#edit_note_elt-'+lineid).serialize()+suffix;

    url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');
    $.get(url,ajaxable_callback);
  }
}

function ajaxable(){
  id=$(this).closest(".tournee-row").attr('id');

  url=$(this).attr('href');
  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');


//  rechercheFormulaireOuvert();
  //alert(url);
  //$(this).closest(".tournee-row").parent().load(url+' #'+id);
  $.get(url, ajaxable_callback);
}

function ajaxable_callback(data, status){
  // recherche de la 1ere balise
  i=-1;
  do{
    i++;
    i=data.indexOf('<',i);
  } while( data[i+1]=='!');
  do{
    i++;
  } while( data[i]==' ' );
  j=data.indexOf(' ',i);

  balise=data.slice(i,j);

  k=data.indexOf("id=\"", i);
  if( k == -1 ) {
    k=data.indexOf("id=\'", i);
    l=data.indexOf("'", k+4);
  } else {
    l=data.indexOf("\"",k+4);
  }
  id=data.slice(k+4,l);

  m=data.indexOf('>',l);
  n=data.lastIndexOf('</'+balise+'>');

  //alert(data);

  //alert('balise: '+balise+' id: '+id+' m: '+m+' n: '+n+' taille: '+data.length+' taille_slice: '+data.slice(m+1,n).length);
  data=data.slice(m+1,n);


  $("#"+id).html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));

  auChargementNouvelleLigne();

}

function askAjaxable(elt){
  url=elt.href;
  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');
  $.get(url, askAjaxable_callback);
}
function askAjaxable_callback(data,status){
  $('#formulaireConfirm').html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));
}



function askActionAjaxable(elt){
  url=elt.href;
  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

  $.get(url, askActionAjaxable_callback);
}
function askActionAjaxable_callback(data,status){
  $('#formulaireConfirm').html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));
}

</script>





<!-- scripts pour l'édition de ligne -->
<script>
function changeClient(){
	if( $("#socid").val()!=-1 && $("#socid").val()!=0){
		setfor3party();
	}
}

function changeTourneeIncluse(){
	if( $("#tourneeincluseid").val()!=-1 && $("#tourneeincluseid").val()!=0) setfortournee();
}

function BL1choix(){
	if( ! jQuery("#BL1").is(':checked') && jQuery("#BL2").is(':checked') ){
		jQuery("#BL1").prop('checked',true).change();
		jQuery("#BL2").prop('checked',false).change();
	}
}

/* Function to set fields from choice */
function setfor3party() {
	console.log("Call set3party. We show most fields");
	/*jQuery("#search_idprod").val('');
	jQuery("#idprod").val('');
	jQuery("#idprodfournprice").val('0');	// Set cursor on not selected product
	jQuery("#search_idprodfournprice").val('');*/
	jQuery("#tournee_line_type_thirdparty").prop('checked',true).change();
	jQuery("#tournee_line_type_tournee").prop('checked',false).change();
	jQuery("#BL").show();
	jQuery("#facture").show();
	jQuery("#etiquettes").show();
	jQuery("#tempstheorique").show();
	jQuery("#infolivraison").show();
}
function setfortournee() {
	console.log("Call setfortournee. We hide some fields and show dates");
	jQuery("#tournee_line_type_thirdparty").prop('checked',false).change();
	jQuery("#tournee_line_type_tournee").prop('checked',true).change();

	/*jQuery("#price_ht").val('').hide();
	jQuery("#multicurrency_price_ht").hide();
	jQuery("#price_ttc").hide();	// May no exists
	jQuery("#fourn_ref").hide();
	jQuery("#tva_tx").hide();
	jQuery("#buying_price").show();
	jQuery("#title_vat").hide();*/
	jQuery("#BL").hide();
	jQuery("#facture").hide();
	jQuery("#etiquettes").hide();
	jQuery("#tempstheorique").hide();
	jQuery("#infolivraison").hide();
}

function onStartEdition(){
  $("#tournee_line_type_thirdparty").on( "click", function() {
    setfor3party();
  });
  $("#tournee_line_type_tournee").on( "click", function() {
    setfortournee();
  });

  $("#BL1").on( "click", function() {
    BL1choix();
  });
  $("#BL2").on( "click", function() {
    BL1choix();
  });

  $("#socid").on( "change", function() {
    changeClient();
  });
  $("#tourneeincluseid").on( "change", function() {
    changeTourneeIncluse();
  });
}

 </script>



<!--script au chargement -->
 <script>
 function auChargementNouvelleLigne(){
   mettreAjaxPartout();

   onStartEdition();
 }
 $(document).ready(function(){
   auChargementNouvelleLigne();
 });

 </script>
