<?php
    /* This script check if media in NBA can actually be loaded.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';
	
	// NBA server
	$nbaTestServer = 'http://145.136.240.125:30076/v2';
	
	// Batch size
	$batchSize = 1000;
	
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(30);
    
   	$condition = new Condition('unitID', 'NOT_EQUALS');
    
    $query = new QuerySpec();
    $query
    	->addCondition($condition)
    	->setFields([
    		'unitID', 
    		'serviceAccessPoints.accessUri',
    		'associatedSpecimenReference'
     	])
    	->setSize($batchSize);
    
    $data = json_decode($client->multimedia()->setQuerySpec($query)->query());
    
    $total = $data->totalSize;

	// Flush buffer so progress is shown properly
	ini_set('implicit_flush', true);
	header('Content-type: text/html; charset=utf-8');
	
    echo "$total media files to be checked\n\n";
    $j = 0;
    
    for ($i = 0; $i <= $total; $i += $batchSize) {
    	
     	// Loop over results in batches
    	$query->setFrom($i);
    	$data = json_decode($client->multimedia()->setQuerySpec($query)->query());
    	
    	foreach ($data->resultSet as $result) {
    		
     		// Get headers, so we don't have to download image
    		$headers = @get_headers($result->item->serviceAccessPoints[0]->accessUri);

			if (!$headers || !strpos($headers[0], '200')) {
			    echo $result->item->associatedSpecimenReference . "\t" .
			    	$result->item->serviceAccessPoints[0]->accessUri . " cannot be displayed\n";
			    $j = 0;
			}
    	}
    }
