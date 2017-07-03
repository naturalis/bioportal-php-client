<?php
    /* This script retrieves 1000 taxa with specimens from the NBA and performs and
     * querySpecial for each. 
     * 
     * In the loop, ScientificNameGroup is queried for 100 taxa. For each taxon, a maximum of
     * 10 specimens is retrieved. This is a real world example from BioPortal. Only timings are
     * printed, not the results themselves.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';
	
	// NBA server
	$nbaTestServer = 'http://145.136.242.167:8080/v2';

    // Collection name
    $collection = 'botany';
	
	// Running time (in mins); set to 1 for just one loop
    $runningTime = 120;
    	
   	// Start script timer
    $scriptStart = microtime(true);
    
	// Keep track of number of loops
	$loopNr = 1;
	    	
    // Time based loop
    while ((microtime(true) - $scriptStart) < ($runningTime * 60)) {
    	
   		$url  = $nbaTestServer . '/specimen/dwca/dataset/' . $collection;
    	
	    // Write to /dev/null
	    $fp = fopen('/dev/null', 'w');
	 
	    // Init curl
	    $ch = curl_init($url);
	    
	    // Write download file to nowhere really; disable curl timeouts
	    curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		
	    // Get them data!
	    curl_exec($ch);
	    
	    // Close curl and file pointer
	    curl_close($ch);
	    fclose($fp);
	
		// Print statistics
	    echo "Loop number: $loopNr\n";
		echo 'Script running time: ' . round(((microtime(true) - $scriptStart) / 60), 2) . "m\n\n";
	    
		$loopNr++;
   }
