<?php
    /* All geo areas in NBA are retrieved. Per area type, a batch query with all
     * areas for that type is compiled. Running time is two hours; can be modified.
     * 
     * The number of specimens found per area is printed to screen for the
     * first loop.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';

	// NBA server 
	$nbaTestServer = 'http://145.136.240.125:32065/v2';

	// Running time (in mins); set to 0.1 for just one loop
    $runningTime = 120;
	
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(30);

    // Use the shorthand method to fetch all predefined geo areas
    $areas = json_decode($client->getGeoAreas());
    if (empty($areas)) {
    	die('No areas found?!');
    }

    $scriptStart = microtime(true);
    
	// Keep track of number of loops
	$loopNr = 1;
	    	
    // Time based loop
    while ((microtime(true) - $scriptStart) < ($runningTime * 60)) {
    	
	    // Loop over the three types of areas
	    foreach (['Country', 'Municipality', 'Nature'] as $type) {
	    	
	    	// Start timer
	    	$start = microtime(true);
	    	
	    	// Initialise batch
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
	
		    // Print result (only on first iteration)
		    if ($loopNr == 1) {
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
		    
			    // Print the ones that failed
			    if (isset($errors)) {
			    	echo "Errors:\n";
			    	foreach ($errors as $area => $json) {
			    		echo $area . ': ' . "$json\n";
			    	}
			    }
			    echo "\n\n";
		    }
		    
		    $printResults = false;
	    }	
	    
	    echo "Loop number: $loopNr\n";
		echo 'Script running time: ' . round(((microtime(true) - $scriptStart) / 60), 2) . "m\n\n\n";
	    
	    $loopNr++;
    }
    