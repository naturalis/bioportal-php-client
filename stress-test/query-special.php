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
	$nbaTestServer = 'http://145.136.240.125:32065/v2';

	// Running time (in mins); set to 0.1 for just one loop
    $runningTime = 120;
	
    // Number of taxa to query
    $nrTaxa = 1000;
    
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
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
    
    $genera = array_slice(json_decode($data, true), 0, $nrTaxa);
    
    echo 'Querying ' . count($genera) . " taxa...\n\n"; 
 	
   	// Start script timer
    $scriptStart = microtime(true);
    
	// Keep track of number of loops
	$loopNr = 1;
	    	
    // Time based loop
    while ((microtime(true) - $scriptStart) < ($runningTime * 60)) {
    	
	    // Loop over genera and emulate BP queries to retrieve data
	    foreach ($genera as $genus => $nrTaxa) {
	    	
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
		 	
	    	if (!isset($taxa->totalSize)) {
		 		echo "ERROR! No taxa found for genus $genus\n";
		 		continue;
		 	}
		 	
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
				$nrSpecimens = count($data);
			}
	
			// End NBA timer here
			$loopEnd = round(microtime(true) - $loopStart, 2);
			
			// Store to calculate average query time
			$stats[] = $loopEnd;
			
			// Print query time
			echo "$genus\t$nrTaxa taxa\t$nrSpecimens specimens in batch\t{$loopEnd}s\n";
	    }
	
		// Print statistics
	    echo "\n\nLoop number: $loopNr\n";
	    echo "Average query time: " . round(array_sum($stats) / count($stats), 2) . "\n";
		echo 'Script running time: ' . round((microtime(true) - $scriptStart) / 60) . "m\n\n\n";
		
		$loopNr++;
    }
