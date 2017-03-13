<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\Common as Common;
    
    /*
     * A condition is built from one or multiple statements. In its simplest form,
     * a condition equals a statement. A condition can also represent a nested set of 
     * and/or statements. Each statement is evaluated before it is appended to the condition.
     */
 	final class Condition extends Common
 	{
        private $_field;
        private $_operator;
        private $_value;
        private $_not;
        private $_boost;
        private $_constantScore = false;
        private $_statement = [];
        private $_condition = [];

        
        /**
         * Constructor
         * 
         * Condition is initialised with field, operator and value, so the constructor
         * has to bootstrap these. Values are set through private methods, which throw
         * exceptions if field or operator are empty or if operator does not match 
         * values in nl\naturalis\bioportal\Common::operators. Value can be omitted _only_ 
         * in the specific case of EQUALS/NOT_EQUALS. When the bootstrap is passed, 
         * $_condition is set.
         * 
         * @param string $field
         * @param string $operator
         * @param unknown $value
         * @return void
         */
        public function __construct ($field = false, $operator = false, $value = null) {
            parent::__construct();
            $this->_bootstrap($field, $operator, $value);
            $this->_setCondition();
        }

        /**
         * Append AND condition
         * 
         * Mirrors functionality in Java client, which allows user to pass either
         * a field-operator-value triplet or a previously instantiated Condition object.
         * Normal use would be the triplet. When the bootstrap is passed, 
         * $_condition is set.
         * 
         * @param string|object $fieldOrCondition Field or previously created 
         * Condition object
         * @param string $operator Operator (when field is set)
         * @param unknown $value Value (when field is set)
         * @throws \InvalidArgumentException In case Condition object is invalid
         * @return \nl\naturalis\bioportal\Condition
         */
        public function setAnd ($fieldOrCondition, $operator = false, $value = null) {
        	// Allow setting a previously constructed Condition, cf Java client
        	if (is_object($fieldOrCondition)) {
        		if (!($fieldOrCondition instanceof Condition)) {
        			throw new \InvalidArgumentException('Error: invalid AND condition.');
        		}
        		$this->_condition['and'][] = 
        			json_decode($fieldOrCondition->getCondition(), true);
        	// Setting an AND condition the regular way
        	} else {
	        	$this->_bootstrap($fieldOrCondition, $operator, $value);
	            $this->_condition['and'][] = $this->_setStatement();
        	}
        	return $this;
        }

        /**
         * Append OR condition
         *
         * Mirrors functionality in Java client, which allows user to pass either
         * a field-operator-value triplet or a previously instantiated Condition object.
         * Normal use would be the triplet. When the bootstrap is passed, 
         * $_condition is set.
         * 
         * @param string|object $fieldOrCondition Field or previously created 
         * Condition object
         * @param string $operator Operator (when field is set)
         * @param unknown $value Value (when field is set)
         * @throws \InvalidArgumentException In case Condition object is invalid
         * @return \nl\naturalis\bioportal\Condition
         */
        public function setOr ($fieldOrCondition, $operator = false, $value = null) {
 	    	// Allow setting a previously constructed Condition, cf Java client
 	    	if (is_object($fieldOrCondition)) {
 	    		if (!($fieldOrCondition instanceof Condition)) {
 	    			throw new \InvalidArgumentException('Error: invalid OR condition.');
 	    		}
 	    		$this->_condition['or'][] = 
 	    			json_decode($fieldOrCondition->getCondition(), true);
 	    	// Setting an OR condition the regular way
 	    	} else {
 	    	 	$this->_bootstrap($fieldOrCondition, $operator, $value);
            	$this->_condition['or'][] = $this->_setStatement();
 	    	}
 	    	return $this;
 	    }
 	    
  	    /**
 	     * Overrides field in existing Condition
 	     * 
 	     * Mirrors functionality in Java client. A bit of a theoretical method,
 	     * as normally one would simply create a new triplet.
 	     * 
 	     * @param string $field
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function setField ($field = null) {
 	    	$this->_setField($field);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /**
 	     * Overrides operator in existing Condition
 	     *
 	     * Mirrors functionality in Java client. A bit of a theoretical method,
 	     * as normally one would simply create a new triplet.
 	     *
 	     * @param string $operator
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function setOperator ($operator = null) {
 	    	$this->_setOperator($operator);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /**
 	     * Overrides value in existing Condition
 	     *
 	     * Mirrors functionality in Java client. A bit of a theoretical method,
 	     * as normally one would simply create a new triplet.
 	     *
 	     * @param unknown $value
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function setValue ($value = null) {
 	    	$this->_setValue($value);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /**
  	     * Sets a negated condition 
  	     * 
  	     * Conditions can be created using negative operators (NOT_EQUALS, etc), 
 	     * or the entire condition can be negated. This flexibility can lead to 
 	     * very complex queries; see the Java client documentation for further 
 	     * information. Sets a negated condition if parameter is empty or equals 'NOT';
  	     * use any other value (e.g. FALSE) to remove the negation.
	     * 
 	     * @param string $not
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function setNot ($not = 'NOT') {
  	    	$this->_not = strtoupper($not) == 'NOT' ? 'NOT' : false;
  	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
  	    /**
  	     * Set boost factor
  	     * 
  	     * Set the Elastic boost factor to increase or reduce the "weight"
  	     * of the Condition.
  	     * 
  	     * @param float $boost
  	     * @throws \InvalidArgumentException In case of invalid $boost
  	     * @return \nl\naturalis\bioportal\Condition
  	     */
  	    public function setBoost ($boost = false) {
 	    	$boost = (float) $boost;
 	    	if (empty($boost)) {
 	    		throw new \InvalidArgumentException('Error: condition boost ' .
 	    			'should be a positive float.');
 	    	}
 	    	$this->_boost = $boost;
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    
 	    /**
 	     * Removes scoring for the condition
 	     * 
 	     * Scoring will be disabled (set to 1) for this condition if value
 	     * is empty or set to TRUE. In theory this will increase performance. 
 	     * 
 	     * @param string $constant
 	     * @throws \InvalidArgumentException In case of invalid $constant
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function setConstantScore ($constant = true) {
 	    	if (!is_bool($constant)) {
 	    		throw new \InvalidArgumentException('Error: condition constant ' .
 	    			'score parameter should be TRUE (default)/FALSE.');
 	    	}
 	    	$this->_constantScore = $constant;
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /**
 	     * Switches the _not parameter
 	     * 
 	     * @see \nl\naturalis\bioportal\Condition::setNot()
 	     * @return \nl\naturalis\bioportal\Condition
 	     */
 	    public function negate () {
 	    	$this->_not = !$this->_not ? 'NOT' : false;
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
		/**
		 * Gets current complete condition
		 * 
		 * @return string Condition as json-formatted string
		 */
 	    public function getCondition () {
 	    	return json_encode($this->_condition);
  	    }
 	    
  	    /**
  	     * Gets AND section of condition
  	     *
  	     * @return string Condition as json-formatted string
  	     */
  	     public function getAnd () {
 	    	if (isset($this->_condition['and'])) {
 	    		return json_encode($this->_condition['and']);
 	    	}
 	    	return null;
 	    }
 	    
 	    /**
 	     * Gets OR section of condition
 	     *
 	     * @return string Condition as json-formatted string
 	     */
 	    public function getOr () {
 	    	if (isset($this->_condition['or'])) {
 	    		return json_encode($this->_condition['or']);
 	    	}
 	    	return null;
 	    }
 	    
 	    /**
 	     * Gets condition field
 	     *
 	     * @return string Field
 	     */
 	    public function getField () {
 	    	return $this->_field;
        }
        
        /**
         * Gets condition operator
         *
         * @return string Operator
         */
        public function getOperator () {
        	return $this->_operator;
        }
        
        /**
         * Gets condition value
         *
         * @return string Value
         */
        public function getValue () {
        	return $this->_value;
        }
        
        /**
         * Gets condition boost factor
         *
         * @return float Boost factor
         */
        public function getBoost () {
        	return json_encode($this->_boost);
        }

        /**
         * Gets condition negation (_not)
         *
         * @return string
         */
        public function getNot () {
        	return $this->_not;
        }
        
        /**
         * Gets if condition is negated
         *
         * @return bool
         */
        public function isNegated () {
        	return $this->_not == 'NOT';
        }
        
        /**
         * Gets if condition uses constant score
         *
         * @return bool
         */
        public function isConstantScore () {
        	return $this->_constantScore;
        }
        
        private function _bootstrap ($field, $operator, $value) {
            if (!$field || !$operator) {
                throw new \InvalidArgumentException('Error: condition incomplete! ' .
                	'Condition should be initialised as a triplet: ' .
                	'"path.to.field", "operator", "value". An empty/not-empty query ' .
                	' should be formatted as a duplet without a value: "path.to.field", ' .
                	'"EQUALS/NOT_EQUALS"');
            }
            // When passing null as value, operator must match EQUALS/NOT_EQUALS
            if ($field && $operator && is_null($value) && 
            	!in_array(strtoupper($operator), ['EQUALS', 'NOT_EQUALS'])) {
             	throw new \InvalidArgumentException('Error: condition incorrectly ' . 
            		'formatted. An empty/not-empty query should be formatted ' .
            		'as a duplet without a value: "path.to.field", "EQUALS/NOT_EQUALS"');
            }
            $this->_setField($field);
            $this->_setOperator($operator);
            $this->_setValue($value);
            return true;
        }

        /*
         * Formats condition array; specifically excludes value if null, to allow
         * empty/not-empty queries. Should only be called after _bootstrap().
         */
        private function _setStatement () {
        	$this->_statement['field'] = $this->_field;
        	$this->_statement['operator'] = $this->_operator;
        	if (is_null($this->_value)) {
        		unset($this->_statement['value']);
        	} else {
        		$this->_statement['value'] = $this->_value;
        	}
        	// Append secondary parameters if set; 
        	// if set previously and now empty, unset
        	foreach (['boost', 'constantScore', 'not'] as $p) {
         		if ($this->{"_$p"} !== false && !is_null($this->{"_$p"})) {
        			$this->_statement[$p] = $this->{"_$p"};
        		} else if (isset($this->_statement[$p])) {
        			unset($this->_statement[$p]);
        		}
        	}
        	return $this->_statement;
        }
        
        private function _setCondition () {
        	// Save and/or statements before resetting condition
        	$and = isset($this->_condition['and']) ? $this->_condition['and'] : false;
        	$or = isset($this->_condition['or']) ? $this->_condition['or'] : false;
        	// Reset condition to current statement
        	$this->_condition = $this->_setStatement();
        	// Reappend and/or statements if necessary
        	if ($and) { 
        		$this->_condition['and'] = $and; 
        	}
        	if ($or) { 
        		$this->_condition['or'] = $or; 
        	}
        	return $this->_condition;
        }
        
        private function _setField ($field) {
            if (empty($field)) {
                throw new \InvalidArgumentException('Error: condition field is not set.');
            }
            $this->_field = $field;
            return $this->_field;
        }

 	    private function _setOperator ($operator) {
            if (empty($operator)) {
                throw new \InvalidArgumentException('Error: condition operator is not set.');
            }
            if (!in_array(strtoupper($operator), self::$operators)) {
                throw new \UnexpectedValueException('Error: ' . 
                	'condition operator should match one of the following: ' . 
                	implode(', ', self::$operators));
             }
            $this->_operator = strtoupper($operator);
            return $this->_operator;
        }

 	 	/*
 	 	 * Value can be null but not an empty string
 	 	 */
 	 	private function _setValue ($value) {
            if (empty($value) && !is_null($value)) {
                throw new \InvalidArgumentException('Error: condition value is empty.');
            }
            $this->_value = $value;
            return $this->_value;
        }
         
 	}
