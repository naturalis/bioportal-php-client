<?php
	/* This example script uses a combination of names service and batch queries.
	 *
	 * The names service is queried for 100 Taraxacum species with specimens. 
	 * For each taxon, a short summary of the first 10 specimens is printed.
	 */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';
	
	// Initialise Client
	$client = new Client();
	
	// Default ini settings can be modified if necessary
	$client
		->setNbaUrl('http://145.136.242.164:8080/v2/')
		->setNbaTimeout(10);
    
    // Query names for Taraxacum
    $condition = new Condition('specimens.matchingIdentifications.defaultClassification.genus', 'LIKE', 'taraxacum');
    $condition->setOr('specimens.matchingIdentifications.scientificName.genusOrMonomial', 'LIKE', 'taraxacum');

    // A NameGroupQuerySpec is used for the names service!
    $query = new NameGroupQuerySpec();
    $query
    	->addCondition($condition)
	    ->setFrom(0) // From and size are used to navigate through result sets
	    ->setSize(100);

	// Fetch all 100 taxa
    $data = json_decode($client->names()->querySpec($query)->query());
    
    // Loop over taxa and fetch details for the first 10 specimens
    foreach ($data->resultSet as $row) {
    	echo $row->item->name . "\n";
    	
    	// Create batch queries for first 10 items
    	// Should normally be done with setSpecimensSize(10) in nams service, but that method is currently unavailable
    	// For now, we take the overhead of getting much more than 10 specimens per taxon for granted...
    	$batch = [];
    	foreach (array_slice($row->item->specimens, 0, 10) as $item) {
    		$condition = new Condition('unitID', 'EQUALS', $item->unitID);
    		$query = new QuerySpec();
     		$batch[$item->unitID] = $query->addCondition($condition);
    	}
    	
    	// Fetch data
    	$specimens = $client->specimen()->batchQuery($batch);
    	
    	// Loop over items and print assemblageID and recordBasis
    	foreach ($specimens as $unitId => $json) {
    		$specimen = json_decode($json);
 	    	echo str_repeat(' ', 5) . $specimen->resultSet[0]->item->assemblageID . " - " .
	    		$specimen->resultSet[0]->item->recordBasis . "\n";
    	}
    }
 