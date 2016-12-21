<?php
    namespace nl\naturalis\bioportal;
    require_once 'autoloader.php';

    header('Content-Type: application/json');

    $c = new Condition('genus', 'LIKE', 'larus');
    $c->addAnd('species', 'EQUALS', 'pipi')->addAnd('species', 'EQUALS', 'papa');
    $c->addOr('species', 'LIKE', 'papi');

    $d = new Condition('family', 'LIKE', 'laridae');

    $query = new QuerySpec();
    $query->sortBy('genus', 'desc')
        ->setFrom(100)
        ->setSize('10')
        ->addCondition($c)
        ->addCondition($d)
        ->setLogicalOperator('or');

    echo $query->getSpec(false);