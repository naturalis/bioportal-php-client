<?php
    /* This example script uses a batch query to stress test the NBA. */
    namespace nl\naturalis\bioportal;
    require_once '../lib/nl/naturalis/bioportal/AbstractClass.php';
    require_once '../lib/nl/naturalis/bioportal/Client.php';
    require_once '../lib/nl/naturalis/bioportal/Condition.php';
    require_once '../lib/nl/naturalis/bioportal/QuerySpec.php';

    // Initialise Client
    $client = new Client();
    // Default ini settings can be modified if necessary
    $client->setNbaUrl('http://145.136.242.164:8080/v2/');
    $client->setNbaTimeout(300);

    // Use the shorthand method to fetch all predefined geo areas
    $areas = json_decode($client->getGeoAreas());

    $start = microtime(true);
    // Create QuerySpec for each country that will fetch the specimens within its borders
    echo 'Querying ' . count($areas->Country) . " areas for specimens...\n";
    foreach ($areas->Country as $country) {
        $c = new Condition('gatheringEvent.siteCoordinates.geoShape', 'IN', $country->locality_en);
        $query = new QuerySpec();
        $batch[$country->locality_en] = $query->setSize(10)->addCondition($c);
    }

    // Do the batch query; debug ON (true flag)
    $result = $client->specimen()->batchQuery($batch, true);

    // Print the request time
    echo 'Query took ' . round((microtime(true) - $start), 2) . " seconds\n";

    // Print result
    foreach ($result as $area => $json) {
        $data = json_decode($json);
        if (isset($data->totalSize)) {
            echo $area . ': ' . $data->totalSize . " specimen(s)\n";
        }    }
    echo "\n\n";
    if (isset($result['error'])) {
        echo 'ERROR reported: ' . $result['error'];
    }
    echo "\n\n";
