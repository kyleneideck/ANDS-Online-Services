<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Slug_Extension extends ExtensionBase
{
	const maxLength = 255;
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	
	function generateSlug()
	{
		// This function expects a title to be present!
		if (!$this->ro->title)
		{
			$this->ro->updateTitles();
		}
	
		$result = strtolower($this->ro->title);
		
		$result = preg_replace("/[^a-z0-9\s-]/", "", $result);
		$result = trim(preg_replace("/[\s-]+/", " ", $result));
		$result = trim(substr($result, 0, self::maxLength));
		$result = preg_replace("/\s/", "-", $result);

		// Check that there are no clashes
		$query_ro_slugs = $this->db->select('registry_object_id')->get_where('registry_objects',array("slug"=> $result));
		$query_url_mappings = $this->db->select('registry_object_id')->get_where('url_mappings',array("slug"=> $result));
		if ($query_ro_slugs->num_rows() > 0 || $query_url_mappings->num_rows())
		{
			if ($query_ro_slugs->num_rows() > 0)
			{
				$existing_slug = array_pop($query_ro_slugs->result_array());
			}
			else if ($query_url_mappings->num_rows() > 0)
			{
				$query_url_mappings = array_pop($query_ro_slugs->result_array());
			}

			$query_ro_slugs->free_result();
			$query_url_mappings->free_result();

			// The slug gets abandoned if it's related record is deleted
			if (!$existing_slug['registry_object_id'])
			{
				// Update to point back to us
				$this->db->where("slug", $result);
				$this->db->update("url_mappings", array("registry_object_id"=>$this->ro->id, "search_title"=>$this->ro->title, "updated"=>time()));

				$this->ro->slug = $result;
				$this->ro->save();
				return $result;
			}
			else if ($existing_slug['registry_object_id'] == $this->ro->id)
			{
				// This is the same record
				// Nothing to do?
			}
			else
			{
				// Not the same record, so lets try and generate a new unique key...
				// this isn't guaranteed to be unique, but is likely to be
				$result .= "-" . sha1($this->id);
				$query = $this->db->select('registry_object_id')->get_where('url_mappings',array("slug"=> $result));
				if ($query->num_rows() == 0)
				{
					$this->db->insert('url_mappings', array("slug"=>$result, "registry_object_id"=>$this->id, "search_title"=>$this->ro->title, "created"=>time(), "updated"=>time()));
				}
				else
				{
					$this->db->where("slug", $result);
					$this->db->update("url_mappings", array("registry_object_id"=>$this->ro->id, "search_title"=>$this->ro->title, "updated"=>time()));

				}
				$this->ro->slug = $result;
				$this->ro->save();
				return $result;
			}
			
		}
		else 
		{
			//Assume this is the first time
			$this->db->insert('url_mappings', array("slug"=>$result, "registry_object_id"=>$this->id, "created"=>time(), "updated"=>time()));
			$this->ro->slug = $result;
			$this->ro->save();
			return $result;
		}

	}
	
	function getAllSlugs()
	{
		$slugs = array();
		
		$query = $this->db->select("slug, created, updated")->get_where('url_mappings', array("registry_object_id"=>$this->id));
		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() AS $row)
			{
				$slugs[] = $row;	
			}
		}
		$query->free_result();
		return $slugs;
	}
}
	
	