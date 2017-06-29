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

    // Initialise Client
    $client = new Client();
    
    // Number of taxa to query
    $nrTaxa = 1000;
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl('http://145.136.240.125:32065/v2')
    	->setNbaTimeout(30);
    
   	// We're interested in genera with at least two specimens
   	$condition = new Condition('specimenCount', 'GT', 1);
    $query = new ScientificNameGroupQuerySpec();
    $query
    	->addCondition($condition)
    	->sortBy('specimenCount', 'desc')
    	->setSize(1000);
    // Get the genera only
    $data = $client
    	->names()
    	->setQuerySpec($query)
    	->getDistinctValues('specimens.matchingIdentifications.scientificName.genusOrMonomial');
    
    $data = array_slice(json_decode($data, true), 0, $nrTaxa);
    
    echo 'Querying ' . count($data) . " taxa...\n\n"; 
 	
   	// Start script timer
    $scriptStart = microtime(true);
    
    // Loop over genera and emulate BP queries to retrieve data
    foreach ($data as $genus => $count) {
    	
    	$batch = [];
	
	    $condition = new Condition('specimens.matchingIdentifications.defaultClassification.genus', 
	    	'LIKE', "{$genus}");
	    $condition->setOr('specimens.matchingIdentifications.scientificName.genusOrMonomial', 
	    	'LIKE', "{$genus}");
	    
	 	// Names query requires a ScientificNameGroup
	 	$query = new ScientificNameGroupQuerySpec();
	 	
	 	// Get 100 taxa; default sort order is by name	
	 	$query
	 		->addCondition($condition)
	 		->setLogicalOperator('and')
	 		->setSize(100)
	 		->setSpecimensSize(10)
	 		->setSortFields([
	 			['specimens.matchingIdentifications.scientificName.genusOrMonomial', 'asc'],
	 			['specimens.matchingIdentifications.scientificName.specificEpithet', 'asc'],
	 			['specimens.matchingIdentifications.scientificName.infraspecificEpithet', 'asc'],
	 		]
	 	);
			
	   	// Start loop timer
	    $loopStart = microtime(true);
	    
	    // QuerySpecial is used to filter only matching results
	 	$taxa = json_decode($client->names()->setQuerySpec($query)->querySpecial());
	 	
	 	// Number of taxa
	    $totalTaxa = $taxa->totalSize;
	    
	 	// Get total number of specimens
	    $condition = new Condition('identifications.defaultClassification.genus', 'LIKE', 'bombus');
	    $condition->setOr('identifications.scientificName.genusOrMonomial', 'LIKE', 'bombus');
	    
	 	// Regular QuerySpec, as we're querying specimen
	 	$query = new QuerySpec();
	 	
	 	// Same query; retrieve just a single result, we only need the totalSize	
	 	$query
	 		->addCondition($condition)
	 		->setLogicalOperator('and')
	 		->setSize(1)
	 		->setConstantScore();
	 	
	 	// Regular query
	 	$data = json_decode($client->specimen()->setQuerySpec($query)->query());
	 	
	 	// Total number of specimens
	 	$totalSpecimens = $data->totalSize;
	 	
	 	// Append specimen details to $taxa; use batch query
		// First aggregate queries for all specimens
		foreach ($taxa->resultSet as $row) {
			foreach ($row->item->specimens as $item) {
				$condition = new Condition('unitID', 'EQUALS', $item->unitID);
				$query = new QuerySpec();
				$query
					->addCondition($condition)
					->setConstantScore()
					->setSize(1);
				$batch[$item->unitID] = $query;
			}
		}
		
		// Fetch all specimens at once
		if (isset($batch)) {
			$data = $client->specimen()->batchQuery($batch);
		}

		// End NBA timer here
		$loopEnd = round(microtime(true) - $loopStart, 2);
		
		// Store to calculate average query time
		$stats[] = $loopEnd;
		
		// Print query time
		echo "$genus -- $count taxa -- {$loopEnd}s\n";
    }

	$scriptEnd = round(microtime(true) - $scriptStart, 2);
	
	// Print statistics
	echo "\n\nTotal running query time: " . $scriptEnd . "s\n";
	echo "Average query time: " . round(array_sum($stats) / count($stats), 2) . "\n\n";
    
