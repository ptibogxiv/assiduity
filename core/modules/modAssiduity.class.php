<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2012	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2013-2014   Florian Henry   <florian.henry@open-concept.pro>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \defgroup agefodd Module AGeFoDD (Assistant de GEstion de la FOrmation Dans Dolibarr)
 * \brief agefodd module descriptor.
 * \file /core/modules/modAgefodd.class.php
 * \ingroup agefodd
 * \brief Description and activation file for module agefodd
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * \class modAgefodd
 * \brief Description and activation class for module agefodd
 */
class modAssiduity extends DolibarrModules {
	var $error;
	/**
	 * Constructor.
	 *
	 * @param DoliDB		Database handler
	 */
	function __construct($db) {
		global $conf;
		
		$this->db = $db;
		
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 431302;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'assiduity';
				// Module description used if translation string 'ModuleXXXDesc' not found (XXX is id value)
        $this->editor_name = 'ptibogxiv.net';
        $this->editor_url = 'https://www.ptibogxiv.net';
		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "hr";
    // Can be enabled / disabled only in the main company with superadmin account
		$this->core_enabled = 0;
		// Module label, used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module Assiduity";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '9.0.2';
		
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/images directory, use this->picto=DOL_URL_ROOT.'/module/images/file.png'
		$this->picto = 'assiduity@assiduity';
		
    
    // Dependencies
    $this->depends = array('modAdherent');		// List of modules id that must be enabled if this module is enabled
    $this->requiredby = array();	// List of modules id to disable if this one is disabled
    $this->phpmin = array(5,0);					// Minimum version of PHP required by module
    $this->need_dolibarr_version = array(4,0);	// Minimum version of Dolibarr required by module
    $this->langfiles = array("assiduity@assiduity");


       // Config pages. Put here list of php page, stored into oblyon/admin directory, to use to setup module.
    $this->config_page_url = array("assiduity.php@assiduity");
    
		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
    'hooks' => array('membercard') 
		);

        // New pages on tabs
        // -----------------
		$this->tabs = array(
				'member:+assiduity:AssiduityMenuSess:assiduity@assiduity:/assiduity/card.php?rowid=__ID__'
		);

        // Boxes
        //------
        $this->boxes = array();

		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;
		
		//Menu left into financial
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=members',
				'type'=>'left',
				'titre'=>'AssiduityMenuSess',
				'mainmenu'=>'members',
				'leftmenu'=>'Assiduity',
				'url'=>'/assiduity/list.php',
				'langs'=>'assiduity@assiduity',
				'position'=>100,
				'enabled'=>'$conf->adherent->enabled',
				'perms'=>'',
				'target'=>'',
				'user'=>0);    
        
    $r ++;
		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=members,fk_leftmenu=Assiduity',
				'type' => 'left',
				'titre' => 'AssiduityMenuNew',
				'url' => '/assiduity/list.php?action=create',
				'langs' => 'assiduity@assiduity',
				'position' => 101,
				'enabled' => '$conf->adherent->enabled',
				'perms' => '',
				'target' => '',
				'user' => 0
		);
    
        $r ++;
		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=members,fk_leftmenu=Assiduity',
				'type' => 'left',
				'titre' => 'AssiduityMenuList',
				'url' => '/assiduity/list.php',
				'langs' => 'assiduity@assiduity',
				'position' => 102,
				'enabled' => '$conf->adherent->enabled',
				'perms' => '',
				'target' => '',
				'user' => 0
		);
            $r ++;
		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=members,fk_leftmenu=Assiduity',
				'type' => 'left',
				'titre' => 'AssiduityMenuListMember',
				'url' => '/assiduity/list.php?action=memberlist',
				'langs' => 'assiduity@assiduity',
				'position' => 103,
				'enabled' => '$conf->adherent->enabled',
				'perms' => '',
				'target' => '',
				'user' => 0
		);

  }



}

?>