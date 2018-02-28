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
	$nbaTestServer = 'http://145.136.242.164:8080/v2';

	// Running time (in mins); set to 0.1 for just one loop
    $runningTime = 60;
	
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(500);

    // Use the shorthand method to fetch all predefined geo areas
    $areas = json_decode($client->getGeoAreas());
    if (empty($areas)) {
    	die("No areas found?!\n\n");
    }

    // Loop over the three types of areas
    foreach (['Country', 'Municipality', 'Nature'] as $type) {
    	
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
	    
	    // Queries have been collected, now execute one-by-one
	    foreach ($batch as $area => $query) {
	    	
	    	// Start timer
	    	$start = microtime(true);
	    	
	    	// Fetch data
	    	$data = json_decode($client->specimen()->setQuerySpec($query)->query());
	    	
	    	// Result as expected; print query time in microseconds
		    if (isset($data->totalSize)) {
		    	echo $area . "\t" . $data->totalSize . " specimen(s)\t" . (round((microtime(true) - $start), 5) * 1000) . "ms\n";
		    
		    // Oops?!
		    } else {
		    	echo $area . " produced an error!\n";
		    }
	    }
	    
	    echo "\n\n";
	   
    }	
