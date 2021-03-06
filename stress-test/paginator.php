<?php
    /* This example loops over a large set (all Bramhs specimens) and times the query for each loop. */

	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';
	
	// NBA server 
	$nbaTestServer = 'http://145.136.240.125:32065/v2';

    // Running time (in mins); set to 1 for just one loop
    $runningTime = 120;
    
    // Loop size
    $size = 10000;
    
    // Print results every x loops
    $printLoops = 10;
    
    // Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(30);
    
    // Get max window size
    $max = $client
    	->specimen()
    	->getIndexMaxResultWindow();
    
    $scriptStart = microtime(true);
    
    // Time based loop
    while ((microtime(true) - $scriptStart) < ($runningTime * 60)) {
    	
    	$stats = [];

		// Result-based loop
		for ($i = 0; $i <= $max; $i += $size) {
			
			$condition = new Condition('sourceSystem.code', 'EQUALS', 'BRAMHS');
		    $query = new QuerySpec();
		    $query
		    	->addCondition($condition)
		    	->setSize($size)
		    	->setFrom($i);
	
		   	// Start loop timer
		    $loopStart = microtime(true);
		    
		    // Do query
		    $client
		    	->specimen()
		    	->setQuerySpec($query)
		    	->query();
		   	
		   	// End loop timer
		    $loopEnd = round(microtime(true) - $loopStart, 2);
		    	
		    if ($i > 0 && $i % ($size * $printLoops) == 0) {
		    	
		    	// Get stats for loop
		    	$currentStats = array_slice($stats, $i / $size - $printLoops, $i / $size);
		    	
		    	// Print stats
		    	echo 'Records ' . ($i - ($size * $printLoops) + 1) . " to $i\n";
		    	echo 'min: ' . min($currentStats) . "s\tmax: " . max($currentStats) . "s\taverage: " .
		    		round(array_sum($currentStats) / count($currentStats), 2) . "s\n\n";
		    }
		    
		    // Store to calculate average query time
		    $stats[$i] = $loopEnd;
		    
		    $lastCounter = $i;
		    
		}
		
		// Test if there's a last set to print
		if ($lastCounter < $i) {
			
			$lastStats = array_slice($stats, array_search($lastCounter, array_keys($stats)), count($stats));
		    
		    // Print stats
		    echo 'Records ' . ($i - ($size * $printLoops) + 1) . " to $i\n";
		    echo 'min: ' . min($lastStats) . "s\tmax: " . max($lastStats) . "s\taverage: " .
		    	round(array_sum($lastStats) / count($lastStats), 2) . "s\n\n";
		}
		
		echo 'Sum of loop times: ' . array_sum($stats) . "s\n";
		echo 'Script running time: ' . round((microtime(true) - $scriptStart) / 60) . "m\n\n\n";
	
    }
	
    