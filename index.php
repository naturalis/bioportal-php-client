<?php
    namespace nl\naturalis\bioportal;
    require_once 'lib/nl/naturalis/bioportal/Autoloader.php';

    // Initialise Client
    $client = new Client();
    // Default ini settings (or even complete config!) can be modified if necessary
    $client->setNbaUrl('http://145.136.242.164:8080/v2/');

    // First condition
    // Condition should be initialized with triplet, as per Java client
    $c = new Condition('acceptedName.genusOrMonomial', 'LIKE', 'lar');
    // Cannot replicate ->and and ->or methods of Java client
    // as these are reserved terms; use ->setAnd and ->setOr instead
    $c->setAnd('acceptedName.specificEpithet', 'LIKE', 'fus');

    // Second condition
    $d = new Condition('defaultClassification.kingdom', 'NOT_EQUALS', 'Animalia');
    $d->setAnd('defaultClassification.kingdom', 'NOT_EQUALS', 'Fungi');

    // Initialise QuerySpec
    $query = new QuerySpec();
    // Append search criteria to QuerySpec; methods are identical to Java client
    // Criteria can be chained as per example below
    $query
        ->setFrom(0)
        ->setSize('50') // valid strings are automatically cast to integers
        ->addCondition($c)
        ->addCondition($d)
        ->setLogicalOperator('or')
        ->sortBy('acceptedName.genusOrMonomial', 'asc');
        /* Or add multiple criteria directly using setSortFields
        ->setSortFields([
            ['acceptedName.genusOrMonomial', 'asc'],
            ['acceptedName.specificEpithet', 'asc'],
        ])
        */

    // Set service and pass on QuerySpec
    $client->taxon()->querySpec($query);
    // Print QuerySpec sent to NBA
    echo "QuerySpec:\n" . $client->getQuerySpec() . "\n\n";
    // Print NBA result
    echo "NBA response:\n" . $client->query() . "\n\n";
