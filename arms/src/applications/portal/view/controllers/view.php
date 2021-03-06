<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View extends MX_Controller {


	/* DEFAULT VIEW HANDLER -- WILL FETCH EXTRIF AND HAND OVER TO A RENDERER (BELOW) */
	function index()
	{
		// Published records are always referenced by SLUG
		// (for backwards compatibility, we can also redirect based on a key)
		if ($this->input->get('key'))
		{
			$this->handleRedirectFromKeyToSlug($this->input->get('key'));
			return;
		}
		else if (!$this->input->get('slug') && !$this->input->get('id'))
		{			
			$this->load->view('soft404');
			return;
		}

		$this->load->model('registry_fetch','registry');

		if ($this->input->get('slug'))
		{
			try
			{
				$extRif = $this->registry->fetchExtRifBySlug($this->input->get('slug'));
			}
			catch (SlugNoLongerValidException $e)
			{
				$this->load->view('soft404', array('previously_valid_title'=>$e->getMessage()));
				return;
			}
			catch (PageNotValidException $e)
			{			
				header("HTTP/1.1 404 Not Found");
				$this->load->view('soft404', array('message'=>$e->getMessage()));
				return;
			}			
		}
		// Draft records are always referenced by ID
		else if ($this->input->get('id'))
		{
			try
			{
				$extRif = $this->registry->fetchExtRifByID($this->input->get('id'));
			}
			catch (PageNotValidException $e)
			{			
				header("HTTP/1.1 404 Not Found");
				$this->load->view('soft404', array('message'=>$e->getMessage()));
				return;
			}						
		}

		// Check we actually got some data back (would probably have an exception before this)
		if (!isset($extRif['data']) || !$extRif['data'])
		{
			$this->load->view('soft404');
			return;
		}
		$this->load->library('stats');
		$this->stats->registerPageView($extRif['registry_object_id']);


		// Check if we have a specific rendering template
		if(isset($extRif['template']) && $extRif['template'] == CONTRIBUTOR_PAGE_TEMPLATE)
		{
			// If there is a renderer for this template, use it!
			$this->checkCustomTemplate($extRif);
		}
		else
		{
			$this->renderDefaultViewPage($extRif);
		}

	}




	private function renderDefaultViewPage($extRif)
	{	
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('dynatree','qtip','google_map');
		$data['scripts'] = array('view');
		$data['ro_slug'] = '';
		$data['ro_id'] = '';


		$suggested_links = array();

		if ($this->input->get('slug'))
		{
			$connections = $this->registry->fetchConnectionsBySlug($this->input->get('slug'));
			$suggested_links['identifiers'] = $this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), "ands_identifiers",0 ,0);
			$suggested_links['subjects'] = $this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), "ands_subjects",0 ,0);
			$data['ro_slug'] = $this->input->get('slug');
		}
		else
		{
			$connections = $this->registry->fetchConnectionsByID($this->input->get('id'));
			$suggested_links['identifiers'] = $this->registry->fetchSuggestedLinksByID($this->input->get('id'), "ands_identifiers",0 ,0);
			$suggested_links['subjects'] = $this->registry->fetchSuggestedLinksByID($this->input->get('id'), "ands_subjects",0 ,0);
			$data['ro_id'] = $this->input->get('id');
		}

		// Render the connections box
		$data['connections_contents'] = $connections;
		$connDiv = $this->load->view('connections', $data, true);

		// Render the suggested links
		$data['suggested_links_contents'] = $suggested_links;
		$suggestedLinksDiv = $this->load->view('suggested_links', $data, true);
		//exit();

		// Generate the view page contents
		$data['registry_object_contents'] = $this->registry->transformExtrifToHTMLStandardRecord($extRif['data']);

		// Leo's suspect-looking decoding (html_entity_decode() in registry_fetch)
		/*$data['registry_object_contents'] = str_replace('&amp;','&', $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('&amp;','&', $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('&lt;','<', $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('&gt;','>', $data['registry_object_contents']);*/

		// well this was really uggly... we should fix it at ingest!
		$data['registry_object_contents'] = str_replace('%%%%CONNECTIONS%%%%', $connDiv, $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('%%%%ANDS_SUGGESTED_LINKS%%%%', $suggestedLinksDiv, $data['registry_object_contents']);
		if(isset($extRif['hasContributor']))
		{
			$data['registry_object_contents'] = str_replace("%%%%GROUP_LINK%%%%search/#!/group=".rawurlencode($extRif['contributorGroup']),$extRif['hasContributor'],$data['registry_object_contents']);
		}else{
			$data['registry_object_contents'] = str_replace("%%%%GROUP_LINK%%%%","",$data['registry_object_contents']);
		}
		$this->load->view('default_view', $data);

	}



	/*
	 * Render this page as a Contributor Page
	 * @param extRif - The extended RIFCS for this record
	 */
	private function renderContributorPage($extRif)
	{
		$data['title']='Research Data Australia';
		$data['js_lib'] = array('dynatree','qtip');
		$data['scripts'] = array('view');

		// Should support both drafts and published records
		// You are viewing a published record if $this->input->get('slug') is set
		// You are viewing a draft record if $this->input->get('id') is set
		// (draft records include data/statistics including other draft records)
		
		$published_only = ($this->input->get('slug') ? true : false);

		// In here, go get the information/precanned text, etc.
		// we have $this->registry-> which gives us the functions in models/registry_fetch.php


		if ($this->input->get('slug'))
		{
			$data['contentData'] = $this->registry->fetchContributorData((string)$this->input->get('slug'));
			$contentDiv = $this->load->view('contentData', $data, true);
			$data['cannedText'] = $this->registry->fetchContributorText((string)$this->input->get('slug'));
			$cannedTextDiv = $this->load->view('cannedText', $data, true);
		}
		/*
		XXX: Todo-> fix this for draft records
		else if ($this->input->get('ia'))
		{
			$data['contentData'] = $this->registry->fetchContributorData((string)$this->input->get('id'));
			$contentDiv = $this->load->view('contentData', $data, true);
			$data['cannedText'] = $this->registry->fetchContributorText((string)$this->input->get('id'));
			$cannedTextDiv = $this->load->view('cannedText', $data, true);
		}
		*/
		else
		{
			$contentDiv = "<i class='lightgrey'>Contributor page data will be generated when this page is published</i>";
			$cannedTextDiv = "<i class='lightgrey'>Contributor page details will be generated when this page is published</i>";
		}



		$connDiv = $this->load->view('connections', $data, true);

		//$data['some_random_data_for_the_view_to_parse'] = $contributorData['data'];

		$data['registry_object_contents'] = $this->registry->transformExtrifToHTMLContributorRecord($extRif['data']);
		$data['registry_object_contents'] = str_replace('%%%%CONTENTS%%%%', $contentDiv, $data['registry_object_contents']);
		$data['registry_object_contents'] = str_replace('%%%%CANNED_TEXT%%%%', $cannedTextDiv, $data['registry_object_contents']);	
		$this->load->view('contributor_view', $data);
	}








	/* This preview widget is embedded in qtips popups */
	/* Note: do not use exceptions as this will override screen
			 styles and produce an undesirable error effect */
	function preview(){
		$this->load->model('registry_fetch','registry');

		if ($this->input->get('slug'))
		{

			try
			{
				$extRif = $this->registry->fetchExtRifBySlug($this->input->get('slug'));
				$html = $this->registry->transformExtrifToHTMLPreview($extRif['data']); 
			}
			catch (SlugNoLongerValidException $e)
			{
				die("Registry object could not be located (perhaps it no longer exists!)");
			}
		}
		else if ($this->input->get('registry_object_id')) {
			try
			{
				$extRif = $this->registry->fetchExtRifByID($this->input->get('registry_object_id'));
				$html = $this->registry->transformExtrifToHTMLPreview($extRif['data']);
			}
			catch (SlugNoLongerValidException $e)
			{
				die("Registry object could not be located (perhaps it no longer exists!)");
			}
		}
		else if ($this->input->post('roIds')) {
			$currRoID = null;

			$html = '';
			foreach($this->input->post('roIds') as $roID)
			{
				$currRoID = $roID;
				try
				{
					$extRif = $this->registry->fetchExtRifByID($roID);
					$html .= $this->registry->transformExtrifToHTMLPreview($extRif['data'], true);
				}
				catch (ErrorException $e)
				{
					$html .= "<div class='ro_preview'><div class='ro_preview_header'></div><div class='title'><i>Oops! The record at this location is missing - perhaps it was removed from the registry? (#".$currRoID.")</i> </div></div>";
				}		
				catch (PageNotValidException $e)
				{			
					$html .= "<div class='ro_preview'><div class='ro_preview_header'></div><div class='title'><i>Oops! The record at this location is missing - perhaps it was removed from the registry? (#".$currRoID.")</i> </div></div>";
				}						
			}			
		}
		else 
		{
			die("Registry object could not be located (no SLUG or ID specified!)");
		}

		$response = array(
			"slug" => $this->input->get('slug'),
			"registry_object_id" => $this->input->get('registry_object_id'),
			"html" => "<div class='previewbox'>".$html."</div>"
		);

		echo json_encode($response);
	}




	function connectionGraph()
	{
		$this->load->model('registry_fetch','registry');
		if ($this->input->get('slug'))
		{
			echo json_encode($this->registry->fetchAncestryGraphBySlug($this->input->get('slug')));
		}
		else if ($this->input->get('id'))
		{
			echo json_encode($this->registry->fetchAncestryGraphByID($this->input->get('id')));
		}
	}

	function getConnections(){
		$this->load->model('registry_fetch','registry');
		$limit = 10;
		$page = ($this->input->get('page')) ? $this->input->get('page') : 1;
		if (!$this->input->get('relation_type')) throw new Exception("Must specify relation_type for getConnections request");

		$offset = ($page * $limit) - $limit;
		if ($this->input->get('slug')){
			$connections = $this->registry->fetchConnectionsBySlug($this->input->get('slug'), $limit, $offset, $this->input->get('relation_type'));
			$data['related_identity_type']='slug';
		}
		else if ($this->input->get('id')){
			$connections = $this->registry->fetchConnectionsByID($this->input->get('id'), $limit, $offset, $this->input->get('relation_type'));
			$data['related_identity_type']='registry_object_id';
		}
		$connections = $connections['connections'];
		$data['currentPage'] = $page;
		$data['totalPage'] = ceil(($connections[0][$this->input->get('relation_type').'_count'])/$limit);
		$data['totalResults'] = $connections[0][$this->input->get('relation_type').'_count'];
		$data['slug'] = $this->input->get('slug');
		$data['id'] = $this->input->get('id');
		$data['connections_contents'] = $connections[0];
		$data['relation_type'] = $this->input->get('relation_type');
		$this->load->view('connections_all', $data);
	}


	function getSuggestedLinks($suggestor, $start, $rows)
	{
		$this->load->model('registry_fetch','registry');

		try 
		{
			if ($this->input->get('slug'))
			{
				echo json_encode($this->registry->fetchSuggestedLinksBySlug($this->input->get('slug'), 
																			$suggestor, $start, $rows));
			}
			else if ($this->input->get('id'))
			{

				echo json_encode($this->registry->fetchSuggestedLinksByID($this->input->get('id'),
																		 $suggestor, $start, $rows));
			}
		} catch (Exception $e)
		{
			echo json_encode(array("status"=>"error","message"=>$e->getMessage()));
		}

	}

	private function checkCustomTemplate($extRifResponse)
	{
		// Check if we have a specific rendering template
		if(isset($extRifResponse['template']) && $extRifResponse['template'] == CONTRIBUTOR_PAGE_TEMPLATE)
		{
			$this->renderContributorPage($extRifResponse);
		}
		else
		{
			$this->renderDefaultViewPage($extRifResponse);
		}
	}


	private function handleRedirectFromKeyToSlug($key)
	{
		if ($key)
		{
			$this->load->model('registry_fetch','registry');
			$slug = $this->registry->getSlugFromKey($key);
			if ($slug)
			{
				redirect("/" . $slug);
			}
			else
			{
				$this->load->view('soft404');
				return;
			}
		}
	}

}