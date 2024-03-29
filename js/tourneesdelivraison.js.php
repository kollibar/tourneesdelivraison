<?php
/* Copyright (C) 2019 Thomas Kolli <thomas@brasserieteddybeer.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER'))  define('NOREQUIREUSER', '1');
if (!defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (!defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))        define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');


/**
 * \file    tourneesdelivraison/js/tourneesdelivraison.js.php
 * \ingroup tourneesdelivraison
 * \brief   JavaScript file for module TourneesDeLivraison.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/../main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');
?>

/* Javascript library of module TourneesDeLivraison */









function afficheLigne(rowid){

}

function masqueLigne(rowid){

}

function nouvelleLigne(rowid){

}
function supprimeLigne(rowid){

}


/*
Fonction pour l'ajaxisation
*/
let listeGET = new Array();
let listeGETattente=new Array();
let waitForLogin=0;
let waitForLoginGET = new Array();

function auChargementNouvelleLigne(elt){
  mettreAjaxPartout(elt);
  ajaxrow(elt);
  onStartEdition(elt);
}

function reload(){
  $(".tournee-row-reload").each(function(){
    id=$(this).attr('id');
    params=$(this).attr('data').slice(1);

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

    if( params == '' ) url = url + 'lineid='+lineid + suffix;
    else url = url + 'lineid='+lineid + '&' + params + suffix;

    url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

    getAjaxable(url, lineid);
  });

  setTimeout(reload, 1000*60*5);
}
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
      $(this).attr('onclick', 'return false;');
      $(this).click(ajaxable);
    }
  });

  listeFormulaires=Array("edit_note_elt", "edit_tag_tiers", "edit_tag_contact", "addcontact");

  listeFormulaires.forEach(function(item, index, array){
    $('.'+item).submit(function(event){
      console.log('submit: '+item);
      id=$(this).closest(".tournee-row").attr('id');
      lineid=id.slice(4);
      valideFormulaire(lineid,item);
      event.preventDefault();
    });

    $('.'+item).find("input[name='']").click(function(){
      id=$(this).closest(".tournee-row").attr('id');
      $('#'+id).find("input[name='action']").attr('value',''); // met l'action à 0
    });
  });
}

function valideFormulaire(lineid, tag){
  if( $('#'+tag+'-'+lineid).length != 0){
    url=$('#'+tag+'-'+lineid).attr('action');

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

    url=url+$('#'+tag+'-'+lineid).serialize()+suffix;

    url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

    if( url.indexOf('cats-') != -1 ){
      url=url.replaceAll('cats-'+lineid,'cats');
    }

    getAjaxable(url, lineid);
  }
}

function getAjaxable(url, lineid=0, action=''){
  console.log('getAjaxable('+url+','+lineid+','+action+')');
  if( listeGET.includes(url) ) return;

  if( action == '' ){
    pos = url.indexOf('action=');

    if( pos >= 0 ){
        pos2=url.indexOf('&', pos+7);
        if( pos2 >= 0){
          action = url.slice(pos+7, pos2);
        } else {

          action = url.slice(pos+7);
        }
    } else {
      action='';
    }
  }

  if( listeGET.length > 1 ){
    console.log("GET en attente:"+url);

    obj=new Object();
    obj.url=url;
    obj.lineid=lineid;
    obj.action=action;

    var i=0;
    lastAction=0;

    console.log('i:'+i);
    if( listeGETattente.length >0) {

      while(i < listeGETattente.length){
        console.log('listeGETattente.length:'+listeGETattente.length);
        if( listeGETattente[i].action != '' ) lastAction=i;

        if( listeGETattente[i].lineid == obj.lineid && action == ''){
          listeGETattente.splice(i,1);
          i--;
        }

        i=i+1;
      }

      if( obj.action != '' ) {
        t1=listeGETattente.splice(0,lastAction+1);
        t2=listeGETattente.splice(lastAction+1);

        console.log('GOTO:'+lastAction+1);

        t1.push(obj);
        listeGETattente = t1.concat(t2);
      }
      else listeGETattente.push(obj);
    } else listeGETattente.push(obj);

    return;
  }

  listeGET.push(url);

  console.log("GET :"+url);

  $.ajax({
    type: 'get',
    url: url,
    context: this,
    success: this.mySuccess,
    error: this.myError,
    cache: false,
    beforeSend: function(jqXHR, settings) {
      url=settings.url;

      i=url.indexOf('&_=');
      if( i==-1 ) i=url.indexOf('?_=');
      if( i != -1 ) {
        j=url.indexOf('#', i+1);
        k=url.indexOf('&', i+1);

        if( k > 0 && k < j) j=k;
        if( j < 0 ) url=url.slice(0,i);
        else url=url.slice(0,i)+url.slice(j);
      }

      jqXHR.url = url;
    },
    error: function(jqXHR, exception) {
        console.log('erreur GET: '+ jqXHR.url+'   retry');
        getAjaxable(jqXHR.url);
    }
  })
  .done(function( data, textStatus, jqXHR ) {
    ajaxable_callback(data, textStatus, jqXHR);
  });
}

