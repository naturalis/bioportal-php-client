<?php
    namespace nl\naturalis\bioportal;
    require_once 'autoloader.php';

    header('Content-Type: application/json');


    $c = new Condition('genus', 'LIKE', 'larus');
    $c->_and('species', 'EQUALS', 'pipi');
    $c->_and('species', 'EQUALS', 'papa');
    $c->_or('species', 'LIKE', 'papi');

    $d = new Condition('family', 'LIKE', 'laridae');

    $query = new QuerySpec();
    $query->sortBy('genus', 'desc');
    $query->setFrom(100);
    $query->setSize('10');
    $query->addCondition($c);
    $query->addCondition($d);
    $query->setLogicalOperator('or');

    echo $query->getSpec(false);