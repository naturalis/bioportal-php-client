<?php
    /* This example script uses a batch query to stress test the NBA. */
    namespace nl\naturalis\bioportal;
    require_once '../lib/nl/naturalis/bioportal/AbstractClass.php';
    require_once '../lib/nl/naturalis/bioportal/Client.php';
    require_once '../lib/nl/naturalis/bioportal/Condition.php';
    require_once '../lib/nl/naturalis/bioportal/QuerySpec.php';

    // Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client->setNbaUrl('http://145.136.242.164:8080/v2/');
    $client->setNbaTimeout(10);

    // Use the shorthand method to fetch all predefined geo areas
    $areas = json_decode($client->getGeoAreas());

    // Loop over the three types of areas
    foreach(['Country', 'Municipality', 'Nature'] as $type) {
    	$start = microtime(true);
    	$batch = [];
    	
    	 // Create QuerySpec for each area that will fetch the specimens within its borders
	    echo 'Querying ' . count((array)$areas->{$type}) . " areas of type $type for specimens...\n";
	    foreach ($areas->{$type} as $area) {
	        $c = new Condition('gatheringEvent.siteCoordinates.geoShape', 'IN', $area->locality->en);
	        $query = new QuerySpec();
	        $batch[$area->locality->en] = $query->setSize(5)->addCondition($c);
	    }
	    
	    // Do the batch query
	    $result = $client->specimen()->batchQuery($batch);
	    
	    // Print the request time
	    echo 'Query took ' . round((microtime(true) - $start), 2) . " seconds\n\n";

	    // Print result
	    foreach ($result as $area => $json) {
	    	$data = json_decode($json);
	    	if (isset($data->totalSize)) {
	    		echo $area . ': ' . $data->totalSize . " specimen(s)\n";
	    	} else {
	    		$errors[$area] = $json;
	    	}
	    }
	    echo "\n\n";
    }
    
    // Print the ones that failed
    if (isset($errors)) {
    	echo "Errors:\n";
    	foreach ($errors as $area => $json) {
    		echo $area . ': ' . "$json\n";
    	}
    }
    echo "\n\n";
    