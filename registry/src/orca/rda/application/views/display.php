<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/ 
?>
<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>

<div id="search-result">
	<form method="post" accept-charset="utf-8" action="http://devl.ands.org.au:8983/solr/db/select/" />
		<textarea name="q" cols=100 rows=30><?php echo $q;?></textarea>
		<input type="hidden" name="wt" value="xml"/>
		<input type="submit"/>
	</form>
</div>

<?php $this->load->view('tpl/footer');?>