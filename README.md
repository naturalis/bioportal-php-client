# bioportal-php-client


Composer

1. Download and install Composer
2. cd path/to/bioportal-php-client
2. php composer install
3. A vendor library will appear in your repository containing the following file and folders:

    autoload.php
    bin
    cilex
    composer
    container-interop
    doctrine
    erusev
    herrera-io
    jms
    justinrainbow
    kherge
    monolog
    nikic
    phpcollection
    phpdocumentor
    phpoption
    phpspec
    phpunit
    pimple
    psr
    sebastian
    seld
    symfony
    twig
    zendframework
    zetacomponents



Unit tests

The BioPortal client unit tests require phpunit 4.5.x. 
Newer versions do not play well with the MakeGood Eclipse plug-in.
Predefined unit tests are stored in path/to/bioportal-php-client/tests



PHPDoc

Documentation can be generated using phpdoc 2.x. If you have used composer, 
documentation can be generated using the following command:

path/to/bioportal-php-client/vendor/bin/phpdoc run -d path/to/bioportal-php-client/lib/nl/naturalis/bioportal -t path/to/bioportal-php-client/documentation


