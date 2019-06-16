<?php
/* Copyright (C) 2010-2013	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2010-2011	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012-2013	Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2017		Juanjo Menent		<jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $conf
 * $langs
 * $dateSelector
 * $forceall (0 by default, 1 for supplier invoices/orders)
 * $element     (used to test $user->rights->$element->creer)
 * $permtoedit  (used to replace test $user->rights->$element->creer)
 * $object_rights->creer initialized from = $object->getRights()
 * $disableedit, $disablemove, $disableremove
 *
 * $type, $text, $description, $line
 */

// Protection to avoid direct call of template
if (empty($object) || ! is_object($object))
{
	print "Error, template page can't be called as URL";
	exit;
}

if (empty($dateSelector)) $dateSelector=0;

// add html5 elements
$domData  = ' data-element="'.$line->element.'"';
$domData .= ' data-id="'.$line->id.'"';

?>
<?php $coldisplay=0; ?>
<!-- BEGIN PHP TEMPLATE objectline_view.tpl.php -->
<tr  id="row-<?php echo $line->id?>" class="drag drop oddeven" <?php echo $domData; ?> >
	<input type="hidden" name="lineid" id="lineid" value="<?php echo $line->id?>">



	<td class="linecolselect" align="center" colspan="2"><?php $coldisplay++;$coldisplay++; ?>    </td> <!-- A FAIRE Ajouter case à cocher-->

	<td class="linecolnum" align="center"><?php $coldisplay++; ?><?php echo ($i+1); ?></td>

	<td class="nobottom linecolclient" align="left" width="5">
		<span class="tournee_line_type_thirdparty">
			<label for="tournee_line_type_thirdparty">
				<input type="radio" class="tournee_line_type_thirdparty" name="tournee_line_type_thirdparty" id="tournee_line_type_thirdparty" value="thirdparty"  <?php echo ($line->type==0?' checked':''); ?> >
				<?php echo $langs->trans('Customer'); ?>
			</label>
			<?php echo $form->select_company($line->fk_soc, 'socid', '(s.client = 1 OR s.client = 3)  AND s.status = 1', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300');
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
		</span>
		<span class="tournee_line_type_tournee">
			<label for="tournee_line_type_tournee">
				<input type="radio" class="tournee_line_type_tournee" name="tournee_line_type_tournee" id="tournee_line_type_tournee" value="tournee" <?php echo ($line->type==1?' checked':''); ?>>
				<?php echo $langs->trans('TourneeDeLivraison'); ?>
			</label>
			<?php echo $formtournee->select_tourneedelivraison($line->fk_tourneedelivraison_incluse, 'tourneeincluseid', '(s.statut=1)', 'SelectTourneeDeLivraison', 0, null, 0, 'minwidth300');
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
		</span>
	</td>

	<td align="right" class="linecoldocs nowrap"><?php $coldisplay++; ?>
		<table>
			<tr><td>
				<label for="BL1"><?php echo $langs->trans('BL'); ?></label>
				<input type="checkbox" name="BL1" id="BL1" value="BL1" <?php echo ($line->BL>0)?'checked':'' ?> >
				<input type="checkbox" name="BL2" id="BL2" value="BL2" <?php echo ($line->BL>1)?'checked':'' ?> >
			</td></tr>
			<tr><td>
				<label for="facture"><?php echo $langs->trans('Invoice'); ?></label>
				<input type="checkbox" name="facture" id="facture" value="facture" <?php echo ($line->facture>0)?'checked':'' ?> >
			</td></tr>
			<tr><td>
				<label for="etiquettes"><?php echo $langs->trans('Etiquettes'); ?></label>
				<input type="checkbox" name="etiquettes" id="etiquettes" value="etiquettes" <?php echo ($line->etiquettes>0)?'checked':'' ?> >
			</td></tr>
		</table>
	</td>

	<?php if( 1==0 && $line->element=='tourneeunique_lines'){ ?>
	  <td class="linecolcmde">A FAIRE</td>
	  <td class="linecolexpedition">A FAIRE</td>'
	  <td class="linecolfacture">A FAIRE</td>'
	<?php } ?>



	<td align="right" class="linecoltpstheo nowrap"><?php $coldisplay++; ?>
		<input type="text" size="5" name="tempstheorique" id="tempstheorique" class="flat right" value="<?php echo $line->tpstheorique; ?>">
	</td>

	<!--<td align="right" class="linecolinfolivraison nowrap"><?php //$coldisplay++; ?>
		<textarea id="infolivraison" name="infolivraison" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
	</td>-->

	<td align="right" class="linecolnote nowrap"><?php $coldisplay++; $coldisplay++; ?>
		<table>
			<tr><td colspan="2">
		<?php
		// Categories
		if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
				$langs->load('categories');

				// Customer
				//print '<tr class=""><td>' . fieldLabel($line->nomelement . 'CategoriesShort', 'custcats') . '</td>';
				//print '<td colspan="3">';
				$cate_arbo = $form->select_all_categories($line->element, null, null, null, null, 1);
				$c = new Categorie($db);
				$cats = $c->containing($line->id, $line->element);
				$arrayselected=array();
				foreach ($cats as $cat) {
					$arrayselected[] = $cat->id;
				}
				print $form->multiselectarray('cats_line', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
				//print "</td></tr>";
			}
			?>
		</td></tr>
		<tr>
		<td align="right" class="linecolnote_public nowrap">
			<textarea id="note_public" name="note_public" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
		</td>
		<td align="right" class="linecolnote_private nowrap"><?php $coldisplay++; ?>
			<textarea id="note_private" name="note_private" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
		</td>
	</tr>
</table>
</td>

	<td align="right" class="linecoladresselivraison nowrap"><?php $coldisplay++; ?>
		<input type="hidden" value="0" id="adresselivraisonid" name="adresselivraisonid">
		<?php //remettre le champs $form_>select_contact en mettant un système pour actualisé les valeurs en fon,ction  du socid
		//$ret=$form->select_contacts($line->fk_soc, '', 'adresselivraisonid', 1, '', '',0,'', 0,0,array(), false,'',''); ?>
	</td>

	<?php if( empty($conf->global->TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE)){ ?>
		<td align="right" class="linecolcontact nowrap"><?php $coldisplay++; ?>
			<?php
				if( count($line->lines) >0 ){
					foreach($line->lines as $contact){
						echo $contact->getFullAddress();
						echo '<br />';
					}
				}
			?>
		</td>
	<?php } ?>


	<td class="nobottom linecoledit" align="center" valign="middle">
		<input type="submit" class="button" value="<?php echo $langs->Trans("Update"); ?>" name="updateline" id="updateline">
		<input type="submit" class="button" value="<?php echo $langs->Trans("Cancel"); ?>" name="" id="">
	</td>

</tr>

<?php
//Line extrafield
if (!empty($extrafieldsline))
{
	print $line->showOptionals($extrafieldsline, 'view', array('style'=>'class="drag drop oddeven"','colspan'=>$coldisplay), '', '', empty($conf->global->MAIN_EXTRAFIELDS_IN_ONE_TD)?0:1);
}
?>

<script type="text/javascript">

/* JQuery for product free or predefined select */
jQuery(document).ready(function() {
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

	<?php if (GETPOST('tournee_line_type_thirdparty') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfor3party();
	<?php } ?>
	<?php if (GETPOST('tournee_line_type_tournee') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfortournee();
	<?php } ?>

	//jQuery("#infolivraison").val(<?php echo '"'.$line->infolivraison.'"';?>);
	jQuery("#note_public").val(<?php echo '"'.$line->note_public.'"';?>);
	jQuery("#note_private").val(<?php echo '"'.$line->note_private.'"';?>);
});

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

</script>

<!-- END PHP TEMPLATE tourneeline_edit.tpl.php -->
