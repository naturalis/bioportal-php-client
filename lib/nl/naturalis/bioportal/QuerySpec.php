<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\Condition as Condition;
    use nl\naturalis\bioportal\Common as Common;
	use phpDocumentor\Descriptor\Builder\Reflector\Tags\ExampleAssembler;
	
	/*
	 * Getters return values as they are, except when they are arrays. In that
	 * case the return is json-encoded.
	 */
				
    class QuerySpec extends Common
 	{
		protected $_querySpec;
 	    protected $_conditions;
		protected $_from;
		protected $_size;
		protected $_sortFields;
		protected $_fields;
		protected $_logicalOperator;
		protected $_constantScore = false;
		
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
