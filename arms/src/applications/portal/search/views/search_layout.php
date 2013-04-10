<?php $this->load->view('rda_header');?>
<div class="container less_padding">
<div id="searchmap" class="hide"></div>
<div class="main">
	<div id="searchmap" class="hide"></div>
	<div class="page_title">
		<h1 id="selected_subject"></h1>
		<h3 id="selected_group"></h3>
	</div>
	<div class="tabs" class="hide">
		<?php
			$tabs = array(
				'All' => array('facet_value'=>'all','display'=>'All','selected'=>true, 'count'=>0),
				'collection' => array('facet_value'=>'collection','display'=>'Collections','selected'=>false, 'count'=>0),
				'party' => array('facet_value'=>'party','display'=>'Parties','selected'=>false, 'count'=>0),
				'activity' => array('facet_value'=>'activity','display'=>'Activities','selected'=>false, 'count'=>0),
				'service' => array('facet_value'=>'service','display'=>'Services','selected'=>false, 'count'=>0)
			);
			
			foreach($tabs as $t){
				if($t['selected']){
					$s = 'current';
				}else $s = '';
				echo "<a href='javascript:;' class='filter ".$s."' filter_type='tab' filter_value=".$t['facet_value'].">".$t['display']."</a>";
			}
		?>
		
		<!-- <a href="javascript:;" class="toggle_sidebar"></a>	 -->
		<div class="clear"></div>
	</div>
	
	<div class="pagination"></div>
	<div id="search-result"></div>
	<div class="pagination"></div>
<div id="collection_explanation" class="hide">
	<strong>Collection</strong><br />
	Research dataset or collection of research materials.
</div>
<div id="activity_explanation" class="hide">
	<strong>Activity</strong><br />
	Project or program that creates research datasets or collections.
</div>
<div id="service_explanation" class="hide">
	<strong>Service</strong><br />
	Service that supports the creation or use of research datasets or collections.
</div>
<div id="party_explanation" class="hide">
	<strong>Party</strong><br />
	Researcher or research organisation that creates or maintains research datasets or collections.
</div>

</div><!-- main -->
<div class="sidebar">
	<div class="widget facet_subjects">
	<h3 class="widget_title">Subjects</h3>
	<div id="browse-subjects-container">
		<a href="javascript:;" id="browse-more-subject">Browse More Subjects</a>
	</div>
		<ul id="top_concepts"></ul>
	</div>
	<div id="facet-result"></div>				
</div><!-- sidebar -->				
<div class="container_clear"></div>
<!-- <div class="border"></div> -->

<script type="text/x-mustache" id="search-result-template">
{{#docs}}
	<div class="post clear" ro_id="{{id}}">
		{{#logo}}
			<img src="{{logo}}" class="logo right"/>
		{{/logo}}
		<img src="<?php echo base_url();?>assets/img/{{class}}.png" class="class_icon icontip_{{class}}" type="{{class}}"/>
		<a href="<?php echo base_url();?>{{slug}}" class="title">{{display_title}}</a>
		<div class="excerpt">
			{{description}}
		</div>
	</div>
{{/docs}}
</script>

<script type="text/x-mustache" id="pagination-template">
<div class="results_navi">
	<div class="results">{{numFound}} results ({{timeTaken}} seconds)</div>
	{{{pagination}}}
	<div class="clear"></div>
</div>
</script>

<script type="text/x-mustache" id="facet-template">
{{#facet_result}}
<div class="widget facet_{{facet_type}}">
	<h3 class="widget_title">{{label}}</h3>
	<ul>
		{{#values}}
			<li><a href="javascript:;" class="filter" filter_type="{{facet_type}}" filter_value="{{title}}">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
{{/facet_result}}
</script>

<script type="text/x-mustache" id="top-level-template">
{{#topConcepts}}
	<li><a href="javascript:;" class="filter" filter_type="subject_vocab_uri" filter_value="{{uri}}">{{prefLabel}} ({{collectionNum}})</a></li>
{{/topConcepts}}
</script>

</div>
<?php $this->load->view('rda_footer');?>