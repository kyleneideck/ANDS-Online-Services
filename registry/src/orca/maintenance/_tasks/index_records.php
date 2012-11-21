<?php
global $solr_url;
$solr_update_url = $solr_url.'update';
$totalCount = 0;
$chunkSize = 49;
function task_index_records($task)
{
	global $solr_url, $solr_update_url;
	$taskId = $task['task_id'];
	$message = '';
	$dataSourceKey = $task['data_source_key'];
	$registryObjectKeys = $task['registry_object_keys'];
	$totalCount = 0;
	$chunkSize = 49;
	$solr_update_url = $solr_url.'update';

	// Single data source
	if($dataSourceKey != '')
	{
		$message .= "clearing Datasource Index\n";
		$result =  clearDS($dataSourceKey);
		$message .= addPublishedSolrIndexForDatasource($dataSourceKey);
		$message .= addDraftSolrIndexForDatasource($dataSourceKey);
	}
	else
	{
		// Index all data sources
		$ds = getDataSources(null, null);
		$ds[] = array('data_source_key'=>'PUBLISH_MY_DATA');
		foreach($ds AS $datasource)
		{
			// Improve responsiveness by creating subtasks per datasource
			addNewTask($task['method'], "Subtask generated by index entire registry: " . $taskId, '', $datasource['data_source_key'], null);
			/*		
			$dataSourceKey = $datasource['data_source_key'];
			$message .= "clearing Datasource Index\n";
			$result =  clearDS($dataSourceKey);
			$message .= addPublishedSolrIndexForDatasource($dataSourceKey);
			$message .= addDraftSolrIndexForDatasource($dataSourceKey);
			*/
		}
	}

	$message .= "\ncompleted! update to ".$solr_update_url;
	return $message;
}

?>