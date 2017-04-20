# bioportal-php-client

Setup

Save a copy of path/to/bioportal-php-client/config/client.ini.tpl as config/client.ini. 
Settings in the ini file can be overridden in runtime in the application, 
but a valid config.ini file is required!


Usage

Annotated examples are found in path/to/bioportal-php-client/example.



Online documentation and unit testing

Composer can be used to install both PHPDoc and PHPUnit.

1. Download and install Composer
2. cd path/to/bioportal-php-client
2. php composer install
3. A vendor library will appear in your repository containing the following:

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



PHPDoc

Documentation can be generated using phpdoc 2.x. If you have used composer, 
documentation is compiled using the following command:

path/to/bioportal-php-client/vendor/bin/phpdoc run -d path/to/bioportal-php-client/lib/nl/naturalis/bioportal -t path/to/bioportal-php-client/documentation


PHPUnit

The BioPortal client unit tests require phpunit 4.5.x. 
Newer versions do not play well with the MakeGood Eclipse plug-in.
Predefined unit tests are stored in path/to/bioportal-php-client/tests





