<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\Condition as Condition;
    use nl\naturalis\bioportal\Common as Common;

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
		 * 
		 * @param string $condition
		 * @throws \InvalidArgumentException
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
		 * 
		 * @param string $path
		 * @param string $direction
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
		 * 
		 * 
		 * @param array $fields
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
         * 
         * @param int $from
         * @throws \InvalidArgumentException
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
  	     * 
  	     * @param int $size
 	     * @throws \InvalidArgumentException
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

        public function setFields ($fields = []) {
            if (!is_array($fields) || empty($fields)) {
                throw new \InvalidArgumentException('Error: ' .
                	'fields should be a non-empty array');
            }
            $this->_fields = $fields;
            $this->_querySpec['fields'] = $this->_fields;
            return $this;
        }
        
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
        
        public function setSpecimensFrom ($from) {
        	if (!$this->isInteger($from)) {
        		throw new \InvalidArgumentException('Error: setSpecimensFrom ' .
        			'parameter "' . $from . '" is not an integer.');
        	}
        	$this->_specimensFrom = (int)$from;
        	$this->_querySpec['specimensFrom'] = $this->_specimensFrom;
        	return $this;
        }
        
        public function setSpecimensSize ($size = false) {
        	if (!$this->isInteger($size)) {
        		throw new \InvalidArgumentException('Error: setSpecimensSize ' .
        			'parameter "' . $size . '" is not an integer.');
        	}
        	$this->_specimensSize = (int)$size;
        	$this->_querySpec['specimensSize'] = $this->_specimensSize;
        	return $this;
        }
        
        public function sortSpecimensBy ($path = false, $direction = 'ASC') {
        	$this->_bootstrapSort($path, $direction);
        	$this->_specimenSortFields[] = [
				'path' => $path,
				'sortOrder' => strtoupper($direction),
        	];
        	$this->_querySpec['specimensSortFields'] = $this->_specimenSortFields;
        	return $this;
        }
        
        public function setSpecimensSortFields ($fields = []) {
        	$this->_specimensSortFields = [];
        	foreach ($fields as $sortBy) {
        		$this->sortSpecimensBy($sortBy[0], isset($sortBy[1]) ? $sortBy[1] : 'ASC');
        	}
        	return $this;
        }
        
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
        
        public function getFields () {
        	return json_encode($this->_fields);
        }
        
        public function getFrom () {
        	return json_encode($this->_from);
        }
        
        public function getSize () {
            return json_encode($this->_size);
        }

 	    public function getLogicalOperator () {
            return json_encode($this->_logicalOperator);
        }

        public function getConditions () {
            return json_encode($this->_conditions);
        }

        public function getSortFields () {
        	return json_encode($this->_sortFields);
        }
 
        public function isConstantScore () {
        	return $this->_constantScore;
        }
        
        public function getSpecimensFrom () {
        	return json_encode($this->_specimensFrom);
        }
        
        public function getSpecimensSize () {
        	return json_encode($this->_specimensSize);
        }
        
        public function getSpecimensSortFields () {
        	return json_encode($this->_specimensSortFields);
        }

        public function isNoTaxa () {
        	return $this->_noTaxa;
        }
        
        /*
         * Offers client an option to easily check if names service specific
         * criteria have been used.
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
        
        public function getQuerySpec ($encoded = true) {
            if (!empty($this->_querySpec)) {
                ksort($this->_querySpec);
                $d = json_encode($this->_querySpec);
                return $encoded ? urlencode($d) : $d;
            }
            return false;
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
