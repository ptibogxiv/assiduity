<?php
/* Copyright (C) 2001-2004	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003	Jean-Louis Bergamo		<jlb@j1b.org>
 * Copyright (C) 2004-2014	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro		<aspangaro.dolibarr@gmail.com>
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
 */

/**
 *       \file       htdocs/adherents/subscription.php
 *       \ingroup    member
 *       \brief      Onglet d'ajout, edition, suppression des adhesions d'un adherent
 */

// Load Dolibarr environment
$res=@include("../main.inc.php");                                // For root directory
if (! $res) $res=@include("../../main.inc.php");  
dol_include_once('/assiduity/lib/assiduity.lib.php');
dol_include_once('/assiduity/class/assiduity.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Load translation files required by the page
$langs->loadLangs(array("companies","bills","members","users","other","assiduity@assiduity"));

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$rowid=GETPOST('rowid','int');
$typeid=GETPOST('typeid','int');

// Security check
$result=restrictedArea($user,'adherent',$rowid,'','cotisation');

$object = new Adherent($db);
$extrafields = new ExtraFields($db);
$adht = new AdherentType($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

$errmsg='';
$errmsgs=array();

$defaultdelay=1;
$defaultdelayunit='y';

if ($rowid)
{
    // Load member
    $result = $object->fetch($rowid);

    // Define variables to know what current user can do on users
    $canadduser=($user->admin || $user->rights->user->user->creer);
    // Define variables to know what current user can do on properties of user linked to edited member
    if ($object->user_id)
    {
        // $user est le user qui edite, $object->user_id est l'id de l'utilisateur lies au membre edite
        $caneditfielduser=( (($user->id == $object->user_id) && $user->rights->user->self->creer)
        || (($user->id != $object->user_id) && $user->rights->user->user->creer) );
        $caneditpassworduser=( (($user->id == $object->user_id) && $user->rights->user->self->password)
        || (($user->id != $object->user_id) && $user->rights->user->user->password) );
    }
}

// Define variables to know what current user can do on members
$canaddmember=$user->rights->adherent->creer;
// Define variables to know what current user can do on properties of a member
if ($rowid)
{
    $caneditfieldmember=$user->rights->adherent->creer;
}

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('membercard','globalcard'));

// PDF
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

/*
 * 	Actions
 */


/*
 * View
 */

$form = new Form($db);

$now=dol_now();

$title=$langs->trans("Member") . " - " . $langs->trans("AssiduityMenuSess");
$helpurl="EN:Module_Foundations|FR:Module_Adh&eacute;rents|ES:M&oacute;dulo_Miembros";
llxHeader("",$title,$helpurl);

if ($rowid > 0)
{
    $res=$object->fetch($rowid);
    if ($res < 0) { dol_print_error($db,$object->error); exit; }

    $adht->fetch($object->typeid);

    $head = assiduity_prepare_head($object);

    $rowspan=10;
    if (empty($conf->global->ADHERENT_LOGIN_NOT_REQUIRED)) $rowspan++;
    if (! empty($conf->societe->enabled)) $rowspan++;

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="rowid" value="'.$object->id.'">';

    dol_fiche_head($head, 'assiduity', $langs->trans("Member"), 0, 'user');

    $linkback = '<a href="'.DOL_URL_ROOT.'/adherents/list.php">'.$langs->trans("BackToList").'</a>';
    
    dol_banner_tab($object, 'rowid', $linkback);
    
    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border" width="100%">';

	// Login
	if (empty($conf->global->ADHERENT_LOGIN_NOT_REQUIRED))
	{
		print '<tr><td class="titlefield">'.$langs->trans("Login").' / '.$langs->trans("Id").'</td><td class="valeur">'.$object->login.'&nbsp;</td></tr>';
	}

	// Type
	print '<tr><td class="titlefield">'.$langs->trans("Type").'</td><td class="valeur">'.$adht->getNomUrl(1)."</td></tr>\n";

	// Morphy
	print '<tr><td>'.$langs->trans("Nature").'</td><td class="valeur" >'.$object->getmorphylib().'</td>';
	print '</tr>';

	// Company
	print '<tr><td>'.$langs->trans("Company").'</td><td class="valeur">'.$object->societe.'</td></tr>';

	// Civility
	print '<tr><td>'.$langs->trans("UserTitle").'</td><td class="valeur">'.$object->getCivilityLabel().'&nbsp;</td>';
	print '</tr>';

	// Password
	if (empty($conf->global->ADHERENT_LOGIN_NOT_REQUIRED))
	{
		print '<tr><td>'.$langs->trans("Password").'</td><td>'.preg_replace('/./i','*',$object->pass);
		if ((! empty($object->pass) || ! empty($object->pass_crypted)) && empty($object->user_id))
		{
		    $langs->load("errors");
		    $htmltext=$langs->trans("WarningPasswordSetWithNoAccount");
		    print ' '.$form->textwithpicto('', $htmltext,1,'warning');
		}
		print '</td></tr>';
	}

    print '</table>';
    
    print '</div>';
    print '<div class="fichehalfright"><div class="ficheaddleft">';
   
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border tableforfield" width="100%">';
	
	// Birthday
	print '<tr><td class="titlefield">'.$langs->trans("Birthday").'</td><td class="valeur">'.dol_print_date($object->birth,'day').'</td></tr>';

	// Public
	print '<tr><td>'.$langs->trans("Public").'</td><td class="valeur">'.yn($object->public).'</td></tr>';

	// Categories
	if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire))
	{
		print '<tr><td>' . $langs->trans("Categories") . '</td>';
		print '<td colspan="2">';
		print $form->showCategories($object->id, 'member', 1);
		print '</td></tr>';
	}

	// Other attributes
	$parameters=array('colspan'=>2);
	$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
	  print $hookmanager->resPrint;
  if (empty($reshook) && ! empty($extrafields->attribute_label))
	{
		print $object->showOptionals($extrafields, 'view', $parameters);
	}

	// Date end subscription
	print '<tr><td>'.$langs->trans("SubscriptionEndDate").'</td><td class="valeur">';
	if ($object->datefin)
	{
	    print dol_print_date($object->datefin,'day');
	    if ($object->hasDelay()) {
	        print " ".img_warning($langs->trans("Late"));
	    }
	}
	else
	{
	    if (! $adht->subscription)
	    {
	        print $langs->trans("SubscriptionNotRecorded");
	        if ($object->statut > 0) print " ".img_warning($langs->trans("Late")); // Affiche picto retard uniquement si non brouillon et non resilie
	    }
	    else
	    {
	        print $langs->trans("SubscriptionNotReceived");
	        if ($object->statut > 0) print " ".img_warning($langs->trans("Late")); // Affiche picto retard uniquement si non brouillon et non resilie
	    }
	}
	print '</td></tr>';
	
	// Third party Dolibarr
	if (! empty($conf->societe->enabled))
	{
		print '<tr><td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans("LinkedToDolibarrThirdParty");
		print '</td>';
		if ($action != 'editthirdparty' && $user->rights->adherent->creer) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editthirdparty&amp;rowid='.$object->id.'">'.img_edit($langs->trans('SetLinkToThirdParty'),1).'</a></td>';
		print '</tr></table>';
		print '</td><td colspan="2" class="valeur">';
		if ($action == 'editthirdparty')
		{
			$htmlname='socid';
			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="form'.$htmlname.'">';
			print '<input type="hidden" name="rowid" value="'.$object->id.'">';
			print '<input type="hidden" name="action" value="set'.$htmlname.'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
			print '<tr><td>';
			print $form->select_company($object->fk_soc,'socid','',1);
			print '</td>';
			print '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
			print '</tr></table></form>';
		}
		else
		{
			if ($object->fk_soc)
			{
				$company=new Societe($db);
				$result=$company->fetch($object->fk_soc);
				print $company->getNomUrl(1);
			}
			else
			{
				print $langs->trans("NoThirdPartyAssociatedToMember");
			}
		}
		print '</td></tr>';
	}

	// Login Dolibarr
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans("LinkedToDolibarrUser");
	print '</td>';
	if ($action != 'editlogin' && $user->rights->adherent->creer)
	{
		print '<td align="right">';
		if ($user->rights->user->user->creer)
		{
			print '<a href="'.$_SERVER["PHP_SELF"].'?action=editlogin&amp;rowid='.$object->id.'">'.img_edit($langs->trans('SetLinkToUser'),1).'</a>';
		}
		print '</td>';
	}
	print '</tr></table>';
	print '</td><td colspan="2" class="valeur">';
	if ($action == 'editlogin')
	{
		$form->form_users($_SERVER['PHP_SELF'].'?rowid='.$object->id,$object->user_id,'userid','');
	}
	else
	{
		if ($object->user_id)
		{
			$form->form_users($_SERVER['PHP_SELF'].'?rowid='.$object->id,$object->user_id,'none');
		}
		else print $langs->trans("NoDolibarrAccess");
	}
	print '</td></tr>';

    print "</table>\n";

	print "</div></div></div>\n";
    print '<div style="clear:both"></div>';
    
    dol_fiche_end();

    print '</form>';


    /*
     * Hotbar
     */

    // Button to create a new subscription if member no draft neither resiliated
    if ($user->rights->adherent->cotisation->creer)
    {
        if ($action != 'addsubscription' && $action != 'create_thirdparty')
        {
            print '<div class="tabsAction">';

				// Delete
				if ($user->rights->adherent->supprimer)
				{
        if (7==4) {
 					print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("CheckIn")."</font></div>\n";
				 	print '<div class="inline-block divButAction"><a class="butActionDelete" href="card.php?rowid='.$object->id.'&action=checkout">'.$langs->trans("CheckOut")."</a></div>\n";         
       
        } else {
					print '<div class="inline-block divButAction"><a class="butAction" href="card.php?rowid='.$object->id.'&action=checkin">'.$langs->trans("CheckIn")."</a></div>\n";
				 	print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("CheckOut")."</font></div>\n";
        }  
        }
				else
				{
					print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("CheckIn")."</font></div>";
					print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("CheckOut")."</font></div>";				
        }

            print '</div>';
            print '<br>';
        }
    } 

    /*
     * List of asssiduitry
     */
    if ($action != 'addsubscription' && $action != 'create_thirdparty')
    {
        $sql = "SELECT p.rowid,p.entity,p.event_id,p.assiduity";
        $sql .= ",c.id,c.datep,c.datep2,c.label";        
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent_assiduity as p";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."actioncomm as c ON c.id = p.event_id";
        $sql.= " WHERE p.fk_object=".$object->id;
        $sql.= " AND p.entity IN (" . getEntity('assiduity') . ") ";
        $sql.= " ORDER BY c.datep DESC LIMIT 1,60";
        
        $result = $db->query($sql);
        if ($result)
        {
            $subscriptionstatic=new Subscription($db);

            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("AssiduityStatut").'</td>';
            print '<td align="center">'.$langs->trans("Event").'</td>';
            print '<td align="center">'.$langs->trans("DateStart").'</td>';
            print '<td align="center">'.$langs->trans("DateEnd").'</td>';
            print '<td align="right">'.$langs->trans("AssiduityStatutbis").'</td>';
            print "</tr>\n";

            $var=True;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);
                $var=!$var;
                print "<tr ".$bc[$var].">";
                $subscriptionstatic->ref=$objp->crowid;
                $subscriptionstatic->id=$objp->crowid;
 print '<td>';
 if ($objp->assiduity=='0')
 {print img_picto($langs->trans('AssiduityAbsent'),'statut8').' '.$langs->trans('AssiduityAbsent');} 
 elseif ($objp->assiduity=='1')
 {print img_picto($langs->trans('AssiduityPresent'),'statut4').' '.$langs->trans('AssiduityPresent');}
 elseif($objp->assiduity=='2')
 {print img_picto($langs->trans('AssiduityNC'),'statut0').' '.$langs->trans('AssiduityNC');}              
                
                print '</td>';
                print '<td align="left"><a href="'.DOL_URL_ROOT.'/custom/assiduity/list.php?id='.$objp->event_id.'">'.img_picto('', 'object_calendar').' '.dol_escape_htmltag($objp->label)."</a></td>";
                print '<td align="center">'.dol_print_date($db->jdate($objp->datep),'dayhour')."</td>\n";
                print '<td align="center">'.dol_print_date($db->jdate($objp->datep2),'dayhour')."</td>\n";
  $assiduity=new Assiduite($db);              
  $percent=$assiduity->fetch_ev_assiduity($objp->event_id);               
                
                print '<td align="right">'.$percent.'% </td>';
                print "</tr>";
                $i++;
            }
            print "</table>";
        }
        else
        {
            dol_print_error($db);
        }


    }

}
else
{
    $langs->load("errors");
    print $langs->trans("ErrorRecordNotFound");
}


llxFooter();

$db->close();