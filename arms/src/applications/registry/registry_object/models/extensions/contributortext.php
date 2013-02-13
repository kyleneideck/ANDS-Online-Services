<?php

class ContributorText_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	

	function getContributorText()
	{

		$this->_CI->load->library('solr');
		$this->_CI->solr->setOpt('q', '*:*');
		$this->_CI->solr->setOpt('fq', 'group:("'.$this->ro->getAttribute('group').'")');
		//$this->_CI->solr->setOpt('rows','0');
		$this->_CI->solr->setFacetOpt('field', 'class');
		$this->_CI->solr->setFacetOpt('field', 'subject_value_resolved');		
		$this->_CI->solr->setFacetOpt('mincount','1');
		$this->_CI->solr->executeSearch();
		$groupName = $this->ro->getAttribute('group');
		//$group = (string)$group;

		//we want to select all published registry objects which have the same group faceted by class;

		$classes = $this->_CI->solr->getFacetResult('class');

		foreach($classes as $class=>$num){
			$contributorData['contents'][$class] = $num;
		}

		$collectionCount = $contributorData['contents']['collection'];
		if($collectionCount==0)
		{
			$collectionCount = 'no';
		}	
		elseif($collectionCount==1)
		{
			$collectionWord = 'collection';
			$collectionCount = 'one';
		}else{
			$collectionWord = 'collections';
		}

		//we want to select all subjects of records which have this as a contibuting group;

		$subjects = $this->_CI->solr->getFacetResult('subject_value_resolved');
		$subjectCount = array();
		foreach($subjects as $subject=>$num){
			$contributorData['subjects'][$subject] = $num;
			$subjectCount[]= $subject;
		}

		$subjectNum = count($subjectCount);
	
		$subjectStr='';
		$subjectWord = 'areas';
		if($subjectNum<1)
		{
			$subjectStr = ".";
		}
		elseif($subjectNum==1)
		{
			$subjectWord = 'area';		
			$subjectStr = ', which covers the subject '.$subjectWord.' <a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subjectCount[0].'">'.$subjectCount[0].'</a>.';
		}
		elseif($subjectNum==2)
		{
			$subjectStr = ', which covers 	'.$subjectNum.' subject '.$subjectWord.' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($groupName).'/subject='.$subjectCount[0].'">'.$subjectCount[0].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($groupName).'/subject='.$subjectCount[1].'">'.$subjectCount[1].'</a>.';
		}	
		else 
		{
			$subjectStr = ', which covers 	'.$subjectNum.' subject '.$subjectWord.' including <a href="'.base_url().'search#!/tab=All/group='.urlencode($groupName).'/subject='.$subjectCount[0].'">'.$subjectCount[0].'</a>, <a href="'.base_url().'search#!/tab=All/group='.urlencode($groupName).'/subject='.$subjectCount[1].'">'.$subjectCount[1].'</a> and <a href="'.base_url().'search#!/tab=All/group='.urlencode($groupName).'/subject='.$subjectCount[2].'">'.$subjectCount[2].'</a>.';	
		}
	

		//we want to select all groups of which have this as a contibuting group;

		$this->_CI->solr->setOpt('fq', 'type:("group")');
		$groups = $this->_CI->solr->executeSearch();
		foreach($groups->{'response'}->{'docs'} as $group){
			$contributorData['groups'][$group->{'list_title'}] = '';
		}

		$groupCount = count($contributorData['groups']);
		if($groupCount==1 || $groupCount==0)
		{
			$groupStr = '';
		}else{
			$groupStr = $groupCount. '	 research groups have been actively involved in collecting data and creating metadata records for the data.';
		}

		$theText = '<p>To date, ' .urlencode($groupName). ' has ';
		if($collectionCount!='no'){
			$theText .=  '<a id="hp-count-collection" href="'.portal_url().'search#!/tab=collection/group='.urlencode($groupName).'">' .$collectionCount .' '.$collectionWord.'</a> in RDA';
		}else{
			$theText .=   ' '.$collectionCount .' '.$collectionWord.' in RDA';
		}
		$theText .= $subjectStr.' ' .$groupStr. ' All the Collections, Parties, Activities and Services associated with ' .urldecode($groupName). '  can be accessed from the Registry Contents box on the right hand side of this page.</p>';

		return $theText;

	}


}