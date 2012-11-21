<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 *
 * XXX:
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 *
 */

class Registry_objects extends CI_Model {

	public $valid_classes = array("collection","activity","party","service");
	public $valid_status  = array("DRAFT"=>"DRAFT", "PUBLISHED"=>"PUBLISHED", "APPROVED"=>"APPROVED", "SUBMITTED_FOR_ASSESSMENT"=>"SUBMITTED_FOR_ASSESSMENT");
	public $valid_levels  = array("level_1"=>"1", "level_2"=>"2", "level_3"=>"3", "level_4"=>"4" );


	/**
	 * Generic registry_objects get handler.
	 *
	 * This moderately nifty piece of code lets you get some `_registry_object`
	 * goodness by any means necessary. Just add wat^H^H^H the following:
	 *   - a list of callback functions to apply to this (the `Registry_objects`) model
	 *   - whether you want `_registry_object`s or plain old `registry_object_id`s (i.e. ints)
	 *   - number of records to limit the reponse to (optional)
	 *   - offset from which to retrieve record set (optional)
	 *
	 * The callback pipeline expects an array of arrays. Crazy, I know. I blame PHP for not
	 * having a tuple/list/set datatype, or even a plain hash. That's right: I'm blaming my tools.
	 * Anyway, `$pipeline` should look something like:
	 *
	 *
	 * array(                                 //a list of callbacks to apply
	 *   array(                               //the first callback
	 *     'args' => ...,                     //arguments to pass to callback. this will be the second parameter. stuff with array as required
	 *     'fn' => function($db,$args){...}   //callback. takes a CodeIgniter db object, which should be returned by the function.
	 *  )
	 * )
	 *
	 *
	 * @param an array of processing callbacks (type `callable`). See above description
	 * for specific details.
	 * @param should we create `_registry_object` objects for each result? (default: true)
	 * @param query limit (int) (default: false; i.e. no limit)
	 * @param query offset (int) (default: false; i.e. no offset)
	 * @return an array of results, or null if no results.
	 */
	public function _get($pipeline, $make_ro=true, $limit=false, $offset=false)
	{
		if (!is_array($pipeline))
		{
			throw new Exception("pipeline must be an array");
		}

		foreach ($pipeline as $p)
		{
			if (!is_callable($p['fn']))
			{
				throw new Exception("pipeline members must be callable");
			}
		}

		$CI =& get_instance();
		$db = $CI->db;
		foreach ($pipeline as $p)
		{
			$db = call_user_func($p['fn'], $db, $p['args']);
		}
		$results = null;
		$query = false;
		if ($limit && $offset)
		{
			$query = $db->get(null, $limit, $offset);
		}
		elseif ($limit)
		{
			$query = $db->get(null, $limit);
		}
		elseif ($offset)
		{
			$query = $db->get(null, null, $offset);
		}
		else
		{
			$query = $db->get();
		}
		if ($query->num_rows() > 0)
		{
			$results = array();
			foreach ($query->result_array() as $rec)
			{
				$results[] = $make_ro ? new _registry_object($rec["registry_object_id"]) : $rec;
			}
		}
		if ($query)
		{
			$query->free_result();
		}
		return $results;
	}

	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("key", $key);
							    return $db;
						    })));
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByID($id)
	{
		$results = $this->_get(array(array('args' => $id,
						   'fn' => function($db,$id) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("registry_object_id", $id);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
	}


	/**
	 * Returns exactly one registry object by URL slug (or NULL)
	 *
	 * @param the registry object slug
	 * @param the status of the registry object we want 
	 * @return _registry_object object or NULL
	 */
	function getBySlug($slug, $status = "PUBLISHED")
	{
		$results = $this->_get(array(array('args' => $slug,
						   'fn' => function($db,$slug) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("status", "PUBLISHED")
								   ->where("slug", $slug);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_registry_object)
	 */
	function getByAttribute($attribute_name, $value, $core=false)
	{
		$args = array('name' => $attribute_name, 'val' => $value);
		return $core == true ?
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_objects")
								->where($args['name'], $args['val']);
							return $db;
						})))
			:
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_object_attributes")
								->where("attribute", $args['name'])
								->where("value", $args['val']);
							return $db;
						})))
			;
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array).
	 * Note that by default, this method returns registry_object_id's only. If you want
	 * `_registry_object`s, pass an additional boolean `true` for the second parameter
	 *
	 * @param the data source ID to match by
	 * @param boolean flag indicating whether to return an array of IDs (int), or an
	 * array of `_registry_object`s.
	 * @return array of results, or null if no matching records
	 */
	function getIDsByDataSourceID($data_source_id, $make_ro=false)
	{
		$results =  $this->_get(array(array('args' => $data_source_id,
						    'fn' => function($db, $dsid) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("data_source_id", $dsid);
							    return $db;
						    })),
					$make_ro);
		return $make_ro ? $results : array_map(function($r){return $r['registry_object_id'];}, $results);
	}

	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the data source ID to match by
	 * @return array(_registry_object)
	 */
	function getByDataSourceKey($data_source_key)
	{
		return $this->_get(array(array('args' => $data_source_key,
					       'fn' => function($db, $dsk) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->join("data_sources",
								      "data_sources.data_source_id = registry_objects.data_source_id")
							       ->where("data_sources.key", $dsk);
						       return $db;
					       })));
	}


	/**
	 * Get a number of registry_objects that match the class requirement (or an empty array)
	 *
	 * @param the value that the class must match
	 * @return array(_registry_object)
	 */
	function getByClass($class)
	{
		return $this->_get(array(array('args' => $class,
					       'fn' => function($db, $class) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->where("class", $class);
						       return $db;
					       })));
	}


	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function create($data_source_key, $registry_object_key, $class, $title, $status, $slug, $record_owner, $harvestID)
	{
		if (is_null($this->getByKey($registry_object_key)))
		{

			$ro = new _registry_object();

			// Get the data_source_id for this data source key
			$this->load->model('data_source/data_sources','ds');
			$ds = $this->ds->getByKey($data_source_key);
			$ro->_initAttribute("data_source_id", $ds->getAttribute('data_source_id'), TRUE);


			$ro->_initAttribute("key",$registry_object_key, TRUE);
			$ro->_initAttribute("class",$class, TRUE);
			$ro->_initAttribute("title",$title, TRUE);
			$ro->_initAttribute("status",$status, TRUE);
			$ro->_initAttribute("slug",$slug, TRUE);
			$ro->_initAttribute("record_owner",$record_owner, TRUE);

			// Some extras
			$ro->setAttribute("created",time());
			$ro->setAttribute("harvest_id", $harvestID);

			$ro->create();
			return $ro;

		}
		else
		{
			return $this->update($registry_object_key, $class, $title, $status, $slug, $record_owner);
		}
	}

	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function update($registry_object_key, $class, $title, $status, $slug, $record_owner)
	{
		$ro = $this->getByKey($registry_object_key);
		if (!is_null($ro))
		{

			$ro->setAttribute("class",$class);
			$ro->setAttribute("title",$title);
			$ro->setAttribute("status",$status);
			$ro->setAttribute("slug",$slug);
			$ro->setAttribute("record_owner",$record_owner);

			$ro->save();
			return $ro;
		}
		else
		{
			throw new Exception ("Unable to update registry object (this registry object key does not exist in the registry)");
		}
	}



	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		include_once("_registry_object.php");
	}

}
