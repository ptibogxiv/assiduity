<?php
/* Copyright (C) 2013 Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2013 Ferran Marcet           <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU  *General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

// Put here all includes required by your class file
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 *	\class      Rewards
 *	\brief      Class for Rewards
 */
class Assiduite extends CommonObject
{
	public $event_id;
  public $fk_object;
  public $assiduity;
  public $entity;
	
	/**
	 * 	Constructor
	 *
	 * 	@param	DoliDB		$db			Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	/**
	 * 
	 * @param 	Facture 	$facture	Invoice object
	 * @param 	double 		$points		Points to add/remove
	 * @param 	string 		$typemov	Type of movement (increase to add, decrease to remove)
	 * @return int			<0 if KO, >0 if OK
	 */
   
public function fetch_ev_assiduity($event)
{ 
		global $langs;

		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE event_id = $event and assiduity='1' ";
    $sql .= " AND entity IN (" . getEntity('assiduity') . ") ";    

		dol_syslog(get_class($this) . "::fetch_ev_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE event_id = $event and assiduity!='2' ";
    $sql .= " AND entity IN (" . getEntity('assiduity') . ") ";     

		dol_syslog(get_class($this) . "::fetch_ev_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
      
     $presence=price(100*$present/$total); 
			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_ev_assiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	}
public function fetch_mb_actualassiduity($member)
{ 
		global $langs;

$begin= dol_now()-(24*3600*365.25);
		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member AND assiduity='1' ";
    $sql.= " AND datestamp>=$begin AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member and assiduity!='2' ";
    $sql.= " AND datestamp>=$begin AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_actualassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
    if ($total>0){
        $presence=price(100*$present/$total);  
    }
    else $presence=price(0);

			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_mb_actualassiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	} 
  public function fetch_actualassiduity()
{ 
		global $langs;

$begin= dol_now()-(24*3600*365.25);
		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE assiduity='1' ";
    $sql.= " AND datestamp>=$begin AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE assiduity!='2' AND datestamp>=$begin AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_actualassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
    if ($total>0){
        $presence=price(100*$present/$total);  
    }
    else $presence=price(0);

			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_mb_actualassiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	}     
public function fetch_mb_totalassiduity($member)
{ 
		global $langs;

		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member and assiduity='1' ";
    $sql.= " AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_totalassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member  and assiduity!='2' ";
    $sql.= " AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_totalassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
    if ($total>0){
        $presence=price(100*$present/$total);  
    }
    else $presence=price(0);

			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_mb_assiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	}  
public function fetch_totalassiduity()
{ 
		global $langs;

		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE assiduity='1' ";
    $sql.= " AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_totalassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE assiduity!='2' AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_totalassiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
    if ($total>0){
        $presence=price(100*$present/$total);  
    }
    else $presence=price(0);

			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_mb_assiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	}   
  public function select_ev_assiduity($member)
{ 
		global $langs;

		$sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member and assiduity='1' ";
    $sql.= " AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$present = $obj->num;
			} else {
      $present = 0;
      }
				
			$this->db->free($resql);
      
    $sql = "SELECT count(*) as num";
		$sql .= " FROM  " . MAIN_DB_PREFIX . "adherent_assiduity ";
		$sql .= " WHERE fk_object = $member  and assiduity!='2' ";
    $sql.= " AND entity IN (" . getEntity('assiduity') . ") "; 

		dol_syslog(get_class($this) . "::fetch_mb_assiduity ", LOG_DEBUG);
		$resql = $this->db->query($sql);
	
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$total = $obj->num;
			} else {
      $total = 1;
      }	
			$this->db->free($resql); 
    
     $presence=price(100*$present/$total); 
			return $presence;
      
    if ($resql) {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_mb_assiduity " . $this->error, LOG_ERR);
			return - 1;
		}
	}
  
}