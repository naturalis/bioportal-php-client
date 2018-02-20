<?php
    /* This script retrieves 1000 (or any numner of) taxa with specimens from the NBA and performs a
     * querySpecial for each. Running time is two hours; can be modified.
     * 
     * In the loop, ScientificNameGroup is queried for 100 taxa. For each taxon, a maximum of
     * 10 specimens is retrieved. This is a real world example from BioPortal. Only running times are
     * printed, not the results themselves.
     */
	namespace nl\naturalis\bioportal;
	
	// For some reason Autoloader doesn't work outside of main directory; use (manual) Loader instead
	require_once '../lib/nl/naturalis/bioportal/Loader.php';

	// NBA server 
	$nbaTestServer = 'http://145.136.242.164:8080/v2';
	
	// Running time (in mins); set to 0.1 for just one loop
    $runningTime = 60;
	
    // Number of taxa to query
    $nrTaxa = 1000;
    
	// Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client
    	->setNbaUrl($nbaTestServer)
    	->setNbaTimeout(500);
    
   	// We're interested in genera with at least two specimens
   	$condition = new Condition('identifications.scientificName.genusOrMonomial', 'NOT_EQUALS');
    $query = new GroupByScientificNameQuerySpec();
    $query
    	->addCondition($condition)
    	->groupSort('count_desc');
 
   	// Get the genera only
    $data = $client
    	->specimen()
    	->setQuerySpec($query)
    	->getDistinctValues('identifications.scientificName.genusOrMonomial');
    
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
		
		    $condition = new Condition('identifications.defaultClassification.genus', 
		    	'CONTAINS', "{$genus}");
		    $condition->setOr('identifications.scientificName.genusOrMonomial', 
		    	'CONTAINS', "{$genus}");
		    
		 	// Query requires a ScientificNameGroup
		 	$query = new GroupByScientificNameQuerySpec();
		 	
		 	// Get 100 taxa; default sort order is by name	
		 	$query
		 		->addCondition($condition)
		 		->setLogicalOperator('and')
		 		->setSize(100)
		 		->setSpecimensSize(10)
		 		->setGroupSort('name_asc')
   				->setFields([
		    		"gatheringEvent.siteCoordinates.geoShape",
			        "identifications.defaultClassification.className",
			        "collectorsFieldNumber",
			        "gatheringEvent.gatheringPersons.fullName",
			        "gatheringEvent.gatheringOrganizations.name",
			        "identifications.defaultClassification.family",
			        "identifications.defaultClassification.genus",
			        "identifications.scientificName.genusOrMonomial",
			        "identifications.defaultClassification.kingdom",
			        "gatheringEvent.localityText",
			        "identifications.defaultClassification.order",
			        "identifications.defaultClassification.phylum",
			        "unitID",
			        "sourceSystem.code",
			        "identifications.scientificName.fullScientificName",
			        "identifications.defaultClassification.specificEpithet",
			        "identifications.scientificName.specificEpithet",
			        "identifications.defaultClassification.infraspecificEpithet",
			        "identifications.scientificName.infraspecificEpithet",
			        "identifications.defaultClassification.subgenus",
			        "identifications.scientificName.subgenus",
			        "identifications.vernacularNames.name",
			        "identifications.typeStatus",
			        "collectionType",
			        "theme",
			        "sourceSystemId",
			        "recordBasis",
			        "kindOfUnit",
			        "preparationType",
			        "assemblageID",
			        "identifications.scientificName.scientificNameGroup"
   				]);
				
		   	// Start loop timer
		    $loopStart = microtime(true);
		    
		    // QuerySpecial is used to filter only matching results
		 	$taxa = json_decode(
		 		$client->specimen()->setQuerySpec($query)->groupByScientificName()
		 	);
		 	
	    	if (!isset($taxa->totalSize)) {
		 		echo "ERROR! No taxa found for genus $genus\n";
		 		continue;
		 	}
		 	
		 	// Number of taxa
		    $totalTaxa = $taxa->totalSize;
		    
		 	// Get total number of specimens
		    $condition = new Condition('identifications.defaultClassification.genus', 
		    	'CONTAINS', "{$genus}");
		    $condition->setOr('identifications.scientificName.genusOrMonomial', 
		    	'CONTAINS', "{$genus}");
		    
		 	// Regular QuerySpec, as we're querying specimen
		 	$query = new QuerySpec();
		 	
		 	// Same query; retrieve just a single result, we only need the totalSize	
		 	$query
		 		->addCondition($condition)
		 		->setLogicalOperator('and')
		 		->setSize(1)
		 		->setConstantScore();
		 	
		 	// Get specimens
		 	$data = json_decode($client->specimen()->setQuerySpec($query)->query());
		 	
		 	// Total number of specimens
		 	$totalSpecimens = $data->totalSize;
		 		
			// End NBA timer here
			$loopEnd = round(microtime(true) - $loopStart, 2);
			
			// Store to calculate average query time
			$stats[] = $loopEnd;
			
			// Print query time
			echo "$genus\t$totalTaxa taxa\t$totalSpecimens specimens\t{$loopEnd}s\n";
	    }
	
		// Print statistics
	    echo "\n\nLoop number: $loopNr\n";
	    echo "Average query time: " . round(array_sum($stats) / count($stats), 2) . "s\n";
		echo 'Script running time: ' . round((microtime(true) - $scriptStart) / 60) . "m\n\n\n";
		
		$loopNr++;
    }
