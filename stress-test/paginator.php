<?php
    /* This example loops over a large set and times the query for each loop. */

	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';

    // Initialise Client
    $client = new Client();
    
    // Loop size
    $size = 10000;
    
    // Print results every x loops
    $printLoops = 10;
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl('http://145.136.240.125:32065/v2')
    	->setNbaTimeout(30);
    
    // Get max window size
    $max = $client
    	->specimen()
    	->getIndexMaxResultWindow();

	for ($i = 0; $i < $max; $i += $size) {
		
		$condition = new Condition('sourceSystem.code', 'EQUALS', 'BRAMHS');
	    $query = new QuerySpec();
	    $query
	    	->addCondition($condition)
	    	->setSize($size)
	    	->setFrom($i);

	   	// Start loop timer
	    $loopStart = microtime(true);
	    
	    // Get the genera only
	    $client
	    	->specimen()
	    	->setQuerySpec($query)
	    	->query();
	   	
	   	// Start loop timer
	    $loopEnd = round(microtime(true) - $loopStart, 2);
	    	
	    if ($i > 0 && $i % ($size * $printLoops) == 0) {
	    	$currentStats = array_slice($stats, $i / $size - $printLoops, $i / $size);
	    	echo 'Records ' . ($i - ($size * $printLoops) + 1) . " to $i\n";
	    	echo 'min: ' . min($currentStats) . "s\tmax: " . max($currentStats) . "s\taverage: " .
	    		round(array_sum($currentStats) / count($currentStats), 2) . "s\n\n";
	    }
	    
	    // Store to calculate average query time
	    $stats[$i] = $loopEnd;
	    
	}
    