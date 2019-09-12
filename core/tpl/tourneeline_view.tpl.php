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
<!-- BEGIN PHP TEMPLATE tourneeline_view.tpl.php -->
<tr  id="row-<?php echo $line->id?>" class="drag drop oddeven" <?php echo $domData; ?> >

	<td class="linecolselect" align="center"><?php $coldisplay++; ?>
	</td>

	<?php
	if ($num > 1 && $conf->browser->layout != 'phone' && ($this->situation_counter == 1 || !$this->situation_cycle_ref) && empty($disablemove)) { ?>
	<td align="center" class="linecolmove tdlineupdown"><?php $coldisplay++; ?>
		<?php if ($i > 0) { ?>
		<a class="lineupdown" href="<?php echo $_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=up&amp;rowid='.$line->id; ?>">
		<?php echo img_up('default',0,'imgupforline'); ?>
		</a>
		<?php } ?>
		<?php if ($i < $num-1) { ?>
		<a class="lineupdown" href="<?php echo $_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=down&amp;rowid='.$line->id; ?>">
		<?php echo img_down('default',0,'imgdownforline'); ?>
		</a>
		<?php } ?>
	</td>
    <?php } else { ?>
    <td align="center"<?php echo (($conf->browser->layout != 'phone' && empty($disablemove)) ?' class="linecolmove tdlineupdown"':' class="linecolmove"'); ?>><?php $coldisplay++; ?></td>
	<?php } ?>

	<td class="linecolnum" align="center"><?php $coldisplay++; ?><?php echo ($i+1); ?>
	</td><!--linecolnum-->

	<?php

		if ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT || $line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_FOURNISSEUR){
	?>
			<td align="left" class="linecolclient nowrap"><?php $coldisplay++; ?>
				<?php echo $line->getBannerAddressSociete('bannerSociete_'.$line->id); ?><br>

				<?php if(!empty($line->force_email_soc)) {?>
					<input type="checkbox" name="force_email_soc_"<?php echo $line->id;?> id="force_email_soc_"<?php echo $line->id;?> value="1" disabled <?php echo empty($line->force_email_soc)?'':'checked' ?> >
					<label for="force_email_soc_"<?php echo $line->id;?> > <?php echo $langs->trans('ajoutMailAuto'); ?> </label>
				<?php } ?>

				<?php if( !empty($conf->global->TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE)){ ?>


					<table class="noborderbottom">
					<?php
						$liste=array();
						if( count($line->lines) >0 || $this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){
							echo '<tr><td style="font-weight:bold;">'.$langs->trans('Contact').':</td></tr>';
						}
						if( count($line->lines) >0 ){
							foreach($line->lines as $contactline){
								$liste[]=$contactline->fk_socpeople;
								print '<tr class="contactlineid" id="contactlineid_'.$contactline->id.'"><td>';
								print $contactline->getBannerContact();
								print '</td><td>';
								if($this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){
									print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deletecontact&amp;contactid=' . $contactline->id . '">';
									print img_delete();
									print '</a>';
								}
								print '</td></tr>';
							}
						}

						if ($this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){ ?>
							<tr>
								<td>
									<?php $ret=$form->select_contacts($line->fk_soc, '', 'addcontactid_'.$line->id, 1, $liste, '',0,'', 0,0,array(), false,'',''); ?>
								</td><?php if($ret-count($liste)>0){ ?>
								<td>
									<?php
										print '<input type="checkbox" name="addcontactid_'.$line->id.'_noemail" id="addcontactid_'.$line->id.'_noemail" value="addcontactid_'.$line->id.'_noemail"' . ((!empty($this->no_email))?'checked':'') . ' >';
										print '<label for="addcontactid_'.$line->id.'_noemail">' . $langs->trans('noEmailAuto'). '</label>';

										if (! empty($conf->global->TOURNEESDELIVRAISON_SMS)){
											print '<input type="checkbox" name="addcontactid_'.$line->id.'_sms" id="addcontactid_'.$line->id.'_sms" value="addcontactid_'.$line->id.'_sms"' . ((!empty($this->no_email))?'checked':'') . 'disabled >';
											print '<label for="addcontactid_'.$line->id.'_sms">' . $langs->trans('sms'). '</label>';
										}
									?>
								</td>
								<td>
									<input type="submit" class="button" value="<?php echo $langs->Trans("AjouterContact"); ?>" name="addcontact_<?php echo $line->id; ?>" id="addcontact_<?php echo $line->id; ?>" <?php  echo (($ret-count($liste)<=0)?'disabled':'')?>>
								</td><?php } ?>
							</tr>
						<?php
						}
					?>
					</table>

				<?php } ?>










			</td>
	<?php
		}
		else
		{
	?>
			<td align="right" class="linecolclient nowrap"><?php $coldisplay++; ?><?php echo $line->getBannerTourneeLivraison(); ?></td>
	<?php
		}
	?>

	<?php
	$parent=$line->getParent();

	if( $line->element != 'tourneeunique_lines' || $parent->statut == STATUS_DRAFT){ ?>
	<td align="right" class="linecoldocs nowrap"><?php $coldisplay++; ?>
		<?php if($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT){ ?>
		<table class="noborderbottom">
			<tr><td>
				<label for="BL1_<?php echo $line->id; ?>"><?php echo $langs->trans('BL'); ?></label>
				<input id="BL1_<?php echo $line->id; ?>" name="BL1_<?php echo $line->id; ?>" type="checkbox" disabled <?php echo ($line->BL>0)?'checked':'' ?> >
				<input id="BL2_<?php echo $line->id; ?>" name="BL2_<?php echo $line->id; ?>" type="checkbox" disabled <?php echo ($line->BL>1)?'checked':'' ?> >
			</td></tr>
			<tr><td>
				<label for="facture_<?php echo $line->id; ?>"><?php echo $langs->trans('Invoice'); ?></label>
				<input type="checkbox" id="facture_<?php echo $line->id; ?>" name="facture_<?php echo $line->id; ?>" disabled <?php echo ($line->facture>0)?'checked':'' ?> >
			</td></tr>
			<tr><td>
				<label for="etiquettes_<?php echo $line->id; ?>"><?php echo $langs->trans('Etiquettes'); ?></label>
				<input type="checkbox" id="etiquettes_<?php echo $line->id; ?>" name="etiquettes_<?php echo $line->id; ?>" disabled <?php echo ($line->etiquettes>0)?'checked':'' ?> >
			</td></tr>
		</table>
	<?php } ?>
	</td>
<?php } ?>


	<?php

	if( $line->element=='tourneeunique_lines' && $parent->statut != STATUS_DRAFT){ ?>
	  <td class="linecolcmde">
			<?php if( $line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT) { ?>

			<table class="noborderbottom">
				<?php foreach ($line->lines_cmde as $lcmde) {
					$cmde=new Commande($this->db);
					$cmde->fetch($lcmde->fk_commande);
					$numshipping = $cmde->nb_expedition();
					$numinvoice=0;
					?>
				<tr><td>
					<?php
					$morehtml=$cmde->getNomUrl();
					echo $lcmde->getMenuStatut(false,$morehtml); ?>
				</td>
				<td>
					<?php
					if( $lcmde->statut != TourneeUnique_lines_cmde::DATE_OK && $lcmde->statut != TourneeUnique_lines_cmde::DATE_NON_OK) continue;
					if( $numshipping!=0){
						echo '<table class="noborderbottom">';
						foreach ($lcmde->lines as $lelt) {
							if($lelt->type_element == 'shipping'){
								//$elt=$lelt->loadElt();
								echo '<tr><td>';
								echo $lelt->getMenuStatut();
								echo '</td></tr>';
								$nb_exp+=1;
							}
						}
						echo '</table>';
					}
					if( ! $lcmde->estLivreCompletement() ){
						// Ship
						if ($cmde->statut > Commande::STATUS_DRAFT && $cmde->statut < Commande::STATUS_CLOSED && ($cmde->getNbOfProductsLines() > 0 || !empty($conf->global->STOCK_SUPPORTS_SERVICES))) {
							if (($conf->expedition_bon->enabled && $user->rights->expedition->creer) || ($conf->livraison_bon->enabled && $user->rights->expedition->livraison->creer)) {
								if ($user->rights->expedition->creer) {
									print '<div class="inline-block divButAction"><a class="butAction tourneeBoutons" href="' . DOL_URL_ROOT . '/expedition/shipment.php?id=' . $cmde->id . '">' . $langs->trans('CreateShipment') . '</a></div>';
								} else {
									print '<div class="inline-block divButAction"><a class="butActionRefused tourneeBoutons" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('CreateShipment') . '</a></div>';
								}
							} else {
								$langs->load("errors");
								print '<div class="inline-block divButAction"><a class="butActionRefused tourneeBoutons" href="#" title="' . dol_escape_htmltag($langs->trans("ErrorModuleSetupNotComplete")) . '">' . $langs->trans('CreateShipment') . '</a></div>';
							}
						}
					} ?>
				</td>
				<td>
					<table class="noborderbottom">
						<?php foreach ($lcmde->lines as $lelt) {
							if($lelt->type_element == 'facture'){
								//$elt=$lelt->loadElt();
								$numinvoice+=1;
								echo '<tr><td>';
								echo $lelt->getMenuStatut();
								echo '</td></tr>';
							}
						} ?>
					</table>
					<?php
					if($numinvoice==0){
						// Create bill and Classify billed
						// Note: Even if module invoice is not enabled, we should be able to use button "Classified billed"
						if ($cmde->statut > Commande::STATUS_DRAFT && ! $cmde->billed) {
							if (! empty($conf->facture->enabled) && $user->rights->facture->creer && empty($conf->global->WORKFLOW_DISABLE_CREATE_INVOICE_FROM_ORDER)) {
								print '<div class="inline-block divButAction"><a class="butAction tourneeBoutons" href="' . DOL_URL_ROOT . '/compta/facture/card.php?action=create&amp;origin=' . $cmde->element . '&amp;originid=' . $cmde->id . '&amp;socid=' . $cmde->socid . '">' . $langs->trans("CreateBill") . '</a></div>';
							}
							if (1==0 && $user->rights->commande->creer && $cmde->statut >= Commande::STATUS_VALIDATED && empty($conf->global->WORKFLOW_DISABLE_CLASSIFY_BILLED_FROM_ORDER) && empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT)) {
								print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $cmde->id . '&amp;action=classifybilled">' . $langs->trans("ClassifyBilled") . '</a></div>';
							}
						}
					} ?>
				</td>
			</tr>
			<?php }
			$nb=$line->getNbCmdeParStatut();
			if( $nb[TourneeUnique_lines_cmde::DATE_NON_OK]==0 && $nb[TourneeUnique_lines_cmde::DATE_OK]==0
					&& ! empty($conf->commande->enabled)
					&& $user->rights->commande->creer) {
						$langs->load("orders");
						print '<tr><td>'.$langs->Trans('PasDeCmde').' :';
						if (!empty($line->aucune_cmde))
						{
							print '<a href="'.$_SERVER['PHP_SELF'].'?action=unsetnocmde_elt&id='.$parent->id.'&lineid='.$line->id.'">';
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER['PHP_SELF'].'?action=setnocmde_elt&id='.$parent->id.'&lineid='.$line->id.'">';
							print img_picto($langs->trans("Disabled"),'switch_off');
						}
						print '</a>';
						$date_derniere_livraison=$line->derniereLivraisonLe();
						if( $date_derniere_livraison != null){
							print '<br>'.$langs->Trans('DerniereLivraisonLe').' : ';
							print dol_print_date($date_derniere_livraison);
						}

						print '</td></tr>';
						if( empty($line->aucune_cmde)){
							//print '<tr><td><div class="inline-block divButAction tourneeBoutons"><a class="butAction" href="'.DOL_URL_ROOT.'/commande/card.php?socid='.$line->fk_soc.'&amp;action=create">'.$langs->trans("AddOrder").'</a></div>';
							print '<tr><td><div class="inline-block divButAction tourneeBoutons"><a class="butAction" href="'.DOL_URL_ROOT.'/commande/card.php?socid='.$line->fk_soc.'&amp;action=create&amp;origin=tourneesdelivraison&originid='.$line->id.'">'.$langs->trans("AddOrder").'</a></div>';
							print '</td></tr>';
						}
			} ?>
			</table>
		<?php } ?>
		</td>

	<?php } ?>

	<td align="right" class="linecoltpstheo nowrap"><?php $coldisplay++; ?><?php echo $line->tpstheorique; ?></td>


	<?php if( $object->statut==TourneeGeneric::STATUS_VALIDATED && $action=='edit_note_elt' && $line->id==$selected  && !empty($object_rights->note)){ ?>
		<td align="right" class="linecolnote nowrap"><?php $coldisplay++; ?>
			<form name="edit_note_elt" id="edit_note_elt" action="<?php echo $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=setnote_elt&lineid=' . GETPOST('lineid').'#row-'.GETPOST('lineid'); ?>" method="POST">
			<input type="hidden" name="token" value="<?php echo $_SESSION ['newtoken']; ?>">
			<input type="hidden" name="action" value="setnote_elt">
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="lineid" value="<?php echo $line->id; ?>" >
			<input type="hidden" name="id" value="<?php echo $object->id; ?>">
			<table class="noborderbottom">
				<tr><td colspan="2">
			<?php
			// Categories
			if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
					$langs->load('categories');

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
			<td align="right" class="linecolnote_public nowrap minwidth100">
				<textarea id="note_public_elt" name="note_public_elt" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
			</td>
			<td align="right" class="linecolnote_private nowrap minwidth100"><?php $coldisplay++; ?>
				<textarea id="note_private_elt" name="note_private_elt" rows="3" style="margin-top: 5px; width: 98%" class="flat"></textarea>
			</td>
			<td>
				<input type="submit" class="button" value="<?php echo $langs->Trans("Update"); ?>" name="setnote_elt" id="setnote_elt">
				<input type="submit" class="button" value="<?php echo $langs->Trans("Cancel"); ?>" name="" id="">
			</td>
		</tr>
		</table>
		</form>
		</td>

		<script type="text/javascript">
		/* JQuery for product free or predefined select */
		jQuery(document).ready(function() {
		jQuery("#note_public_elt").val(<?php echo '"'.$line->note_public.'"';?>);
		jQuery("#note_private_elt").val(<?php echo '"'.$line->note_private.'"';?>);
		});
		</script>


	<?php } else { ?>
	<td align="right" class="linecolnote nowrap"><?php $coldisplay+=2; ?>
		<?php
		if($object->statut==TourneeGeneric::STATUS_VALIDATED && !empty($object_rights->note)){
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit_note_elt&id='.$parent->id.'&lineid='.$line->id.'#row-'.$line->id.'">';
			print img_edit($langs->trans($val['edit note']), 1);
			print '</a>';
		}
	 ?>
		<table class="noborderbottom">
			<tr><td colspan="2"><?php
			// Catégories
			if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){
					$langs->load('categories');

					print '<div id="view_tag_line_'.$line->id.'">';
					//print '<tr><td>' .
					print $langs->trans($line->nomelement . "CategoriesShort");
					// . '</td>';
					//print '<td>';
					print $form->showCategories($line->id, $line->element, 1);
					//print "</td></tr>";
					print '</div>';
					print '<div id="edit_tag_line_'.$line->id.'" style="display:none;">';
					//print '<tr class=""><td>' . fieldLabel($line->nomelement . 'CategoriesShort', 'custcats') . '</td>';
					//print '<td colspan="3">';
					$cate_arbo = $form->select_all_categories($line->element, null, null, null, null, 1);
					$c = new Categorie($db);
					$cats = $c->containing($line->id, $line->element);
					$arrayselected=array();
					foreach ($cats as $cat) {
						$arrayselected[] = $cat->id;
					}
					print $form->multiselectarray('cats_line'.$line->id, $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
					print '</div>';
				}?>
			</td></tr>
			<tr>
				<td align="right" class="linecolnote_public nowrap"><?php echo $line->note_public; ?></td>
				<td align="right" class="linecolnote_private nowrap"><?php echo $line->note_private; ?></td>
			</tr>
		</table>
	</td>
<?php } ?>

	<td align="right" class="linecoladresselivraison nowrap"><?php $coldisplay++; ?>
		<?php echo $line->getBannerAddresseLivraison('bannerAdresseLivraison_'.$line->id); ?>
	</td>


	<?php if( empty($conf->global->TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE)){ ?>
		<td align="right" class="linecolcontact nowrap"><?php $coldisplay++; ?>
			<style type="text/css">

			</style>
			<table class="noborderbottom">
			<?php
				$liste=array();
				if( count($line->lines) >0 ){
					foreach($line->lines as $contactline){
						$liste[]=$contactline->fk_socpeople;
						print '<tr class="contactlineid" id="contactlineid_'.$contactline->id.'"><td>';
						print $contactline->getBannerContact();
						print '</td><td>';
						if($this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){
							print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deletecontact&amp;contactid=' . $contactline->id . '">';
							print img_delete();
							print '</a>';
						}
						print '</td></tr>';
					}
				}

				if ($this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){ ?>
					<tr>
						<td>
							<?php $ret=$form->select_contacts($line->fk_soc, '', 'addcontactid_'.$line->id, 1, $liste, '',0,'', 0,0,array(), false,'',''); ?>
						</td><?php if($ret-count($liste)>0){ ?>
						<td>
							<input type="submit" class="button" value="<?php echo $langs->Trans("AjouterContact"); ?>" name="addcontact_<?php echo $line->id; ?>" id="addcontact_<?php echo $line->id; ?>" <?php  echo (($ret-count($liste)<=0)?'disabled':'')?>>
						</td><?php } ?>
					</tr>
				<?php
				}
			?>
			</table>
		</td>
	<?php } ?>

	<?php if ($this->statut == 0  && ($object_rights->ecrire) && $action != 'selectlines' ) { ?>
	<td class="linecoldelete_edit" align="center"><?php $coldisplay++; ?>

			<table class="noborderbottom">
			<tr><td class="linecoledit" align="center"><?php // $coldisplay++; ?>
				<?php if (($line->info_bits & 2) == 2 || ! empty($disableedit)) { ?>
				<?php } else { ?>
				<a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$this->id.'&amp;action=editline&amp;lineid='.$line->id.'#line_'.$line->id; ?>">
				<?php echo img_edit(); ?>
				</a>
				<?php } ?>
			</td></tr>

			<tr><td class="linecoldelete" align="center"><?php // $coldisplay++; ?>
				<?php
				if (($line->fk_prev_id == null ) && empty($disableremove)) { //La suppression n'est autorisée que si il n'y a pas de ligne dans une précédente situation
					print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deleteline&amp;lineid=' . $line->id . '">';
					print img_delete();
					print '</a>';
				}
				?>
			</td></tr>
		</table>
	</td>

<?php } else { ?>
	<td colspan="2"><?php $coldisplay=$coldisplay+3; ?></td>
<?php } ?>
	<?php  if($action == 'selectlines'){ ?>
	<td class="linecolcheck" align="center"><input type="checkbox" class="linecheckbox" name="line_checkbox[<?php echo $i+1; ?>]" value="<?php echo $line->id; ?>" ></td>
	<?php } ?>

</tr>

<?php
//Line extrafield
if (!empty($extrafieldsline))
{
	print $line->showOptionals($extrafieldsline, 'view', array('style'=>'class="drag drop oddeven"','colspan'=>$coldisplay), '', '', empty($conf->global->MAIN_EXTRAFIELDS_IN_ONE_TD)?0:1);
}
?>

<!-- END PHP TEMPLATE tourneetline_view.tpl.php -->
