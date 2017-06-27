<?php
    /* This script retrieves Bombus specimens from the NBA. 
     * 
     * The ScientificNameGroup is queried for 100 taxa. For each taxon, a maximum of
     * 10 specimens is retrieved. This is a real world example from BioPortal.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';

    // Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl('http://145.136.242.167:8080/v2/')
    	->setNbaTimeout(10);

 	// Bombus specimens
    $condition = new Condition('specimens.matchingIdentifications.defaultClassification.genus', 'LIKE', 'bombus');
    $condition->setOr('specimens.matchingIdentifications.scientificName.genusOrMonomial', 'LIKE', 'bombus');
    
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
		
   	// Start timer
    $start = microtime(true);
    
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
 	
 	echo "Species ($totalTaxa) with specimens ($totalSpecimens)\n\n";
 	
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
		
		// End NBA timer here
		$nbaEnd = round(microtime(true) - $start, 2);
		
		// Decode json
		$specimens = array_map('json_decode', $data);
		
		// Loop again over $taxa and append the fetched specimen details
		foreach ($taxa->resultSet as $i => $row) {
			foreach ($row->item->specimens as $j => $item) {
				if (isset($specimens[$item->unitID])) {
					$detail = $specimens[$item->unitID];
					$taxa->resultSet[$i]->item->specimens[$j]->specimenDetails =
						$detail->resultSet[0]->item;
				}
			}
		}
	}
	
	// Print results
	foreach ($taxa->resultSet as $i => $row) {
		echo ucfirst($row->item->name) . 
			' -- ' . $row->item->specimenCount . " specimen(s)\n";
		foreach ($row->item->specimens as $j => $item) {
			echo $item->unitID . 
				(isset($item->specimenDetails->kindOfUnit) ? ' -- ' . $item->specimenDetails->kindOfUnit : '') .
				(isset($item->specimenDetails->preparationType) ? ' -- ' . $item->specimenDetails->preparationType : '') .
				"\n";
		}
		echo "\n";
	}
	
	$scriptEnd = round(microtime(true) - $start, 2);
	
	echo "NBA query time: {$nbaEnd}s\n";
	echo "Total time, including parsing and printing: {$scriptEnd}s\n\n\n";
	
