<?php
    namespace nl\naturalis\bioportal;
 	
	/*
	 * Extends QuerySpec to provide dedicated methods for groupByScientificName query
	 */
    final class ScientificNameGroupQuerySpec extends QuerySpec
 	{
		private $_specimensSize;
		private $_specimensFrom;
		private $_specimensSortFields;
		private $_noTaxa = false;

 	    public function __construct () {
            parent::__construct();
        }
        
        /**
         * Set from offset parameter for specimens in groupByScientificName query
         * 
         * Sets the offset within an aggregation of specimens in the groupByScientificName 
         * query. So e.g. if there are 150 specimens for a particular scientific name, this
         * method in combination with setSpecimensSize() allows the user to cycle 
         * through these specimens.
         * 
         * @param integer|string $from
         * @throws \InvalidArgumentException In case $from is not a valid integer
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function setSpecimensFrom ($from) {
        	if (!$this->isInteger($from)) {
        		throw new \InvalidArgumentException('Error: setSpecimensFrom ' .
        			'parameter "' . $from . '" is not an integer.');
        	}
        	$this->_specimensFrom = (int)$from;
        	$this->_querySpec['specimensFrom'] = $this->_specimensFrom;
        	return $this;
        }
        
        /**
         * Set result size parameter for specimens in groupByScientificName query
         *
         * Sets the result size within an aggregation of specimens in the 
         * groupByScientificName query. So e.g. if there are 150 specimens for a 
         * particular scientific name, this method in combination with 
         * setSpecimensFrom() allows the user to cycle through these specimens.
         *
         * @param integer|string $size
         * @throws \InvalidArgumentException In case $size is not a valid integer
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function setSpecimensSize ($size = false) {
        	if (!$this->isInteger($size)) {
        		throw new \InvalidArgumentException('Error: setSpecimensSize ' .
        			'parameter "' . $size . '" is not an integer.');
        	}
        	$this->_specimensSize = (int)$size;
        	$this->_querySpec['specimensSize'] = $this->_specimensSize;
        	return $this;
        }
        
        /**
         * Set sort criterium for specimens in groupByScientificName query
         * 
         * Sort specimens within the aggregation by scientific name in the 
         * groupByScientificName query. Only a single criterium can be set; 
         * use setSpecimenSortFields() to set multiple criteria. Defaults to 
         * ascending direction. Throws an exception (using private bootstrap method) 
         * if either path or direction are invalid.
         * 
         * @param string $path
         * @param string $direction
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function sortSpecimensBy ($path = false, $direction = 'ASC') {
        	$this->_bootstrapSort($path, $direction);
        	$this->_specimenSortFields[] = [
				'path' => $path,
				'sortOrder' => strtoupper($direction),
        	];
        	$this->_querySpec['specimensSortFields'] = $this->_specimenSortFields;
        	return $this;
        }
        
        /**
        * Set multiple sort criteria for specimens in groupByScientificName query
        * 
        * Sets sort based on array of sort criteria. Note that input differs:
        * setSortFields() takes an array with one or more array in which the first value
        * is the path and the second the direction.
        *
        * setSortFields([
        * 	['path', 'direction'],
        *   ['path', 'direction']
        * ]);
        *
        * Uses sortBy() method and its error checking to set criteria.
		* @param array $fields
        * @return \nl\naturalis\bioportal\QuerySpec
        */
        public function setSpecimensSortFields ($fields = []) {
        	$this->_specimensSortFields = [];
        	foreach ($fields as $sortBy) {
        		$this->sortSpecimensBy($sortBy[0], isset($sortBy[1]) ? $sortBy[1] : 'ASC');
        	}
        	return $this;
        }
        
        /**
         * Exclude taxa from groupByScientificName query
         * 
         * Response from groupByScientificName query includes both specimens and taxa. 
         * This method provides the option to exclude taxa. The opposite can be achieve 
         * by setting setSpecimensSize(0).
         * 
         * @param bool $noTaxa
         * @throws \InvalidArgumentException In case $noTaxa is invalid
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function setNoTaxa ($noTaxa = true) {
        	if (!is_bool($noTaxa)) {
        		throw new \InvalidArgumentException('Error: setNoTaxa ' .
        			'parameter should be TRUE (default)/FALSE.');
        	}
        	$this->_noTaxa = $noTaxa;
        	if ($this->_noTaxa) {
        		$this->_querySpec['noTaxa'] = $this->_noTaxa;
        	} else if (isset($this->_querySpec['noTaxa'])) {
        		unset($this->_querySpec['noTaxa']);
        	}
         	return $this;
        }
               
        /**
         * Get QuerySpec specimens from
         *
         * @return integer
         */
        public function getSpecimensFrom () {
        	return $this->_specimensFrom;
        }
        
        /**
         * Get QuerySpec specimens size
         *
         * @return integer
         */
        public function getSpecimensSize () {
        	return $this->_specimensSize;
        }
        
        /**
         * Get QuerySpec specimens sort fields
         *
         * @return string QuerySpec specimen sort fields as json-encoded string
         */
        public function getSpecimensSortFields () {
        	return json_encode($this->_specimensSortFields);
        }

        /**
         * Get QuerySpec specimens return taxa in NBA response?
         *
         * @return bool
         */
        public function isNoTaxa () {
        	return $this->_noTaxa;
        }
 	}
