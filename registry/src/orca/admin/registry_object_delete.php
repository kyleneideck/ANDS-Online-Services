<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';
// Page processing
// -----------------------------------------------------------------------------
	
// Get the record from the database.
$registryObject = getRegistryObject(getQueryValue('key'));
$registryObjectKey = null;
$dataSourceKey = null;
$registryObjectRecordOwner = null;
$registryObjectDataSourceRecordOwner = null;
$dataSource = null;
if( !$registryObject )
{
	responseRedirect('../manage/my_records.php');
}
else
{
	$registryObjectKey = $registryObject[0]['registry_object_key'];
	$dataSourceKey = $registryObject[0]['data_source_key'];
	$dataSource = getDataSources($dataSourceKey, null);
	
	// Get the values that we'll need to check for conditional display and access.
	$registryObjectRecordOwner = $registryObject[0]['record_owner'];
	$registryObjectDataSourceRecordOwner = $dataSource[0]['record_owner'];
	
	// Check access.
	if( !(userIsDataSourceRecordOwner($registryObjectDataSourceRecordOwner) || userIsORCA_ADMIN()) )
	{
		responseRedirect('../manage/my_records.php');
	}
}

if( strtoupper(getPostedValue('action')) == "CANCEL" )
{
	responseRedirect("../view.php?key=".urlencode($registryObjectKey));
}

if( strtoupper(getPostedValue('action')) == "DELETE" )
{
	$actions = '    1 Registry Object deleted.';
	$result = deleteRegistryObject($registryObjectKey);
	if( $result != '' )
	{
		$actions = '    '.$result;
		
	}
	$actions .= deleteSolrHashKey(sha1($registryObjectKey));
    $actions .= queueSyncDataSource($dataSourceKey);
	// Log the datasource activity.
	insertDataSourceEvent($dataSourceKey, "DELETE REGISTRY OBJECT\nKey: ".$registryObjectKey."\n  ACTIONS\n".$actions);
	
	
	responseRedirect('../manage/my_records.php?data_source='.esc($dataSourceKey));
}
// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
if( $registryObject )
{
	
	$registryObjectClass = $registryObject[0]['registry_object_class'];
	$registryObjectType = $registryObject[0]['type'];
	$dataSourceTitle = $dataSource[0]['title'];
	
	
?>

<form id="registry_object_delete" action="registry_object_delete.php?key=<?php printSafe(urlencode($registryObjectKey)) ?>" method="post">
  <table class="formTable" summary="Delete Registry Object">
    <thead>
      <tr>
        <td>&nbsp;</td>
        <td>Delete <?php printSafe($registryObjectClass); ?></td>
      </tr>
    </thead>
    <tbody class="formFields">
      <tr>
        <td class="">Type:</td>
        <td><?php printSafe($registryObjectType) ?></td>
      </tr>
      <tr>
        <td class="">Key:</td>
        <td><?php printSafe($registryObjectKey) ?><input type="hidden" name="key" id="key" value="<?php printSafe($registryObjectKey) ?>"/></td>
      </tr>
      <tr>
        <td class="">Source:</td>
        <td><?php printSafe($dataSourceTitle) ?></td>
      </tr>
      <tr>
        <td class="">Names:</td>
        <td><?php print(getNameHTML($registryObjectKey)) ?></td>
      </tr>
    </tbody>
    <tbody>
      <tr>
        <td/>

        <td><input type="submit" name="action" value="Cancel"/>&nbsp;&nbsp;<input type="submit" name="action" value="Delete"/>&nbsp;&nbsp;</td>
      </tr>
      <tr>
        <td/>
        <td class="formNotes">
        	This <?php printSafe($registryObjectClass); ?> may be re-created with the next harvest from the originating Data Source.<br/>
        	Also note that you may break relations that this <?php printSafe($registryObjectClass); ?> has with other objects in the Registry.<br/>
        </td>
      </tr>
    </tbody>
  </table>

</form>


<?php
}
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';
?>
