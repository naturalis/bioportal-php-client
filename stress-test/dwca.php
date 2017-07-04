<?php
    /* This script generates a DwCA file of a specified collection (default: botany). 
     * Running time is two hours; can be modified.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';
	
	// NBA server
	$nbaTestServer = 'http://145.136.240.125:32065/v2';

    // Collection name
    $collection = 'botany';
    
	// Running time (in mins); set to 1 for just one loop
    $runningTime = 120;
    	
   	// Start script timer
    $scriptStart = microtime(true);
    
	// Keep track of number of loops
	$loopNr = 1;
	    	
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(30)
    	->setNbaDwcaDownloadDirectory('/tmp');
	
	// Time based loop
    while ((microtime(true) - $scriptStart) < ($runningTime * 60)) {
 		// Print statistics
	    echo "Loop number: $loopNr\n";
   		echo 'Archive written to: '. $client->specimen()->dwcaGetDataSet($collection) . "\n";
	    echo 'Script running time: ' . round(((microtime(true) - $scriptStart) / 60), 2) . "m\n\n";
	    
		$loopNr++;
   }
