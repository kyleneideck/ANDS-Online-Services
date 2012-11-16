<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
    <head>
        <title>MAP WIDGET EXAMPLE</title>

        <script type="text/javascript" src="<?=current_protocol();?>maps.google.com/maps/api/js?sensor=false&libraries=drawing"></script>
	    <script src='<?=current_protocol();?>ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js'></script>
	    
	    <link rel="stylesheet" type="text/css" href="<?=asset_url('css/location_capture_widget.css');?>" />
    </head>
<body>

<div style="width: 500px; margin:auto;">
	
	<h2>My Institution's Dataset Registration Form</h2>
	<form id="myform">
		
		<p>
		Dataset Identifier: <br/>
		<input type="text" id="datasetId" size="80" />
		
		</p>
		
		<p>
		Dataset Name: <br/>
		<input type="text" id="datasetName" size="80" />
		</p>
		
		<p>
		Location: <i>(click and draw a point or region | search for a place name)</i><br/>
		<script type="text/javascript">
			mapInputFieldId = 'geoLocation';
			//latLon = '';
			//mctServicePath = 'http://test.ands.org.au/registry/orca/services/gazetteer_jsonp.php';
		</script>
		<script type="text/javascript" src="<?=asset_url('js/location_capture_widget.js');?>"></script>

		</p>
		
		<p align="center">
			<button id="btnSubmit">Submit this form...</button>
		</p>
	</form>
	

</div>

<script type="text/javascript">
// Demo script
$('#btnSubmit').click(function(e){
	
	e.preventDefault();
	var message = 	"Form data currently contains the following fields:" + "\n"
				+	"==========" + "\n"
				+	"datasetId: " + $('#datasetId').val() + "\n\n"
				+	"datasetName: " + $('#datasetName').val() + "\n\n"
				+	"geoLocation: " + $('#geoLocation').val() + "\n\n";
	alert(message);
	
});
</script>

</body>
</html>