<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia - Search';
		$data['scripts'] = array('search','infobox');
		$data['js_lib'] = array('google_map', 'range_slider','vocab_widget','qtip');

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
		$page = 1;
		$start = 0;
		$filters = $this->input->post('filters');
		$this->load->library('solr');

		/**
		 * Default Search Parameters for RDA Portal search
		 */
		if (isset($filters["rows"]))
		{
			$pp = (int) $filters["rows"];
		}
		else
		{
			$pp = 15; 
		}
				
		$this->solr->setOpt('rows', $pp);
		// $this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '1');
		$this->solr->setOpt('fl', '*, score');

		if (!isset($filters["q"]))
		{
			$this->solr->setOpt('q', '*:*');
			$this->solr->setOpt('sort', 's_list_title asc');
		}


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
		$this->solr->setFacetOpt('limit','100');
		$this->solr->setFacetOpt('sort','index');

		/**
		 * Setting the SOLR OPTIONS based on the filters sent over AJAX
		 */
		$filteredSearch = false;
		if($filters){
			$this->solr->setOpt('q','');
			foreach($filters as $key=>$value){
				$value = rawurldecode($value);
				switch($key){
					case 'rq':
						$this->solr->addQueryCondition($value);
					break;
					case 'q': 
						$value = escapeSolrValue($value);
						//echo 'id:"'.$value.'"^1 group:"'.$value.'"^0.8 display_title:"'.$value.'"^0.5 list_title:"'.$value.'"^0.5 fulltext:*'.$value.'*^0.2'
						$this->solr->addQueryCondition('+(id:"'.$value.'"^1 group:"'.$value.'"^0.8 display_title:"'.$value.'"^0.5 list_title:"'.$value.'"^0.5 fulltext:*'.$value.'*^0.2)');
						// 'fulltext:*'.$value.'*');
					break;
					case 'p': 
						$page = (int)$value;
						if($page>1){
							$start = $pp * ($page-1);
						}
						$this->solr->setOpt('start', $start);
						$filteredSearch = true;
						break;
					case 'class': 
						if($value!='all'){
							$this->solr->addQueryCondition('+class:("'.$value.'")');
						}else{
							$this->solr->addQueryCondition('*:*');
						}
						$filteredSearch = true;
						break;
					case 'group': 
						$this->solr->addQueryCondition('+group:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'type': 
						$this->solr->addQueryCondition('+type:'.$value);
						$filteredSearch = true;
						break;
					case 'subject_value_resolved': 
						$this->solr->addQueryCondition('+subject_value_resolved:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 's_subject_value_resolved': 
						$this->solr->addQueryCondition('+s_subject_value_resolved:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'subject_vocab_uri':
						$this->solr->addQueryCondition('+subject_vocab_uri:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'temporal':
						$date = explode('-', $value);
						$this->solr->addQueryCondition('+earliest_year:['.$date[0].' TO *]');
						$this->solr->addQueryCondition('+latest_year:[* TO '.$date[1].']');
						$filteredSearch = true;
						break;
					case 'license_class': 
						$this->solr->addQueryCondition('+license_class:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'spatial':
						$this->solr->addQueryCondition('+spatial_coverage_extents:"Intersects('.$value.')"');
						$filteredSearch = true;
						break;
					case 'map':
						$this->solr->addQueryCondition('+spatial_coverage_area_sum:[0.00001 TO *]');
						if (isset($filters['rows']) && is_numeric($filters['rows']))
						{
						    $this->solr->setOpt('rows', $filters['rows']);
						}
						else
						{
						    $this->solr->setOpt('rows', 1500);
						}
						$this->solr->setOpt('fl', 'id,spatial_coverage_area_sum,spatial_coverage_centres,spatial_coverage_extents,spatial_coverage_polygons');
						break;
				}
			}
		}

		if(trim($this->solr->getOpt('q'))==''){
			$this->solr->addQueryCondition('*:*');
		}

//		var_dump($this->solr->constructFieldString());

		/**
		 * Doing the search
		 * smcphill 20130429: shouldn't need to store the entire result
		 * in 'solr_result'; all the relevant bits get added to $data
		 * later on (I think...)
		 */
		//$data['solr_result'] = $this->solr->executeSearch();
		$this->solr->executeSearch();



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
				uksort($solr_facet_values, "strnatcasecmp");
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
		// if($page > 1){
		// 	$pagi .=  '<a href="javascript:void(0);"> &lt;</a>';
		// }
		for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
			if (($x > 0) && ($x <= ceil($data['numFound'] / $pp))) { //if it's valid
				if($x==$page){//if we're on current
					$pagi .=  '<a href="javascript:;" class="current filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}else{//not current
					$pagi .=  '<a href="javascript:;" class="filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}
			}
		}
		// if not on last page, show Next
		// if($page < ceil($data['numFound'] / $pp)){
		// 	$pagi .=  '<a href="javascript:void(0);">&gt;</a>';
		// }
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="'.ceil($data['numFound'] / $pp).'">Last</a>';
		$pagi .=  '</div>';
		$data['pagination'] = $pagi;

		$data['options'] = $this->solr->getOptions();
		$data['facet_counts'] = $this->solr->getFacet();
		$data['fieldstrings'] = $this->solr->constructFieldString();

		//return the result to the client
		echo json_encode($data);
	}

	function getAllSubjects($vocab_type){
		$filters = $this->input->post('filters');
		$subjects_categories = $this->config->item('subjects_categories');
		$list = $subjects_categories[$vocab_type]['list'];
		$result = array();
		foreach($list as $l){
			$result_type = $this->getAllSubjectsForType($l, $filters);
			$result_list = (isset($result_type['list']) ? $result_type['list'] : array());
			$result = array_merge($result, $result_list);
		}

		$azTree = array();
		$azTree['0-9']=array('subjects'=>array(), 'total'=>0, 'display'=>'0-9');
		foreach(range('A', 'Z') as $i){$azTree[$i]=array('subjects'=>array(), 'total'=>0, 'display'=>$i);}

		foreach($result as $r){
			if(ctype_alnum($r['value'])){
				$first = strtoupper($r['value'][0]);
				if(is_numeric($first)){$first='0-9';}
				$azTree[$first]['total']++;
				array_push($azTree[$first]['subjects'], $r);
			}
		}
		$data['azTree'] = $azTree;
		$this->load->view('subjectfacet-tree', $data);
	}

	function getAllSubjectsForType($type, $filters){
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('fl', '*, score');
		$this->solr->setOpt('qf', 'id^1 group^0.8 display_title^0.5 list_title^0.5 fulltext^0.2');
		$this->solr->setOpt('rows', '0');

		$this->solr->clearOpt('fq');

		if($filters){
            foreach($filters as $key=>$value){
                $value = urldecode($value);
                switch($key){
                    case 'q': 
                        $this->solr->setOpt('q', "+fulltext:(*" . $value . "*)");
                        break;
                    case 'class': 
                        if($value!='all') $this->solr->addQueryCondition('+class:("'.$value.'")');
                        break;
                    case 'group': 
                        $this->solr->addQueryCondition('+group:("'.$value.'")');
                        break;
                    case 'type': 
                        $this->solr->addQueryCondition('+type:'.$value);
                        break;
                    case 's_subject_value_resolved': 
						$this->solr->addQueryCondition('+s_subject_value_resolved:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'subject_vocab_uri':
						$this->solr->addQueryCondition('+subject_vocab_uri:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'temporal':
						$date = explode('-', $value);
						$this->solr->addQueryCondition('+earliest_year:['.$date[0].' TO *]');
						$this->solr->addQueryCondition('+latest_year:[* TO '.$date[1].']');
						$filteredSearch = true;
						break;
                    case 'license_class': 
                        $this->solr->addQueryCondition('+license_class:("'.$value.'")');
                        break;             
                    case 'spatial':
                        $this->solr->addQueryCondition('+spatial_coverage_extents:"Intersects('.$value.')"');
                        break;
                    case 'map':
						$this->solr->addQueryCondition('+spatial_coverage_area_sum:[0.00001 TO *]');
						break;
                }
            }
        }
        $this->solr->addQueryCondition('+subject_type:"'.$type.'"');
		$this->solr->setFacetOpt('pivot', 'subject_type,subject_value_resolved');
		$this->solr->setFacetOpt('sort', 'subject_value_resolved');
		$this->solr->setFacetOpt('limit', '25000');
		$content = $this->solr->executeSearch();
		$facets = $this->solr->getFacet();
		$facet_pivots = $facets->{'facet_pivot'}->{'subject_type,subject_value_resolved'};
		//echo json_encode($facet_pivots);
		$result = array();
		$result[$type] = array();
		
		foreach($facet_pivots as $p){
			if($p->{'value'}==$type){
				$result[$type] = array('count'=>$p->{'count'}, 'list'=>array());
				foreach($p->{'pivot'} as $pivot){
					array_push($result[$type]['list'], array('value'=>$pivot->{'value'}, 'count'=>$pivot->{'count'}));
				}
				$result[$type]['size'] = sizeof($result[$type]['list']);
				// echo json_encode($p->{'pivot'});
			}
		}
		return $result[$type];
	}

	function getsubjectfacet(){
		$filters = $this->input->post('filters');
		$data['subjectType'] = $this->input->post('subjectType');
		$this->load->view('subjectfacet', $data);
	}

	function getTopLevel(){
		$this->load->library('vocab');
		$filters = $this->input->post('filters');
		echo json_encode($this->vocab->getTopLevel('anzsrc-for', $filters));
	}
}