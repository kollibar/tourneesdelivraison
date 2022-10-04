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
<tr  id="row-<?php echo $line->id?>" class="drag drop oddeven tournee-row" <?php echo $domData; ?> >
	<input type="hidden" name="lineid" id="lineid" value="<?php echo $line->id?>">

	<input type="hidden" name="var" id="var" value="<?php echo $var?>">
	<input type="hidden" name="i" id="i" value="<?php echo $i?>">
	<input type="hidden" name="num" id="num" value="<?php echo $num?>">

	<td class="linecolselect" align="center" colspan="2"><?php $coldisplay++;$coldisplay++; ?>    </td> <!-- A FAIRE Ajouter case à cocher-->

	<td class="linecolnum" align="center"><?php $coldisplay++; ?><?php echo ($i+1); ?></td>

	<td class="nobottom linecolclient" align="left" width="5">

		<span>
			<?php if ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR){
				echo $line->getBannerAddressSociete('bannerSociete_'.$line->id);
			} else {
				echo $line->getBannerTourneeLivraison();
			}
			?>
		</span>
		<span>
			<input type="checkbox" name="force_email_soc" id="force_email_soc" value="1" <?php echo empty($line->force_email_soc)?'':'checked' ?> >
		  <label for="force_email_soc"> <?php echo $langs->trans('ajoutMailAuto'); ?> </label>
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
			<textarea id="note_public" name="note_public" rows="3" style="margin-top: 5px; width: 98%" class="flat"><?php echo $line->note_public;?></textarea>
		</td>
		<td align="right" class="linecolnote_private nowrap"><?php $coldisplay++; ?>
			<textarea id="note_private" name="note_private" rows="3" style="margin-top: 5px; width: 98%" class="flat"><?php echo $line->note_public;?></textarea>
		</td>
	</tr>
</table>
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
	<?php if (GETPOST('tournee_line_type_thirdparty') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfor3party();
	<?php } ?>
	<?php if (GETPOST('tournee_line_type_tournee') == 'predef') { // When we submit with a predef product and it fails we must start with predef ?>
		setfortournee();
	<?php } ?>
});
</script>

<!-- END PHP TEMPLATE tourneeline_edit.tpl.php -->