/* fonction à appeller pour les modifications d'éléments qui pevent être ajaxés */
function ajaxable(){
  id=$(this).closest(".tournee-row").attr('id');

  url=$(this).attr('href');

  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

  getAjaxable(url, id);
}

function getLineId(url){
  i=url.indexOf('lineid=');
  if( i< 0) return '';

  k=url.indexOf('&', i);
  if( k < 0 ){
    return url.slice(i+7)
  }else{
    return url.slice(i+7, k);
  }
}


function login(data, status, xhr){
  $('body').append('<iframe id="iframe_login" src="/" style="width:70vw;height:70vh;position: fixed; top:15vh; right:15vw;"></iframe>');

  $('#iframe_login').on('load',function(){
    if(  $('#iframe_login').contents().find('body').find('form:first').attr('id') == login ) {  // écran de connection
      waitForLogin=1;
    } else if(  $('#iframe_login').contents().find('body:first').attr('id') == 'mainbody' ){
        // la connection est déjà faite
        loggedIn();
    }
  });
}

function loggedIn(){
  $('#iframe_login').remove();

  waitForLogin=0;
  while( waitForLoginGET.length > 0){
    url=waitForLoginGET.shift();
    console.log('GET:'+url);
    lineid=getLineId(url);
    console.log('lineid:'+lineid);
    getAjaxable(url, lineid);
  }
}

function getFirstId(data){
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
  return id;
}

function removeFromListeGet(url){
  for (var i = 0; i <= listeGET.length; i++){

    if( listeGET[i] == url ){
      // console.log(i+': '+listeGET[i]+' <=====');
      listeGET.splice(i,i);
      return true;  // trouvé et enlevé !
    } else {
      // console.log(i+': '+listeGET[i]);
    }
  }
  return false; // pas trouvé
}

function ajaxable_callback(data, status, xhr){
  url=xhr.url;

  if( data == "" ){
    url=url.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee');
    window.location.href=url;
    return;
  }

  id = getFirstId(data);

  if( id=="login"){ // la connection a été coupé
    if( waitForLogin ){
      waitForLoginGET.push(url);
      return;
    } else {
        waitForLogin=1;
        waitForLoginGET.push(url);

        removeFromListeGet(url)
        // afficher l'écran de recherche d'url

        login(data, status, xhr);
    }
  }

  if( id == 'dialog-confirm'){
    if( url.indexOf("actionajax=true") != -1){
      data=data.replace('after location.href','after form validation');
      $('#formulaireConfirm').html(data.replace('location.href = urljump;','getAjaxable(urljump)'));
    } else {
      $('#formulaireConfirm').html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));
    }
    return;
  }

  m=data.indexOf('>',l);
  n=data.lastIndexOf('</'+balise+'>');

  data=data.slice(m+1,n);

  i=url.indexOf('&_=');
  if( i==-1 ) i=url.indexOf('?_=');
  if( i != -1 ) {
    j=url.indexOf('#', i+1);
    k=url.indexOf('&', i+1);

    if( k > 0 && k < j) j=k;
    if( j < 0 ) url=url.slice(0,i);
    else url=url.slice(0,i)+url.slice(j);
  }

  console.log("reception :"+"#"+id+'    '+url);

  if( removeFromListeGet(url) == false ) return;

  if( listeGETattente.length > 0 ){
    obj=listeGETattente.shift();
    getAjaxable(obj.url);
  }

  if( id.slice(0,4) == "row-"){
    if( data.indexOf('id="cats_line"') != -1 ){
      data=data.replaceAll('cats_line','cats_line'+id);
    }
    if( data.indexOf('id="cats_tiers"') != -1 ){
      data=data.replaceAll('cats_tiers','cats_tiers'+id);
    }
  }

  $("#"+id).html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));

  auChargementNouvelleLigne($("#"+id));

}

/* fonction à appeller sur les formulaires pour lesquels le formulaire est ajaxé mais pas sa validation (élément .askAjaxable) */
function askAjaxable(elt){
  console.log('askAjaxable('+elt+')');
  url=elt.href;
  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

  // console.log("GET :"+url);
  // $.get(url, askAjaxable_callback);
  getAjaxable(url);
}
function askAjaxable_callback(data,status){
  $('#formulaireConfirm').html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));
}


/* fonction à appeller sur les formulaires pour lesquels le formulaire est ajaxé ET la validation de ce formulaire (élément .askActionAjaxable) */
function askActionAjaxable(elt){
  console.log('askActionAjaxable('+elt+')');
  url=elt.href;
  url=url.replace('tourneesdelivraison/tournee','tourneesdelivraison/ajax/tournee');

  // console.log("GET :"+url);
  // $.get(url, askActionAjaxable_callback);
  getAjaxable(url);
}

function askActionAjaxable_callback(data,status){
  data=data.replace('after location.href','after form validation');

  $('#formulaireConfirm').html(data.replace('location.href = urljump;','getAjaxable(urljump)'));
  //$('#formulaireConfirm').html(data.replaceAll('tourneesdelivraison/ajax/tournee', 'tourneesdelivraison/tournee'));
}


/*
   Fonctions pour l'édition
*/
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
