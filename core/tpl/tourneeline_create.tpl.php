 <?php
 /*
 * Need to have following variables defined:
 * $object (tourneedelivraison // tourneeunique)
 * $conf
 * $langs
 * $dateSelector
 */


// Protection to avoid direct call of template
if (empty($object) || ! is_object($object))
{
	print "Error, template page can't be called as URL";
	exit;
}

$colspan = 4;

$objectline = null;
if (!empty($extrafieldsline))
{
	if ($this->element=='tourneedelivraison_lines') {
		$objectline = new TourneeDeLivraison_lines($this->db);
	}
	elseif ($this->element=='tourneeunique_lines') {
		$objectline = new TourneeUnique_lines($this->db);
	}
}

?>

<!-- BEGIN PHP TEMPLATE tourneeline_create.tpl.php -->
<!--<?php
$nolinesbefore=(count($this->lines) == 0 || $forcetoshowtitlelines);
if ($nolinesbefore) {
?>
<thead>
	<tr class="liste_titre nodrag nodrop">
		<td class="linecolselect" align="center" width="5">&nbsp;</td>
		<td class="linecolnum" align="center" width="5">&nbsp;</td>
		<td class="linecolclient"><?php echo $langs->trans('Customer')." // ".$langs->trans('TourneeDeLivraison');?></td>
		<td class="linecolbl"><?php echo $langs->trans('BL'); ?></td>
		<td class="linecolfacture"><?php echo $langs->trans('Facture'); ?></td>
		<td class="linecoletiquettes"><?php echo $langs->trans('Etiquettes'); ?></td>
		<td class="linecoltpsthoe"><?php echo $langs->trans('TempsTheorique'); ?></td>
		<td class="linecolinfolivraison"><?php echo $langs->trans('InfoLivraison'); ?></td>
		<td class="linecoladresselivraison"><?php echo $langs->trans('AdresseLivraison'); ?></td>
		<td class="linecolcontact"><?php echo $langs->trans('Contact'); ?></td>
	</tr>
	</thead>
<?php
}
$coldisplay=2;
?>
-->

<tbody>
<td class="nobottom linecolselect" align="center" width="5" colspan="2"></td>
<td class="nobottom linecolnum" align="center" width="5"></td>

