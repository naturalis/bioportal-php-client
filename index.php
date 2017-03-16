<?php
    namespace nl\naturalis\bioportal;
    require_once 'lib/nl/naturalis/bioportal/Autoloader.php';

    // Initialise Client
    $client = new Client();
    
    // Default ini settings can be modified if necessary
    $client->setNbaUrl('http://145.136.242.164:8080/v2/');

    // First condition
    // Condition should be initialized with triplet, as per Java client
    $condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
    
    // Cannot replicate ->and and ->or methods of Java client
    // as these are reserved terms; use ->setAnd and ->setOr instead
    $condition
	  	->setAnd('acceptedName.specificEpithet', 'LIKE', 'fus');
    
	// Second condition
	$secondCondition = new Condition('sourceSystem.code', 'EQUALS', 'COL');
    
    // Initialise QuerySpec
    $query = new QuerySpec();
    
    // Append search criteria to QuerySpec; methods are identical to Java client
    $query
        ->setFrom(0)
        ->setSize('50') // valid strings are automatically cast to integers
        ->addCondition($condition)
        ->addCondition($secondCondition)
        ->setLogicalOperator('and') 
        ->setSortFields([
            ['acceptedName.genusOrMonomial'], // 'asc' is default, can be omitted
            ['acceptedName.specificEpithet', 'desc'],
        ]);

    // Set taxon service and pass QuerySpec
    $client->taxon()->querySpec($query);
    
    // Print NBA result
    header('Content-Type: application/json');
    echo $client->query();
