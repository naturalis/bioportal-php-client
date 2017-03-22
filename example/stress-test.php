<?php
    /* This example script uses a batch query to stress test the NBA. 
     * 
     * All areas are retrieved. Per area type, a batch query with all
     * areas for that type is compiled. The number of specimens
     * found per area is printed to screen.
     */
    namespace nl\naturalis\bioportal;
    require_once '../lib/nl/naturalis/bioportal/Common.php';
    require_once '../lib/nl/naturalis/bioportal/Client.php';
    require_once '../lib/nl/naturalis/bioportal/Condition.php';
    require_once '../lib/nl/naturalis/bioportal/QuerySpec.php';

    // Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl('http://145.136.242.164:8080/v2/')
    	->setNbaTimeout(10);

    // Use the shorthand method to fetch all predefined geo areas
    $areas = json_decode($client->getGeoAreas());
    if (empty($areas)) {
    	die('No areas found?!');
    }

    // Loop over the three types of areas
    foreach(['Country', 'Municipality', 'Nature'] as $type) {
    	
    	// Start timer
    	$start = microtime(true);
    	
    	// Initialise batch array
    	$batch = [];
    	
	    echo 'Querying ' . count((array)$areas->{$type}) . " areas of type $type for specimens...\n";
	    foreach ($areas->{$type} as $area) {
	    	
	    	// Create QuerySpec for each area that will fetch the specimens within its borders
	    	$c = new Condition('gatheringEvent.siteCoordinates.geoShape', 'IN', $area->locality->en);
	        $query = new QuerySpec();
	        
	        // Add QuerySpec to batch array
	        $batch[$area->locality->en] = $query->setSize(5)->addCondition($c);
	    }
	    
	    // Do the batch query
	    $result = $client->specimen()->batchQuery($batch);
	    
	    // Print the request time
	    echo 'Query took ' . round((microtime(true) - $start), 2) . " seconds\n\n";

	    // Print result
	    foreach ($result as $area => $json) {
	    	$data = json_decode($json);
	    	// Result as expected
	    	if (isset($data->totalSize)) {
	    		echo $area . ': ' . $data->totalSize . " specimen(s)\n";
	    	// No result; add to errors array
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
    