<td class="nobottom linecolclient" align="left" width="5">
<span class="tournee_line_type_thirdparty">
	<label for="tournee_line_type_thirdparty">
		<input type="radio" class="tournee_line_type_thirdparty" name="tournee_line_type_thirdparty_client" id="tournee_line_type_thirdparty_client" value="client"  <?php echo (GETPOST('tournee_line_type')=='client'?' checked':''); ?> >
		<?php echo $langs->trans('Customer'); ?>
	</label>
	<?php echo $form->select_company('', 'socid_client', '(s.client = 1 OR s.client = 3) AND s.status = 1', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300');
							//string	$selected	Preselected type
							//string	$htmlname	Name of field in form
							//string	$filter	Optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
							//string	$showempty	Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
							//int	$showtype	Show third party type in combolist (customer, prospect or supplier)
							//int	$forcecombo	Force to use standard HTML select component without beautification
							//array	$events	Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
							//string	$filterkey	Filter on key value
							//int	$outputmode	0=HTML select string, 1=Array
							//int	$limit	Limit number of answers
							//string	$morecss	Add more css styles to the SELECT component
							//string	$moreparam	Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
							//bool	$multiple	add [] in the name of element and add 'multiple' attribut
	?>
</span><br />
<span class="tournee_line_type_thirdparty">
	<label for="tournee_line_type_thirdparty_fournisseur">
		<input type="radio" class="tournee_line_type_thirdparty" name="tournee_line_type_thirdparty_fournisseur" id="tournee_line_type_thirdparty_fournisseur" value="fournisseur"  <?php echo (GETPOST('tournee_line_type')=='fournisseur'?' checked':''); ?> >
		<?php echo $langs->trans('Supplier'); ?>
	</label>
	<?php echo $form->select_company('', 'socid_fournisseur', 's.fournisseur = 1 AND s.status = 1', 'SelectThirdParty_fournisseur', 0, 0, null, 0, 'minwidth300'); ?>
</span><br />
<span class="tournee_line_type_tournee">
	<label for="tournee_line_type_tournee">
		<input type="radio" class="tournee_line_type_tournee" name="tournee_line_type_tournee" id="tournee_line_type_tournee" value="tournee" <?php echo (GETPOST('tournee_line_type')=='tournee'?' checked':''); ?>>
		<?php echo $langs->trans('TourneeDeLivraison'); ?>
	</label>
	<?php echo $formtournee->select_tourneedelivraison('', 'tourneeincluseid', '(s.statut=1)', 'SelectTourneeDeLivraison', 0, null, 0, 'minwidth300');
							//string	$selected	Preselected type
							//string	$htmlname	Name of field in form
							//string	$filter	Optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
							//string	$showempty	Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
							//int	$forcecombo	Force to use standard HTML select component without beautification
							//array	$events	Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
							//string	$filterkey	Filter on key value
							//int	$outputmode	0=HTML select string, 1=Array
							//int	$limit	Limit number of answers
							//string	$morecss	Add more css styles to the SELECT component
							//string	$moreparam	Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
							//bool	$multiple	add [] in the name of element and add 'multiple' attribut
	?>
</span><br />
<span>
  <input type="checkbox" name="force_email_soc" id="force_email_soc" value="1" >
  <label for="force_email_soc"> <?php echo $langs->trans('ajoutMailAuto'); ?> </label>
</span>
</td>

<td align="right" class="linecoldocs nowrap"><?php $coldisplay++; ?>
		<table id="tabledocs">
			<tr><td>
				<label for="BL1"><?php echo $langs->trans('BL'); ?></label>
				<input type="checkbox" name="BL1" id="BL1" value="BL1">
				<input type="checkbox" name="BL2" id="BL2" value="BL2">
			</td></tr>
			<tr><td>
				<label for="facture"><?php echo $langs->trans('Invoice'); ?></label>
				<input type="checkbox" name="facture" id="facture" value="facture">
			</td></tr>
			<tr><td>
				<label for="etiquettes"><?php echo $langs->trans('Etiquettes'); ?></label>
				<input type="checkbox" name="etiquettes" id="etiquettes" value="etiquettes" checked>
			</td></tr>
		</table>
	</td>

<?php if( $this->element=='tourneeunique_lines'){ ?>
  <td class="linecolcmde"><?php $langs->trans('Order'); ?></td>
  <td class="linecolexpedition"><?php $langs->trans('Sending'); ?></td>'
  <td class="linecolfacture"><?php $langs->trans('Invoice'); ?></td>'
<?php } ?>


	<td class="nobottom linecoltpstheo" align="right">
	<input type="text" size="5" name="tempstheorique" id="tempstheorique" class="flat right" value="<?php echo (isset($_POST["tempstheorique"])?GETPOST("tempstheorique",'alpha',2):''); ?>">
	</td>
  <!--
	<td class="nobottom linecolinfolivraison" align="right">
	<textarea id="infolivraison" name="infolivraison" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
</td>-->

  <td class="nobottom linecolnote" align="right">
    <table>
      <tr><td colspan="2">
  <?php // Categories
  	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
  			$langs->load('categories');

  			//print '<tr><td class="toptd">' . fieldLabel($line->nomelement . 'CategoriesShort', 'custcats') . '</td><td colspan="3">';
  			$cate_arbo = $form->select_all_categories($this->element.'_lines', null, 'parent', null, null, 1);
  			print $form->multiselectarray('cats_line', $cate_arbo, GETPOST('cats_line', 'array'), null, null, null, null, "90%");
  			//print "</td></tr>";
  		}
      ?>
        </td>
      </tr>
      <tr>
        <td>
          <textarea id="note_public" name="note_public" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
  	     </td>

        <td class="nobottom linecolnote_private" align="right">
  	     <textarea id="note_private" name="note_private" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
        </td>
      </tr>
    </table>
  </td>

	<td class="nobottom linecoladresselivraison" id="td_adresselivraisonid" align="right">
		<?php //remplit par ajax après selection d'une société
		?>
	</td>

	<td class="nobottom linecoledit" align="center" valign="middle">
	<input type="submit" class="button" value="<?php echo $langs->Trans("Add"); ?>" name="addline" id="addline">
	</td>

<tbody>



<script type="text/javascript">

var listeContact = new Map();

