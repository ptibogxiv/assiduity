<?php
/* Copyright (C) 2017	Regis Houssin	<regis.houssin@capnetworks.com>
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

use Luracast\Restler\RestException;

dol_include_once('/assiduity/class/assiduity.class.php');

/**
 * API class for multicompany
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class Assiduity extends DolibarrApi
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    static $FIELDS = array(
        'label'
    );

    /**
     * Constructor
     */
    function __construct()
    {
        global $db, $conf;
        $this->db = $db;
    }

    /**
     * Get properties of an entity
     *
     * Return an array with entity informations
     *
     * @param     int     $id ID of entity
     * @return    array|mixed data without useless information
     *
     * @throws    RestException
     */
    function get($id)
    {

$assiduity=new Assiduite($this->db);
$percenta=$assiduity->fetch_mb_actualassiduity($id); 
$percentnow=$assiduity->fetch_actualassiduity();        
$percentt=$assiduity->fetch_mb_totalassiduity($id);       
        return array(
            'actual0' => $percenta,
            'total0' => $percentnow

        );
    }


    /**
     * Validate fields before creating an object
     *
     * @param array|null    $data   Data to validate
     * @return array
     *
     * @throws RestException
     */
    function _validate($data)
    {
        $membertype = array();
        foreach (MembersTypes::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
            $membertype[$field] = $data[$field];
        }
        return $membertype;
    }

    /**
     * Clean sensible object datas
     *
     * @param   object  $object    Object to clean
     * @return    array    Array of cleaned object properties
     */
    function _cleanObjectDatas($object) {

        $object = parent::_cleanObjectDatas($object);

        // Remove constants
        foreach($object as $key => $value)
        {
        	if (preg_match('/^MAIN_/', $key))
        	{
        		unset($object->$key);
        	}
        }

        unset($object->language);
        unset($object->fk_tables);
  

        return $object;
    }

}
