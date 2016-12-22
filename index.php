<?php
    namespace nl\naturalis\bioportal;
    require_once 'autoloader.php';

    // First condition
    // Condition should be initialized with triplet, as per Java client
    $c = new Condition('acceptedName.genusOrMonomial', 'LIKE', 'lar');
    // Cannot replicate ->and and ->or methods of Java client
    // as these are reserved terms; use ->addAnd and ->addOr instead
    $c->addAnd('acceptedName.specificEpithet', 'LIKE', 'fus');

    // Second condition
    $d = new Condition('defaultClassification.kingdom', 'NOT_EQUALS', 'Animalia');
    $d->addAnd('defaultClassification.kingdom', 'NOT_EQUALS', 'Fungi');

    // Initialise QuerySpec
    $query = new QuerySpec();
    // Append search criteria to QuerySpec; methods are identical to Java client
    // Criteria can be chained as per example below
    $query->sortBy('acceptedName.genusOrMonomial', true)
        ->setFrom(0)
        ->setSize('50')
        ->addCondition($c)
        ->addCondition($d)
        ->setLogicalOperator('or');

    // Initialize Client
    $client = new Client();
    // Set service and pass on QuerySpec
    $client->taxon()->querySpec($query);
    // Print QuerySpec sent to NBA
    echo '<p><u>QuerySpec</u>:<br>' . $client->getQuerySpec() . '</p>';
    // Print NBA result
    echo '<p><u>NBA response</u>:<br>' . $client->query() . '</p>';