/* JQuery for product free or predefined select */
jQuery(document).ready(function() {
	$("#tournee_line_type_thirdparty_client").on( "click", function() {
		setfor3party_client();
	});
  $("#tournee_line_type_thirdparty_fournisseur").on( "click", function() {
		setfor3party_fournisseur();
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

	$("#socid_client").on( "change", function() {
		changeClient();
	});
  $("#socid_fournisseur").on( "change", function() {
		changeFournisseur();
	});
	$("#tourneeincluseid").on( "change", function() {
		changeTourneeIncluse();
	});

	<?php if (GETPOST('tournee_line_type_thirdparty') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfor3party();
	<?php } ?>
	<?php if (GETPOST('tournee_line_type_tournee') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfortournee();
	<?php } ?>

});

function changeClient(){
	if( $("#socid_client").val()!=-1 && $("#socid_client").val()!=0) setfor3party_client();
}
function changeFournisseur(){
	if( $("#socid_fournisseur").val()!=-1 && $("#socid_fournisseur").val()!=0) setfor3party_fournisseur();
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
function setfor3party_client() {
	console.log("Call set3party. We show most fields");
	/*jQuery("#search_idprod").val('');
	jQuery("#idprod").val('');
	jQuery("#idprodfournprice").val('0');	// Set cursor on not selected product
	jQuery("#search_idprodfournprice").val('');*/
	jQuery("#tournee_line_type_thirdparty_client").prop('checked',true).change();
  jQuery("#tournee_line_type_thirdparty_fournisseur").prop('checked',false).change();
	jQuery("#tournee_line_type_tournee").prop('checked',false).change();

  jQuery("#tabledocs").show();
	jQuery("#tempstheorique").show();
	jQuery("#infolivraison").show();

  $("#socid_fournisseur").val("-1").change();
  $("#tourneeincluseid").val("-1").change();

  if( $("#socid_client").val()!=-1 && $("#socid_client").val()!=0){
    loadAdresseLivraison($("#socid_client").val());
  }
}
function setfor3party_fournisseur() {
	console.log("Call set3party_fournisseur. We show most fields");
	/*jQuery("#search_idprod").val('');
	jQuery("#idprod").val('');
	jQuery("#idprodfournprice").val('0');	// Set cursor on not selected product
	jQuery("#search_idprodfournprice").val('');*/
	jQuery("#tournee_line_type_thirdparty_fournisseur").prop('checked',true).change();
	jQuery("#tournee_line_type_thirdparty_client").prop('checked',false).change();
	jQuery("#tournee_line_type_tournee").prop('checked',false).change();
  jQuery("#tabledocs").hide();
	jQuery("#tempstheorique").show();
	jQuery("#infolivraison").show();

  $("#socid_client").val("-1").change();
  $("#tourneeincluseid").val("-1").change();
  if( $("#socid_fournisseur").val()!=-1 && $("#socid_fournisseur").val()!=0){
    loadAdresseLivraison($("#socid_fournisseur").val());
  }
}
function setfortournee() {
	console.log("Call setfortournee. We hide some fields and show dates");
	jQuery("#tournee_line_type_thirdparty_client").prop('checked',false).change();
  jQuery("#tournee_line_type_thirdparty_fournisseur").prop('checked',false).change();
	jQuery("#tournee_line_type_tournee").prop('checked',true).change();

	/*jQuery("#price_ht").val('').hide();
	jQuery("#multicurrency_price_ht").hide();
	jQuery("#price_ttc").hide();	// May no exists
	jQuery("#fourn_ref").hide();
	jQuery("#tva_tx").hide();
	jQuery("#buying_price").show();
	jQuery("#title_vat").hide();*/
	jQuery("#tabledocs").hide();
	jQuery("#tempstheorique").hide();
	jQuery("#infolivraison").hide();


  $("#socid_fournisseur").val("-1").change();
  $("#socid_client").val("-1").change();

  $("#td_adresselivraisonid").html("").change();
}

function loadAdresseLivraison(fk_soc){
  if( listeContact.has(fk_soc)){
    $("#td_adresselivraisonid").html(listeContact.get(fk_soc)).change();
  } else {
    <?php
      $r=explode("/",$_SERVER["PHP_SELF" ]);
      $r[]=$r[count($r)-1];
      $r[count($r)-2]="ajax";
      $url=implode("/",$r);
    ?>
    $.get("<?php echo $url."?id=$object->id&action=ajax_actualiseFormAddressLivraison&new_fk_soc=";?>"+fk_soc, function(data, status){
      if( status=="success"){
        $("#td_adresselivraisonid").html(data).change();
        listeContact.set(fk_soc,data);
      }
    });
  }
}

</script>


<!-- END PHP TEMPLATE tourneeline_create.tpl.php -->
