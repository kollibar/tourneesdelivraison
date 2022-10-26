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
<tr id="row-<?php echo $line->id;?>" class="drag drop oddeven tournee-row <?php echo (($ligneVide || ( $reload && $line->id != $selected ))?'tournee-row-reload':'');?>" <?php echo $domData;?>  data="<?php echo $paramsLienLigne;?>" >
	<?php if( $ligneVide == false ) { ?>

	<td class="linecolselect" align="center"><?php $coldisplay++; ?>
		<input type="checkbox" id="line-select-row-<?php echo $line->id;?>" class="line-select">
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

				<?php
					if( $afficheTags ){
						if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire)){


							print '<div id="view_tag_soc_'.$line->fk_soc.'">';
							//print '<tr><td>' .
							// print $langs->trans($line->nomelement . "CategoriesShort");
							// . '</td>';
							//print '<td>';
							if( $action != 'edit_tag_tiers' || $line->id != $selected || empty($user->rights->societe->contact->creer)) {
								print '<div>';
								print $form->showCategoriesExcluding($line->fk_soc, (($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?'customer':'supplier'), (($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?$categoriesClientExclure:$categoriesFournisseurExclure),1, 1);
								print '<span style="width:20px;"></span>';
								
								if( !empty($conf->global->TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG) ){
									print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit_tag_tiers&id='.$this->id.$paramsLienLigne.'&lineid='.$line->id.'#row-'.$line->id.'" class="ajaxable">';
									print img_edit($langs->trans($val['edit note']), 1);
									print '</a>';
								}

								print '</div>';
							} else {
								$langs->load('categories');?>
								<form class="edit_tag_tiers" name="edit_tag_tiers-<?php echo $line->id?>" id="edit_tag_tiers-<?php echo $line->id?>" action="<?php echo $_SERVER["PHP_SELF"] . '?id=' . $this->id . '#row-'.GETPOST('lineid'); ?>" method="POST">
								<input type="hidden" name="token" value="<?php echo $_SESSION ['newtoken']; ?>">
								<input type="hidden" name="action" value="settag_tiers">
								<input type="hidden" name="mode" value="">
								<input type="hidden" name="var" value="<?php echo $paramsLienLigne_var;?>">
								<input type="hidden" name="i" value="<?php echo $paramsLienLigne_i;?>">
								<input type="hidden" name="num" value="<?php echo $paramsLienLigne_num;?>">
								<input type="hidden" name="lineid" value="<?php echo $line->id; ?>" >
								<input type="hidden" name="id" value="<?php echo $this->id; ?>">

								<?php
								$cate_arbo = suppressionCategories($form->select_all_categories(($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?'customer':'supplier', null, null, null, null, 1),($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?$categoriesClientExclure:$categoriesFournisseurExclure);

								$c = new Categorie($db);
								$cats = $c->containing($line->fk_soc, ($line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT)?'customer':'supplier');
								$arrayselected=array();
								foreach ($cats as $cat) {
									$arrayselected[] = $cat->id;
								}
								print $form->multiselectarray('cats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
								//print "</td></tr>";
								?>
								<input type="submit" class="button" value="<?php echo $langs->Trans("Update"); ?>" name="settag_tiers" id="settag_tiers">
								<input type="submit" class="button" value="<?php echo $langs->Trans("Cancel"); ?>" name="" id="">
								</form>
								<?php
							}

							//print "</td></tr>";
							print '</div>';
						}
					}
				?>

				<?php if( !empty($conf->global->TOURNEESDELIVRAISON_AFFICHAGE_CONTACT_INTEGRE)){


						print '<table class="noborderbottom">';

						$liste=array();
						if( count($line->lines) >0 || $this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){
							print '<tr><td style="font-weight:bold;">'.$langs->trans('Contact').':';
							if( ( $this->statut != 0 && $action != 'modifie_contact')){
								print '<a href="'.$_SERVER['PHP_SELF'].'?action=modifie_contact&id='.$this->id.$paramsLienLigne.'&lineid='.$line->id.'#row-'.$line->id.'" class="ajaxable">';
								print img_edit($langs->trans($val['edit note']), 1);
								print '</a>';
							}
							print '</td></tr>';
						}
						if( count($line->lines) >0 ){
							$contactlineid=GETPOSTINT('contactlineid');
							foreach($line->lines as $contactline){
								$liste[]=$contactline->fk_socpeople;
								print '<tr class="contactlineid" id="contactlineid_'.$contactline->id.'"><td>';
								print $contactline->getBannerContact();
								if( $afficheTags ){
									if( $action != 'edit_tag_contact' || $line->id != $selected || $contactline->rowid != $contactlineid || empty($user->rights->societe->creer)) {
										print '<div>';
										print $form->showCategoriesExcluding($contactline->fk_socpeople, 'contact', $categoriesContactExclure,1, 1);

										print '<span style="width:20px;"></span>';
										if( !empty($conf->global->TOURNEESDELIVRAISON_AUTORISER_EDITION_TAG) ){
											print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit_tag_contact&id='.$this->id.$paramsLienLigne.'&contactlineid='.$contactline->rowid.'&lineid='.$line->id.'#row-'.$line->id.'" class="ajaxable">';
											print img_edit($langs->trans($val['edit note']), 1);
											print '</a>';
										}
										print '</div>';
									} else {
										$langs->load('categories');
										print '<form class="edit_tag_contact" name="edit_tag_contact-'.$line->id.'" id="edit_tag_contact-'.$line->id.'" action="'.$_SERVER["PHP_SELF"] . '?id=' . $this->id . '#row-'.GETPOST('lineid').'" method="POST">';
										print '<input type="hidden" name="token" value="'.$_SESSION ['newtoken'].'">';
										print '<input type="hidden" name="action" value="settag_contact">';
										print '<input type="hidden" name="mode" value="">';
										print '<input type="hidden" name="var" value="'.$paramsLienLigne_var.'">';
										print '<input type="hidden" name="i" value="'.$paramsLienLigne_i.'">';
										print '<input type="hidden" name="num" value="'.$paramsLienLigne_num.'">';
										print '<input type="hidden" name="lineid" value="'.$line->id.'" >';
										print '<input type="hidden" name="contactlineid" value="'.$contactline->rowid.'" >';
										print '<input type="hidden" name="id" value="'.$this->id.'">';

										$cate_arbo = suppressionCategories($form->select_all_categories('contact', null, null, null, null, 1),$categoriesContactExclure);

										$c = new Categorie($db);
										$cats = $c->containing($contactline->fk_socpeople, 'contact');
										$arrayselected=array();
										foreach ($cats as $cat) {
											$arrayselected[] = $cat->id;
										}
										print $form->multiselectarray('cats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
										//print "</td></tr>";

										print '<input type="submit" class="button" value="'.$langs->Trans("Update").'" name="settag_contact" id="settag_contact">';
										print '<input type="submit" class="button" value="'.$langs->Trans("Cancel").'" name="" id="">';
										print '</form>';
									}
								}
								print '</td>';

								print '<td>';
								if(( $this->statut == 0 || $action == 'modifie_contact')  && $object_rights->ecrire && $action != 'selectlines'){
									print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deletecontact&amp;contactid=' . $contactline->id . $paramsLienLigne . '" class="ajaxable">';
									print img_delete();
									print '</a>';
								}
								print '</td></tr>';
							}
						}

						if (( $this->statut == 0 || $action == 'modifie_contact') && $object_rights->ecrire && $action != 'selectlines'){
							print '<tr><td>';

							if( $action == 'modifie_contact' ){
								print '<form class="addcontact" name="addcontact-'.$line->id.'" id="addcontact-'.$line->id.'" action="'.$_SERVER["PHP_SELF"] . '?id=' . $this->id . '#row-'.GETPOST('lineid').'" method="POST">';
								print '<input type="hidden" name="token" value="'.$_SESSION ['newtoken'].'">';
								print '<input type="hidden" name="action" value="addcontact">';
								print '<input type="hidden" name="mode" value="">';
								print '<input type="hidden" name="var" value="'.$paramsLienLigne_var.'">';
								print '<input type="hidden" name="i" value="'.$paramsLienLigne_i.'">';
								print '<input type="hidden" name="num" value="'.$paramsLienLigne_num.'">';
								print '<input type="hidden" name="lineid" value="'.$line->id.'" >';
								print '<input type="hidden" name="id" value="'.$this->id.'">';
							}

							$ret=$form->select_contacts($line->fk_soc, '', 'addcontactid_'.$line->id, 1, $liste, '',0,'', 0,0,array(), false,'','');
							// print '</td>';
							if( $ret - count($liste) > 0 ){
								// print '<td>';


								print '<input type="checkbox" name="addcontactid_'.$line->id.'_noemail" id="addcontactid_'.$line->id.'_noemail" value="addcontactid_'.$line->id.'_noemail"' . ((!empty($this->no_email))?'checked':'') . ' >';
								print '<label for="addcontactid_'.$line->id.'_noemail">' . $langs->trans('noEmailAuto'). '</label>';

								if (! empty($conf->global->TOURNEESDELIVRAISON_SMS)){
									print '<input type="checkbox" name="addcontactid_'.$line->id.'_sms" id="addcontactid_'.$line->id.'_sms" value="addcontactid_'.$line->id.'_sms"' . ((!empty($this->no_email))?'checked':'') . 'disabled >';
									print '<label for="addcontactid_'.$line->id.'_sms">' . $langs->trans('sms'). '</label>';
								}
							//	print '</td><td>';

								if( $action != 'modifie_contact' ){
									print '<input type="submit" class="button" value="'.$langs->Trans("AjouterContact").'" name="addcontact_'.$line->id.'" id="addcontact_'.$line->id.'"'.(($ret-count($liste)<=0)?'disabled':'').'>';
								} else {
									print '<input type="submit" class="button" value="'.$langs->Trans("Update").'" name="addcontact" id="addcontact">';
									print '<input type="submit" class="button" value="'.$langs->Trans("Cancel").'" name="" id="">';

								}
								print '</form>';
							}
							print '</td></tr>';

						}

					print '</table>';

				} ?>










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

<?php if ($this->statut == TourneeGeneric::STATUS_VALIDATED && !empty($conf->facture->enabled) && $user->rights->facture->lire && !empty($conf->global->TOURNEESDELIVRAISON_AFFICHER_INFO_FACTURES)) {
	print '<td align="right" class="linecolfacture nowrap" >';
	// print '<div style="transform: scale(0.9,0.9);">';

	$coldisplay++;

	if( $afficheTags ){
		$boxstat = '';

		$client = $line->getSoc();

		$tmp = $client->getOutstandingBills('customer', 0); // toutes factures
		$outstandingOpened = $tmp['opened'];

		$montantFactureNonDelivre=0;
		$factureNonDelivre=array();
		$nbFactureNonDelivre=0;

		$listeFactureImpayees=$tmp["refsopened"];

		// recherche de facture non délivrées
		foreach ($listeFactureImpayees as $facture_id => $facture_nom) {
			foreach ($line->lines_cmde as $lcmde) {
				foreach ($lcmde->lines as $lelt) {
					if($lelt->type_element == 'facture'){
						if( $lelt->fk_elt == $facture_id ){
							$facture=$lelt->loadElt();
							$montantFactureNonDelivre += floatval($facture->total_ttc);
							$factureNonDelivre[]=$facture_id;
							$nbFactureNonDelivre++;
						}
					}
				}
			}
		}

		$outstandingOpened -= $montantFactureNonDelivre;

		// Box outstanding bill
		$warn = '';
		if ($client->outstanding_limit != '' && $client->outstanding_limit < $outstandingOpened) {
			$warn = ' '.img_warning($langs->trans("OutstandingBillReached"));
		}
		$text = $langs->trans("CurrentOutstandingBill");
		$link = DOL_URL_ROOT.'/compta/recap-compta.php?socid='.$client->id;
		$icon = 'bill';
		if ($link) $boxstat .= '<a href="'.$link.'" class="boxstatsindicator thumbstat nobold nounderline">';
		$boxstat .= '<div class="boxstats" title="'.dol_escape_htmltag($text).'">';
		$boxstat .= '<span class="boxstatstext">'.img_object("", $icon).' <span>'.$text.'</span></span><br>';
		$boxstat .= '<span class="boxstatsindicator'.($outstandingOpened > 0 ? ' amountremaintopay' : '').'">'.price($outstandingOpened, 1, $langs, 1, -1, -1, $conf->currency).$warn.'</span>';
		$boxstat .= '</div>';
		if ($link) $boxstat .= '</a>';

		$tmp = $client->getOutstandingBills('customer', 1);	// en retard
		$outstandingOpenedLate = $tmp['opened'];

		if ($outstandingOpened != $outstandingOpenedLate && !empty($outstandingOpenedLate)) {
			$warn = '';
			if ($client->outstanding_limit != '' && $client->outstanding_limit < $outstandingOpenedLate) {
				$warn = ' '.img_warning($langs->trans("OutstandingBillReached"));
			}
			$text = $langs->trans("CurrentOutstandingBillLate");
			$link = DOL_URL_ROOT.'/compta/recap-compta.php?socid='.$client->id;
			$icon = 'bill';
			if ($link) $boxstat .= '<a href="'.$link.'" class="boxstatsindicator thumbstat nobold nounderline">';
			$boxstat .= '<div class="boxstats" title="'.dol_escape_htmltag($text).'">';
			$boxstat .= '<span class="boxstatstext">'.img_object("", $icon).' <span>'.$text.'</span></span><br>';
			$boxstat .= '<span class="boxstatsindicator'.($outstandingOpenedLate > 0 ? ' amountremaintopay' : '').'">'.price($outstandingOpenedLate, 1, $langs, 1, -1, -1, $conf->currency).$warn.'</span>';
			$boxstat .= '</div>';
			if ($link) $boxstat .= '</a>';


		}
		print $boxstat;


		$sql = 'SELECT f.rowid as facid, f.ref, f.type';
		$sql .= ', f.total_ht as total_ht';
		$sql .= ', f.total_tva as total_tva';
		$sql .= ', f.total_ttc';
		$sql .= ', f.datef as df, f.datec as dc, f.paye as paye, f.fk_statut as statut';
		$sql .= ', f.date_lim_reglement';
		$sql .= ', s.nom, s.rowid as socid';
		$sql .= ', SUM(pf.amount) as am';
		$sql .= " FROM ".MAIN_DB_PREFIX."societe as s,".MAIN_DB_PREFIX."facture as f";
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiement_facture as pf ON f.rowid=pf.fk_facture';
		$sql .= " WHERE f.fk_soc = s.rowid AND s.rowid = ".$client->id;
		$sql .= " AND f.paye = 0";
		$sql .= " AND f.entity IN (" . getEntity('invoice') . ")";
		$sql .= ' GROUP BY f.rowid, f.ref, f.type, f.total_ht, f.total_tva, f.total_ttc,';
		$sql .= ' f.datef, f.datec, f.paye, f.fk_statut,';
		$sql .= ' s.nom, s.rowid';
		$sql .= " ORDER BY f.datef DESC, f.datec DESC";

		$resql = $db->query($sql);



		if($resql){

			$facturestatic = new Facture($db);

			$num = $db->num_rows($resql);

			if( $num - $nbFactureNonDelivre > 0 ){
				print '<div class="div-table-responsive-no-min">';
				print '<table class="noborder centpercent lastrecordtable">';

				print '<tr class="liste_titre">';
				print '<td colspan="5">';
				print '<table width="100%" class="nobordernopadding">';
				print '<tr>';
				print '<td>'.$langs->trans("FacturesImpayees").'</td><td class="right"></td>';
				print '<td width="20px" class="right"><a href="'.DOL_URL_ROOT.'/compta/facture/stats/index.php?socid='.$client->id.'">'.img_picto($langs->trans("Statistics"), 'stats').'</a></td>';
				print '</tr>';
				print '</table>';
				print '</td></tr>';
			}


			$i = 0;
			while ($i < $num - $nbFactureNonDelivre) {

				$objp = $db->fetch_object($resql);

				if( in_array($objp->facid, $factureNonDelivre) )continue;

				$facturestatic->id = $objp->facid;
				$facturestatic->ref = $objp->ref;
				$facturestatic->type = $objp->type;
				$facturestatic->total_ht = $objp->total_ht;
				$facturestatic->total_tva = $objp->total_tva;
				$facturestatic->total_ttc = $objp->total_ttc;
				$facturestatic->paye = $objp->paye;
				$facturestatic->date_lim_reglement = $objp->date_lim_reglement;


				print '<tr class="oddeven">';
				print '<td class="nowrap">';
				print $facturestatic->getNomUrl(1);
				print '</td>';

	/*
				if ($objp->date_lim_reglement > 0) {
					print '<td class="right" width="80px">'.dol_print_date($db->jdate($objp->date_lim_reglement), 'day').'</td>';
				} else {
					print '<td class="right"><b>!!!</b></td>';
				}*/

				print '<td class="right" style="min-width: 60px">';
				print price($objp->total_ttc, 1, $langs, 1, -1, -1, $conf->currency);
				print '</td>';

	/*
				var_dump($objp->date_lim_reglement);

				mktime(0,0,0, substr($objp->date_lim_reglement, 5,2), substr($objp->date_lim_reglement, 8,2), substr($objp->date_lim_reglement, 0,4));
				// 2022-11-20
				substr($objp->date_lim_reglement, 5,2);
				substr($objp->date_lim_reglement, 8,2);
				substr($objp->date_lim_reglement, 0,4);*/

				if( mktime(0,0,0, substr($objp->date_lim_reglement, 5,2), substr($objp->date_lim_reglement, 8,2), substr($objp->date_lim_reglement, 0,4)) < time()) $retard=true;
				else $retard=false;

				print '<td class="nowrap right" style="min-width: 60px"><span class="badge  badge-status'.($retard?8:1).' badge-status" title="'.$langs->trans("DateDue").': '.dol_print_date($objp->date_lim_reglement, 'day').'">'.($retard?$langs->trans("Late"):$langs->trans("Impayee")).'</span></td>';
				// $langs->trans("CurrentOutstandingBill");
				print "</tr>\n";

				$i++;
			}

			$db->free($resql);

			if( $num - $nbFactureNonDelivre > 0) {
				print "</table>";
				print '</div>';
			}
		} else {
			dol_print_error($db);
		}
	}

	print '</td>';

} ?>


	<?php

	if( $line->element=='tourneeunique_lines' && $parent->statut != STATUS_DRAFT){ ?>
	  <td class="linecolcmde">
			<?php if( $line->type==TourneeGeneric_lines::TYPE_THIRDPARTY_CLIENT) { ?>

			<table class="noborderbottom">
				<?php foreach ($line->lines_cmde as $lcmde) {
					$cmde=$lcmde->loadElt();
					if( $lcmde->statut!=TourneeUnique_lines_cmde::DATE_OK
						&& $lcmde->statut!=TourneeUnique_lines_cmde::DATE_NON_OK
						&& $cmde->statut==Commande::STATUS_CLOSED) continue;
					// $cmde=new Commande($this->db);
					// $cmde->fetch($lcmde->fk_commande);
					$numshipping = $cmde->nb_expedition();
					$numinvoice=0;
					$nb_exp=0;
					foreach ($lcmde->lines as $lelt) {
						if($lelt->type_element == 'shipping'){
							$nb_exp+=1;
						}
						if($lelt->type_element == 'facture'){
							$numinvoice+=1;
						}
					}
					?>
				<tr><td>
					<?php
					$morehtml=$cmde->getNomUrl();
					echo $lcmde->getMenuStatut(false,$morehtml); ?>
				</td>
				<td>
					<?php
					if( $lcmde->statut != TourneeUnique_lines_cmde::DATE_OK && $lcmde->statut != TourneeUnique_lines_cmde::DATE_NON_OK) continue;
					if( $nb_exp != 0 ){
						echo '<table class="noborderbottom">';
						foreach ($lcmde->lines as $lelt) {
							if($lelt->type_element == 'shipping'){
								//$elt=$lelt->loadElt();
								echo '<tr><td>';
								echo $lelt->getMenuStatut();
								echo '</td></tr>';
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
								print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $cmde->id . '&amp;action=classifybilled'.$paramsLienLigne.'" class="ajaxable">' . $langs->trans("ClassifyBilled") . '</a></div>';
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
							print '<a href="'.$_SERVER['PHP_SELF'].'?action=unsetnocmde_elt&id='.$parent->id.'&lineid='.$line->id.$paramsLienLigne.'" class="ajaxable">';
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER['PHP_SELF'].'?action=setnocmde_elt&id='.$parent->id.'&lineid='.$line->id.$paramsLienLigne.'" class="ajaxable">';
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


	<?php if( $this->statut==TourneeGeneric::STATUS_VALIDATED && $action=='edit_note_elt' && $line->id==$selected  && !empty($object_rights->note)){ ?>
		<td align="right" class="linecolnote nowrap"><?php $coldisplay++; ?>
			<form class="edit_note_elt" name="edit_note_elt-<?php echo $line->id?>" id="edit_note_elt-<?php echo $line->id?>" action="<?php echo $_SERVER["PHP_SELF"] . '?id=' . $this->id . '#row-'.GETPOST('lineid'); ?>" method="POST">
			<input type="hidden" name="token" value="<?php echo $_SESSION ['newtoken']; ?>">
			<input type="hidden" name="action" value="setnote_elt">
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="var" value="<?php echo $paramsLienLigne_var;?>">
			<input type="hidden" name="i" value="<?php echo $paramsLienLigne_i;?>">
			<input type="hidden" name="num" value="<?php echo $paramsLienLigne_num;?>">
			<input type="hidden" name="lineid" value="<?php echo $line->id; ?>" >
			<input type="hidden" name="id" value="<?php echo $this->id; ?>">
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
					print $form->multiselectarray('cats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
					//print "</td></tr>";
				}
				?>
			</td></tr>
			<tr>
			<td align="right" class="linecolnote_public nowrap minwidth100">
				<textarea id="note_public_elt" name="note_public_elt" rows="3" style="margin-top: 5px; width: 98%" class="flat"><?php echo $line->note_public;?></textarea>
			</td>
			<td align="right" class="linecolnote_private nowrap minwidth100"><?php $coldisplay++; ?>
				<textarea id="note_private_elt" name="note_private_elt" rows="3" style="margin-top: 5px; width: 98%" class="flat"><?php echo $line->note_private;?></textarea>
			</td>
			<td>
				<input type="submit" class="button" value="<?php echo $langs->Trans("Update"); ?>" name="setnote_elt" id="setnote_elt">
				<input type="submit" class="button" value="<?php echo $langs->Trans("Cancel"); ?>" name="" id="">
			</td>
		</tr>
		</table>
		</form>
		</td>


	<?php } else { ?>
	<td align="right" class="linecolnote nowrap"><?php $coldisplay+=2; ?>
		<?php
		if($this->statut==TourneeGeneric::STATUS_VALIDATED && !empty($object_rights->note)){
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit_note_elt&id='.$parent->id.$paramsLienLigne.'&lineid='.$line->id.'#row-'.$line->id.'" class="ajaxable">';
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

					if( $line->element == 'tourneeunique_lines' && ( $line->aucune_cmde == 1 || $nb[TourneeUnique_lines_cmde::DATE_NON_OK] != 0 || $nb[TourneeUnique_lines_cmde::DATE_OK] != 0)){
						print $form->showCategoriesExcluding($line->id, $line->element, $categoriesLineCmdeExclure,1,1);
					} else {
						print $form->showCategories($line->id, $line->element, 1, 1);
					}

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
				<td align="right" class="linecolnote_public nowrap"><?php echo (( $line->element == 'tourneeunique_lines' && ($line->aucune_cmde == 1 || $nb[TourneeUnique_lines_cmde::DATE_NON_OK]!=0 || $nb[TourneeUnique_lines_cmde::DATE_OK]!=0))?preg_replace('/(\[.*?\])/m', '', $line->note_public):$line->note_public); ?></td>
				<td align="right" class="linecolnote_private nowrap"><?php echo (( $line->element == 'tourneeunique_lines' && ($line->aucune_cmde == 1 || $nb[TourneeUnique_lines_cmde::DATE_NON_OK]!=0 || $nb[TourneeUnique_lines_cmde::DATE_OK]!=0))?preg_replace('/(\[.*?\])/m', '', $line->note_private):$line->note_private); ?></td>
			</tr>
		</table>
	</td>
<?php } ?>



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
						print $form->showCategoriesExcluding($contactline->fk_socpeople, 'contact', $categoriesContactExclure,1, 1);
						print '</td><td>';
						if($this->statut == 0 && $object_rights->ecrire && $action != 'selectlines'){
							print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deletecontact&amp;contactid=' . $contactline->id .$paramsLienLigne. '" class="ajaxable">';
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
				<a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$this->id.$paramsLienLigne.'&amp;action=editline&amp;lineid='.$line->id.'#line_'.$line->id; ?>">
				<?php echo img_edit(); ?>
				</a>
				<?php } ?>
			</td></tr>

			<tr><td class="linecoldelete" align="center"><?php // $coldisplay++; ?>
				<?php
				if (($line->fk_prev_id == null ) && empty($disableremove)) { //La suppression n'est autorisée que si il n'y a pas de ligne dans une précédente situation
					print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $this->id . '&amp;action=ask_deleteline&amp;lineid=' . $line->id . $paramsLienLigne . '" class="ajaxable">';
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

<?php } ?>
</tr><!-- row-<?php echo $line->id ?> -->

<?php
//Line extrafield
if (!empty($extrafieldsline))
{
	print $line->showOptionals($extrafieldsline, 'view', array('style'=>'class="drag drop oddeven"','colspan'=>$coldisplay), '', '', empty($conf->global->MAIN_EXTRAFIELDS_IN_ONE_TD)?0:1);
}
?>

<!-- END PHP TEMPLATE tourneetline_view.tpl.php -->
