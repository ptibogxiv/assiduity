<?php
/* Copyright (C) 2004		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2005-2013	Laurent Destailleur		<eldy@users.sourceforge.org>
 * Copyright (C) 2011-2012	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2012  Juanjo Menent			<jmenent@2byte.es>
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
 * \file       htdocs/paypal/admin/paypal.php
 * \ingroup    paypal
 * \brief      Page to setup paypal module
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';

$servicename='Assiduity';

$langs->load("errors");
$langs->load("admin");
$langs->load("main");
$langs->load("assiduity@assiduity");

if (! $user->admin) accessforbidden();

$action = GETPOST('action','alpha');

if ($action == 'setvalue' && $user->admin)
{

$mydate = dol_mktime(12, 0 , 0, $_POST['beginmonth'], $_POST['beginday'], $_POST['beginyear']);
$begin=strftime('%Y-%m-%d ', $mydate);

	$db->begin();
  
$event=GETPOST('assiduity_event');
$arraye = array();
foreach ($event as $mb) {
$arraye[] = $mb;
}    
$ev_separated = implode(",", $arraye);  
  
    $result=dolibarr_set_const($db, "ASSIDUITY_EVENT",$ev_separated,'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
        $result=dolibarr_set_const($db, "ASSIDUITY_EVENT_BEGIN",$begin,'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++; 
    
$member=GETPOST('assiduity_type');
$arraym = array();
foreach ($member as $mb) {
$arraym[] = $mb;
}    
$mb_separated = implode(",", $arraym);
    $result=dolibarr_set_const($db, "ASSIDUITY_MEMBER_TYPE",$mb_separated,'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;             
	if (! $error)
  	{
  		$db->commit();
  		setEventMessage($langs->trans("SetupSaved"));
  	}
  	else
  	{
  		$db->rollback();
		dol_print_error($db);
    }
}


/*
 *	View
 */

$form=new Form($db);
$formproduct=new FormProduct($db);

llxHeader('',$langs->trans("AssiduitySetup"));


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("AssiduitySetup"),$linkback);
print '<br>';

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';


dol_fiche_head ('', 'assiduity', $langs->trans ( "Assiduity" ), 0, "assiduity@assiduity" );

print $langs->trans("AssiduityDesc")."<br>\n";

// Test if php curl exist
if (! function_exists('curl_version'))
{
	$langs->load("errors");
	setEventMessage($langs->trans("ErrorPhpCurlNotInstalled"), 'errors');
}


print '<br>';

print '<table class="noborder" width="100%">';

$var=true;
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Assiduity_Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("ASSIDUITY_EVENT_BEGIN").'</td><td>';
if ($conf->global->ASSIDUITY_EVENT_BEGIN) {
$datefrom=$conf->global->ASSIDUITY_EVENT_BEGIN;
}
else {
$datefrom=dol_getdate();
}
print $form->select_date($datefrom,'begin',0,0,0,"myform");
print ' &nbsp; '.$langs->trans("Example").': ';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("ASSIDUITY_EVENT").'</td><td>';
 $sql = "SELECT a.id,a.libelle";        
        $sql.= " FROM ".MAIN_DB_PREFIX."c_actioncomm as a";
        $sql.= " WHERE active=1 ORDER BY position ASC";
        
        $result = $db->query($sql);
        if ($result)
        {

            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            $var=True;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);
                $var=!$var;
                print "<tr ".$bc[$var].">";
 print '<td><input type="checkbox" id="assiduity_event" class="flat" name="assiduity_event['.$objp->id.']"  value="'.$objp->id.'" ';
 
 if (in_array($objp->id,explode(",",$conf->global->ASSIDUITY_EVENT))) {
 print ' checked';
 }
  
 print '>';
              
                print '</td>';
                print '<td align="left">'.$langs->trans($objp->libelle)."</td>";
                print "</tr>";
                $i++;
            }
            print "</table>";
        }
        else
        {
            dol_print_error($db);
        }
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("ASSIDUITY_MEMBER_TYPE").'</td><td>';
        $sql = "SELECT p.rowid,p.entity,p.libelle";        
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent_type as p";
        $sql.= " WHERE  p.entity IN (" . getEntity('adherent') . ")";
        
        $result = $db->query($sql);
        if ($result)
        {

            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            $var=True;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);
                $var=!$var;
                print "<tr ".$bc[$var].">";
 print '<td><input type="checkbox" id="assiduity_type" class="flat" name="assiduity_type['.$objp->rowid.']"  value="'.$objp->rowid.'" ';
 
 if (in_array($objp->rowid,explode(",",$conf->global->ASSIDUITY_MEMBER_TYPE))) {
 print ' checked';
 }
  
 print '>';
              
                print '</td>';
                print '<td align="left">'.dol_escape_htmltag($objp->libelle)."</td>";
                print "</tr>";
                $i++;
            }
            print "</table>";
        }
        else
        {
            dol_print_error($db);
        }
print '</td></tr>';


print '</table>';

dol_fiche_end();

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></div>';

print '</form>';

print '<br><br>';

print '<div id="apidoc">';
print '</div>';


llxFooter();
$db->close();
