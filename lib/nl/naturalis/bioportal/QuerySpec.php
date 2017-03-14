<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\Condition as Condition;
    use nl\naturalis\bioportal\Common as Common;
	use phpDocumentor\Descriptor\Builder\Reflector\Tags\ExampleAssembler;
	
	/*
	 * Getters return values as they are, except when they are arrays. In that
	 * case the return is json-encoded.
	 */
				
    final class QuerySpec extends Common
 	{
		private $_querySpec;
 	    private	$_conditions;
		private $_from;
		private $_size;
		private $_sortFields;
		private $_fields;
		private $_logicalOperator;
		private $_constantScore = false;
		
		// Used exclusively for names service
		private $_specimensSize;
		private $_specimensFrom;
		private $_specimensSortFields;
		private $_noTaxa = false;

 	    public function __construct() {
            parent::__construct();
        }
        
		/**
		 * Add Condition object to QuerySpec
		 * 
		 * @param object $condition
		 * @throws \InvalidArgumentException In case of invalid Condition object
		 * @return \nl\naturalis\bioportal\QuerySpec
		 */
        public function addCondition ($condition = false) {
            if (!($condition instanceof Condition)) {
                throw new \InvalidArgumentException('Error: invalid condition, ' .
                	'should be created using the Condition class.');
            }
            $this->_conditions[] = json_decode($condition->getCondition(), true);
            $this->_querySpec['conditions'] = $this->_conditions;
            return $this;
        }
        
		/**
		 * Set single sort criterium
		 * 
		 * Set sort field and order for QuerySpec. Only a single criterium can be set;
		 * use setSortFields() to set multiple criteria. Defaults to ascending direction.
		 * Throws an exception (using private bootstrap method) if either path or direction 
		 * are invalid.
		 * 
		 * @param string $path NBA path to field
		 * @param string $direction 
		 * @see \nl\naturalis\bioportal\QuerySpec::setSortFields()
		 * @return \nl\naturalis\bioportal\QuerySpec
		 */
        public function sortBy ($path = false, $direction = 'ASC') {
            $this->_bootstrapSort($path, $direction);
            $this->_sortFields[] = [
                'path' => $path,
                'sortOrder' => strtoupper($direction),
            ];
            $this->_querySpec['sortFields'] = $this->_sortFields;
            return $this;
        }
        
		/**
		 * Set multiple sort criteria
		 * 
		 * Sets sort based on array of sort criteria. Note that input differs: 
		 * setSortFields() takes an array with one or more array in which the first value 
		 * is the path and the second the direction. 
		 * 
		 * setSortFields([
		 * 	 ['path', 'direction'], 
		 *   ['path', 'direction']
		 * ]);
		 * 
		 * Uses sortBy() method and its error checking to set criteria.
		 * 
		 * @param array $fields Input format is different from sortBy(); see description 
		 * @return \nl\naturalis\bioportal\QuerySpec
		 */
        public function setSortFields ($fields = []) {
        	$this->_sortFields = [];
            foreach ($fields as $sortBy) {
                $this->sortBy($sortBy[0], isset($sortBy[1]) ? $sortBy[1] : 'ASC');
            }
            return $this;
        }
		
        /**
         * Set QuerySpec from offset parameter
         * 
         * @param int $from
         * @throws \InvalidArgumentException In case of invalid $from
         * @return \nl\naturalis\bioportal\QuerySpec
         */
 	 	public function setFrom ($from = false) {
 	     	if (!$this->isInteger($from)) {
                throw new \InvalidArgumentException('Error: from parameter "' . 
                	$from . '" is not an integer.');
 	     	}
 	     	$this->_from = (int)$from;
 	     	$this->_querySpec['from'] = $this->_from;
            return $this;
 	 	}

 	    /**
  	     * Set QuerySpec result size parameter
  	     * 
  	     * @param int $size
 	     * @throws \InvalidArgumentException In case of invalid $size
 	     * @return \nl\naturalis\bioportal\QuerySpec
 	     */
 	    public function setSize ($size = false) {
 	     	if (!$this->isInteger($size)) {
                throw new \InvalidArgumentException('Error: size parameter "' . 
                	$size . '" is not an integer.');
 	     	}
 	     	$this->_size = (int)$size;
 	     	$this->_querySpec['size'] = $this->_size;
            return $this;
 	    }

 	    /**
 	     * Set QuerySpec logical operator
 	     * 
 	     * Individual Condition objects are combined using 'AND' or 'OR'
 	     * parameter.
 	     * 
 	     * @param string $operator
 	     * @throws \UnexpectedValueException In case of invalid operator
 	     * @return \nl\naturalis\bioportal\QuerySpec
 	     */
        public function setLogicalOperator ($operator = false) {
            if (!in_array(strtoupper($operator), self::$logicalOperators)) {
                throw new \UnexpectedValueException('Error: ' .
                	'logical operator should match ' .
                    implode(', ', self::$logicalOperators));
            }
            $this->_logicalOperator = strtoupper($operator);
            $this->_querySpec['logicalOperator'] = $this->_logicalOperator;
            return $this;
        }

        /**
         * Set QuerySpec fields to return
         * 
         * Returns only thw fields specified in the $fields array, rather than
         * the full response.
         * 
         * @param array $fields
         * @throws \InvalidArgumentException In case $fields is invalid
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function setFields ($fields = []) {
            if (!is_array($fields) || empty($fields)) {
                throw new \InvalidArgumentException('Error: ' .
                	'fields should be a non-empty array');
            }
            $this->_fields = $fields;
            $this->_querySpec['fields'] = $this->_fields;
            return $this;
        }
        
        /**
         * Set QuerySpec constant score
         * 
         * Override any settings in the Condition object(s) and disable scoring.
         * 
         * @param string $constant
         * @throws \InvalidArgumentException In case $constant is invalid
         * @return \nl\naturalis\bioportal\QuerySpec
         */
        public function setConstantScore ($constant = true) {
        	if (!is_bool($constant)) {
        		throw new \InvalidArgumentException('Error: condition constant ' .
        			'score parameter should be TRUE (default)/FALSE.');
        	}
        	$this->_constantScore = $constant;
        	if ($this->_constantScore) {
        		$this->_querySpec['constantScore'] = $this->_constantScore;
        	} else if (isset($this->_querySpec['constantScore'])) {
        		unset($this->_querySpec['constantScore']);
        	}
         	return $this;
        }
        
        /**
         * Set from offset parameter for specimens in names service
         * 
         * Sets the offset within an aggregation of specimens in the names service.
         * So e.g. if there are 150 specimens for a particular scientific name, this
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
         * Set result size parameter for specimens in names service
         *
         * Sets the result size within an aggregation of specimens in the names service.
         * So e.g. if there are 150 specimens for a particular scientific name, this
         * method in combination with setSpecimensFrom() allows the user to cycle 
         * through these specimens.
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
         * Set sort criterium for specimens in names service
         * 
         * Sort specimens within the aggregation by scientific name in names service.
         * Only a single criterium can be set; use setSpecimenSortFields() to set 
         * multiple criteria. Defaults to ascending direction. Throws an exception 
         * (using private bootstrap method) if either path or direction are invalid.
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
        * Set multiple sort criteria for specimens in names service
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
         * Exclude taxa from names service query
         * 
         * Response from names service includes both specimens and taxa. This method
         * provides the option to exclude taxa. The opposite can be achieve by setting
         * setSpecimensSize(0).
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
         * Check if QuerySpec contains names service-only criteria
         *
         * Offers the option to check if names service specific criteria
         * have been used. If so, no other service may be set in the CLient.
         *
         * @return boolean
         */
        public function usesSpecimensCriteria () {
        	foreach ([$this->_specimensSize, $this->_specimensFrom,
        		$this->_specimensSortFields] as $var) {
        		if (isset($var)) {
        			return true;
       			}
       		}
      		return $this->isNoTaxa();
        }
        
        /**
         * Get QuerySpec
         * 
         * Gets QuerySpec either as json or url-encoded json (default).
         * 
         * @param bool $encoded Url encode QuerySpec json-encoded string?
         * @return string|boolean
         */
        public function getQuerySpec ($encoded = true) {
        	if (!empty($this->_querySpec)) {
        		ksort($this->_querySpec);
        		$d = json_encode($this->_querySpec);
        		return $encoded ? urlencode($d) : $d;
        	}
        	return false;
        }
        
        /**
         * Get QuerySpec fields
         * 
         * @return string Fields as json-encoded string
         */
        public function getFields () {
        	return json_encode($this->_fields);
        }
        
        /**
         * Get QuerySpec from
         * 
         * @return integer
         */
        public function getFrom () {
        	return $this->_from;
        }
        
        /**
         * Get QuerySpec size
         *
         * @return integer
         */
        public function getSize () {
            return $this->_size;
        }

        /**
         * Get QuerySpec from
         *
         * @return string
         */
        public function getLogicalOperator () {
            return $this->_logicalOperator;
        }

        /**
         * Get QuerySpec conditions
         *
         * @return string QuerySpec conditions as json-encoded string
         */
        public function getConditions () {
            return json_encode($this->_conditions);
        }

        /**
         * Get QuerySpec sort fields
         *
         * @return string QuerySpec sort fields as json-encoded string
         */
        public function getSortFields () {
        	return json_encode($this->_sortFields);
        }
 
        /**
         * Get QuerySpec constant score
         *
         * @return bool 
         */
        public function isConstantScore () {
        	return $this->_constantScore;
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
        
         private function _bootstrapSort ($path, $direction) {
        	if (!$path || !is_string($path)) {
        		throw new \InvalidArgumentException('Error: ' .
        			'sort by statement incomplete! Statement should contain the path ' .
        			'to the field to sort on (default sort order is ASC), ' .
        			'or a duplet: path.to.field, ASC/DESC".');
        	}
        	if (!in_array(strtoupper($direction), self::$sortDirections)) {
        		throw new \UnexpectedValueException('Error: ' .
        			'sort direction should match one of the ' .
        			'following: ' . implode(', ', self::$sortDirections));
        	}
        	return true;
         }

 	}
