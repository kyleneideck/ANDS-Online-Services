<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia - Search';
		$data['scripts'] = array('search');
		$data['js_lib'] = array('google_map', 'range_slider');

		$this->load->library('stats');
		$this->stats->registerPageView();		
		
		$this->load->view('search_layout', $data);
	}

	function filter(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		/**
		 * Getting the stuffs Ready
		 */
		$pp = 15;//per page
		$page = 1;
		$start = 0;
		$filters = $this->input->post('filters');
		$this->load->library('solr');

		/**
		 * Default Search Parameters for RDA Portal search
		 */
		
		$this->solr->setOpt('rows', $pp);
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('qf', 'id^10 group^8 display_title^5 list_title^5 fulltext^1.2');
		$facets = array(
			'class' => 'Class',
			//'subject_value_resolved' => 'Subjects',
			'group' => 'Contributed By',
			'type' => 'Type',
			'license_class' => 'Licence'
		);
		foreach($facets as $facet=>$display){
			$this->solr->setFacetOpt('field', $facet);
		}
		$this->solr->setFacetOpt('mincount','1');

		/**
		 * Setting the SOLR OPTIONS based on the filters sent over AJAX
		 */
		$filteredSearch = false;
		if($filters){
					
			foreach($filters as $key=>$value){
				$value = urldecode($value);
				switch($key){
					case 'q': 
						$this->solr->setOpt('q', $value);
					break;
					case 'p': 
						$page = (int)$value;
						if($page>1){
							$start = $pp * ($page-1);
						}
						$this->solr->setOpt('start', $start);
						$filteredSearch = true;
						break;
					case 'tab': 
						if($value!='all') $this->solr->setOpt('fq', 'class:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'group': 
						$this->solr->setOpt('fq', 'group:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'type': 
						$this->solr->setOpt('fq', 'type:'.$value);
						$filteredSearch = true;
						break;
					case 'subject_value_resolved': 
						$this->solr->setOpt('fq', 'subject_value_resolved:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'license_class': 
						$this->solr->setOpt('fq', 'license_class:("'.$value.'")');
						$filteredSearch = true;
						break;						
					case 'spatial':
						$this->solr->setOpt('fq', 'spatial_coverage_extents:"Intersects('.$value.')"');
						$filteredSearch = true;
						break;
					case 'map':
						$this->solr->setOpt('fq', 'spatial_coverage_extents:(*)');
						$this->solr->setOpt('rows', 100);
						break;
				}
			}
		}

		//var_dump($this->solr->constructFieldString());

		/**
		 * Doing the search
		 */
		$data['solr_result'] = $this->solr->executeSearch();



		/**
		 * Getting the results back
		 */
		$data['solr_header'] = $this->solr->getHeader();
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;

		/**
		 * Register the search term
		 */
		if(!$filteredSearch){
			$this->stats->registerSearchTerm($this->solr->getOpt('q'));
			$this->stats->registerSearchStats($this->solr->getOpt('q'),$data['numFound']);
		}

		/**
		 * House cleaning on the facet_results
		 */
		$data['facet_result'] = array();
		foreach($facets as $facet=>$display){
			$facet_values = array();
			$solr_facet_values = $this->solr->getFacetResult($facet);
			if(count($solr_facet_values)>0){
				foreach($solr_facet_values AS $title => $count){
					if($count>0){
						$facet_values[] = array(
							'title' => $title,
							'count' => $count
						);
					}
				}
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
				if($facet=='class'){
					$data['selected_tab'] = $facet;
				}
			}else if(isset($filters[$facet])){//for selected facet, always display this
				$facet_values[] = array('title'=>$filters[$facet], 'count'=>0);
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
			}
		}

		/**
		 * Pagination prep
		 * Page: {{page}}/{{totalPage}} |  <a href="#">First</a>  <span class="current">1</span>  <a href="#">2</a>  <a href="#">3</a>  <a href="#">4</a>  <a href="#">Last</a>
		 */
		$range = 3;
		$pagi = '';
		$pagi .= '<div class="page_navi">';
		$pagi .=  'Page: '.$page.'/'.ceil($data['numFound'] / $pp).'   |  ';
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="1">First</a>';
		if($page > 1){
			//$pagi .=  '<a href="javascript:void(0);"> &lt;</a>';
		}
		for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
			if (($x > 0) && ($x <= ceil($data['numFound'] / $pp))) { //if it's valid
				if($x==$page){//if we're on current
					$pagi .=  '<a href="javascript:;" class="current filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}else{//not current
					$pagi .=  '<a href="javascript:;" class="filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}
			}
		}
		//if not on last page, show Next
		if($page < ceil($data['numFound'] / $pp)){
			//$pagi .=  '<a href="javascript:void(0);">&gt;</a>';
		}
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="'.ceil($data['numFound'] / $pp).'">Last</a>';
		$pagi .=  '</div>';
		$data['pagination'] = $pagi;

		/**
		 * Debugging
		 */
		$data['options'] = $this->solr->getOptions();
		$data['facet_counts'] = $this->solr->getFacet();
		$data['fieldstrings'] = $this->solr->constructFieldString();

		//return the result to the client
		echo json_encode($data);
	}
}