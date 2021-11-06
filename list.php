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
dol_include_once('/rewards/class/rewards.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$langs->loadLangs(array('companies','members','users','mails','other','assiduity@assiduity'));

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$rowid=GETPOST('rowid','int');
$id=GETPOST('id','int');
$memberlist=GETPOST('memberlist','alpha');

// Security check
$result=restrictedArea($user,'adherent',$rowid,'','cotisation');

$object = new Adherent($db);
$extrafields = new ExtraFields($db);
//$adht = new AdherentType($db);

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


 
if (0 == 0)  //affichage ou non de la liste
{
    $res=$object->fetch($rowid);
    if ($res < 0) { dol_print_error($db,$object->error); exit; }

    //$adht->fetch($object->typeid);

    $head = assiduity_prepare_head($object);
  
$member= GETPOST('member');    
$assiduity= GETPOST('assiduity'); 
$event= GETPOST('event');
if ($member= GETPOST('member')){

$evt=new ActionComm($db);
$evt->fetch($event);

foreach ($member as $mb) {
    
    $sql  = "INSERT INTO  ".MAIN_DB_PREFIX."adherent_assiduity (entity,fk_object,event_id,datestamp,assiduity)";
    $sql .= " VALUES ('$entity','$mb','$event',".$evt->datep.",'$assiduity[$mb]')";
    if (! $db->query($sql) )
    {
    dol_syslog(get_class($this)."::del_commercial Erreur");
    }
  if ($assiduity[$mb]==1) {
  $rewards = new Rewards($db);
  $member = new Adherent($db);
  $member->fetch($mb);      	
  $facture = new Facture($db);
	$facture->socid = $member->fk_soc;
	$facture->id = '';
  $facture->fk_facture_source = $event;
	$rewards->create($facture, GETPOST('rewards','int'));
  } else {
  $rewards = new Rewards($db);
  $member = new Adherent($db);
  $member->fetch($mb);      	
  $facture = new Facture($db);
	$facture->socid = $member->fk_soc;
	$facture->id = '';
  $facture->fk_facture_source = $event;
	$rewards->create($facture, 0);
  }

    }
}
 
   	if ($action == 'create')
	{     
  
        dol_fiche_head(null, 'card', '', 0, '');
        print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'" method="post" name="formsoc">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        
        print '<SELECT name="event">';  
        
        $sql = "SELECT c.id,c.datep,c.datep2,c.label,c.fk_action";               
        $sql.= " FROM ".MAIN_DB_PREFIX."actioncomm as c";
        $sql.= " WHERE c.fk_action in (".$conf->global->ASSIDUITY_EVENT.")";
        $sql.= " AND c.entity IN (" . getEntity('assiduity') . ") ";
        $sql.= " AND c.datep > '".$conf->global->ASSIDUITY_EVENT_BEGIN."' ";
        $sql.= " AND c.id NOT IN (SELECT event_id FROM ".MAIN_DB_PREFIX."adherent_assiduity) ";
        $sql.= " ORDER BY c.datep ASC";
        $sql.= " LIMIT 0,5";
        
        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;

            $var=True;
            while ($i < $num)
            {            
                $objp = $db->fetch_object($result);
                $var=!$var;               
             
                print '<OPTION value="'.$objp->id.'">'.dol_escape_htmltag($objp->label).' ('.dol_print_date($db->jdate($objp->datep),'dayhour').' '.dol_print_date($db->jdate($objp->datep2),'dayhour').')</OPTION>';   
                            
                $i++;
            }
        }
        else
        {
            dol_print_error($db);
        }

        print '</SELECT> et ajout de <input size="5" type="text" name="rewards" value="0"> points<br /><br />';
  
  
        $sql = "SELECT a.rowid,a.firstname,a.lastname, a.fk_adherent_type as typeid";               
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql.= " WHERE a.statut=1 AND a.fk_adherent_type IN(".$conf->global->ASSIDUITY_MEMBER_TYPE.")";
        $sql.= " AND a.entity IN (" . getEntity('assiduity') . ") ";
        $sql.= " ORDER BY a.firstname ASC";
        
        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("AssiduityStatut").'</td>';
            print '<td align="center">'.$langs->trans("Member").'</td>';
            print '<td align="left">'.$langs->trans("AssiduityStatutbis").'</td>';
            print '<td align="right">'.$langs->trans("AssiduityActual").'</td>';
            print "</tr>\n";

            $var=True;
            while ($i < $num)
            {            
                $objp = $db->fetch_object($result);
                $var=!$var;

$assiduity=new Assiduite($db);
$percenta=$assiduity->fetch_mb_actualassiduity($objp->rowid);               

$adht = new AdherentType($db);
$adht->fetch($objp->typeid);
//$extrafields = new ExtraFields($db);
//$extrafields->fetch_name_optionals_label($adht->table_element);               
                
                print "<tr ".$bc[$var].">";
                print '<td>'.$objp->rowid;              
                
                print '</td>';
                print '<td align="left"><a href="'.$dolibarr_main_url_root.dol_buildpath('/assiduity/card.php?rowid='.$objp->rowid, 1).'">'.img_picto('', 'object_user').' '.$objp->firstname.' '.$objp->lastname.'</a></td>'; 
                print '<td align="left"><input type="hidden" id="assiduity" class="flat" name="member['.$objp->rowid.']"  value="'.$objp->rowid.'" >';
                print '<input type="radio" id="assiduity" class="flat" name="assiduity['.$objp->rowid.']"  value="1"';
                if ($adht->array_options["options_assiduity_default"] == 1) { print ' checked';}
                print '> Présent ';
                print '<input type="radio" id="assiduity" class="flat" name="assiduity['.$objp->rowid.']"  value="0"';
                if ($adht->array_options["options_assiduity_default"] == 0) { print ' checked';}
                print '> Absent ';
                print '<input type="radio" id="assiduity" class="flat" name="assiduity['.$objp->rowid.']"  value="2"';
                if ($adht->array_options["options_assiduity_default"] == 2) { print ' checked';}
                print '> NC';
                print '</td>';                         
                print '<td align="right">'.$percenta.'%</td>';
                print "</tr>";
                $i++;
            }
            print "</table>";
        dol_fiche_end();

        print '<div class="center">';
        print '<input type="submit" class="button" name="create" value="'.$langs->trans('AssiduityAdd').'">';            
            print "</div></form>";
        }
        else
        {
            dol_print_error($db);
        }

  }
  elseif ($action == 'memberlist'){
        $sql = "SELECT a.rowid,a.firstname,a.lastname";               
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql.= " WHERE a.statut=1 AND a.fk_adherent_type IN(".$conf->global->ASSIDUITY_MEMBER_TYPE.")";
        $sql.= " AND a.entity IN (" . getEntity('assiduity') . ") ";
        $sql.= " ORDER BY a.firstname ASC";
        
        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;
 $assiduity=new Assiduite($db);           
            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("AssiduityStatut").'</td>';
            print '<td align="center">'.$langs->trans("Member").'</td>';
$percentnow=$assiduity->fetch_actualassiduity();
            print '<td align="center">'.$langs->trans("AssiduityActual").' '.$percentnow.'%</td>';
$percenttnow=$assiduity->fetch_totalassiduity();
            print '<td align="right">'.$langs->trans("AssiduityTotal").' '.$percenttnow.'%</td>';
            print "</tr>\n";

            $var=True;
            while ($i < $num)
            {            
                $objp = $db->fetch_object($result);
                $var=!$var;


              
                              
                print "<tr ".$bc[$var].">";
 print '<td>'.$objp->rowid;              
                
                print '</td>';
                print '<td align="left"><a href="'.$dolibarr_main_url_root.dol_buildpath('/assiduity/card.php?rowid='.$objp->rowid, 1).'">'.img_picto('', 'object_user').' '.$objp->firstname.' '.$objp->lastname.'</a></td>';
$percenta=$assiduity->fetch_mb_actualassiduity($objp->rowid);               
                print '<td align="left">'.$percenta.'%</td>';
$percentt=$assiduity->fetch_mb_totalassiduity($objp->rowid);                                          
                print '<td align="right">'.$percentt.'%</td>';
                print "</tr>";
                $i++;
            }
            print "</table>";
        dol_fiche_end();

        }
        else
        {
            dol_print_error($db);
        }
  }
  else {
  

    if (!$id)
    {
        $sql = "SELECT c.id,c.datep,c.datep2,c.label,c.fk_action";               
        $sql.= " FROM ".MAIN_DB_PREFIX."actioncomm as c";
        $sql.= " WHERE c.fk_action in (".$conf->global->ASSIDUITY_EVENT.") ";
        $sql.= " AND c.entity IN (" . getEntity('assiduity') . ") ";
        $sql.= " AND c.id IN (SELECT event_id FROM ".MAIN_DB_PREFIX."adherent_assiduity) ";
        $sql.= " ORDER BY c.datep DESC";
        $sql.= " LIMIT 0,60";
        
        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("AssiduityStatut").'</td>';
            print '<td align="center">'.$langs->trans("Member").'</td>';
            print '<td align="center">'.$langs->trans("DateStart").'</td>';
            print '<td align="center">'.$langs->trans("DateEnd").'</td>';
            print '<td align="right">'.$langs->trans("AssiduityStatutbis").'</td>';
            print "</tr>\n";

            $var=True;
            while ($i < $num)
            {            
                $objp = $db->fetch_object($result);
                $var=!$var;

$assiduity=new Assiduite($db);
$percent=$assiduity->fetch_ev_assiduity($objp->id);               
                
                
                print "<tr ".$bc[$var].">";
 print '<td>'.$objp->id;              
                
                print '</td>';
                print '<td align="left"><a href="'.$dolibarr_main_url_root.dol_buildpath('/assiduity/list.php?id='.$objp->id, 1).'">'.img_picto('', 'object_calendar').' '.dol_escape_htmltag($objp->label)."</a></td>";
                print '<td align="center">'.dol_print_date($db->jdate($objp->datep),'dayhour')."</td>\n";
                print '<td align="center">'.dol_print_date($db->jdate($objp->datep2),'dayhour')."</td>\n";                            
                print '<td align="right">'.$percent.'% <a href="'.$dolibarr_main_url_root.dol_buildpath('/assiduity/list.php?id='.$objp->id, 1).'">'.img_edit().'</a></td>';
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
    else
    {
        $sql = "SELECT p.assiduity,p.fk_object,p.event_id,a.rowid,a.firstname,a.lastname";               
        $sql.= " FROM  ".MAIN_DB_PREFIX."adherent_assiduity as p";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."adherent as a ON a.rowid=p.fk_object";
        $sql.= " WHERE p.event_id=".$id;
        $sql.= " AND p.entity IN (" . getEntity('assiduity') . ") ";
//        $sql.= " AND c.id IN (SELECT event_id FROM ".MAIN_DB_PREFIX."adherent_assiduity) ";
        $sql.= " ORDER BY a.firstname ASC";
        
        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("AssiduityStatut").'</td>';
            print '<td align="center">'.$langs->trans("Member").'</td>';
            print '<td align="center">'.$langs->trans("DateEnd").'</td>';
            print '<td align="right">'.$langs->trans("Amount").'</td>';
            print "</tr>\n";

            $var=True;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);
                $var=!$var;
                print "<tr ".$bc[$var].">";
 print '<td>'.$objp->rowid;              
                
                print '</td>';
                print '<td align="left"><a href="'.DOL_URL_ROOT.'/custom/assiduity/card.php?rowid='.$objp->rowid.'">'.img_picto('', 'object_user').' '.$objp->firstname." ".$objp->lastname."</a></td>";
                print '<td align="center">';
                
 if ($objp->assiduity=='0')
 {print img_picto($langs->trans('AssiduityAbsent'),'statut8').' '.$langs->trans('AssiduityAbsent');} 
 elseif ($objp->assiduity=='1')
 {print img_picto($langs->trans('AssiduityPresent'),'statut4').' '.$langs->trans('AssiduityPresent');}
 elseif($objp->assiduity=='2')
 {print img_picto($langs->trans('AssiduityNC'),'statut0').' '.$langs->trans('AssiduityNC');}  
                 
                print "</td>\n";
                               
                print '<td align="right"><a href="'.$dolibarr_main_url_root.dol_buildpath('/assiduity/list.php?id='.$objp->id, 1).'">'.img_edit().'</a></td>';
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


}
else
{
    $langs->load("errors");
    print $langs->trans("ErrorRecordNotFound");
}


llxFooter();

$db->close();