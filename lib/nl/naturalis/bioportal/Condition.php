<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractClass as AbstractClass;
    
    /*
     * A condition is built from one or multiple statements. In its simplest form,
     * a condition equals a statement. A condition can also represent a nested set of 
     * and/or statements. Each statement is evaluated before it is appended to the condition.
     */

 	final class Condition extends AbstractClass
 	{
        private $_field;
        private $_operator;
        private $_value;
        private $_not;
        private $_boost;
        private $_constantScore;
        private $_statement = [];
        private $_condition = [];

        public function __construct ($field = false, $operator = false, $value = null) {
            parent::__construct();
            $this->_bootstrapCondition($field, $operator, $value);
            $this->_setCondition();
        }

        public function addAnd ($fieldOrCondition, $operator = false, $value = null) {
        	// Allow setting a previously constructed Condition, cf Java client
        	if (is_object($fieldOrCondition)) {
        		if (!($fieldOrCondition instanceof Condition)) {
        			throw new \InvalidArgumentException('Error: invalid AND condition.');
        		}
        		$this->_condition['and'][] = 
        			json_decode($fieldOrCondition->getCondition(), true);
        	// Setting an AND condition the regular way
        	} else {
	        	$this->_bootstrapCondition($fieldOrCondition, $operator, $value);
	            $this->_condition['and'][] = $this->_setStatement();
        	}
        	return $this;
        }

 	    public function addOr ($fieldOrCondition, $operator = false, $value = null) {
 	    	// Allow setting a previously constructed Condition, cf Java client
 	    	if (is_object($fieldOrCondition)) {
 	    		if (!($fieldOrCondition instanceof Condition)) {
 	    			throw new \InvalidArgumentException('Error: invalid OR condition.');
 	    		}
 	    		$this->_condition['or'][] = 
 	    			json_decode($fieldOrCondition->getCondition(), true);
 	    	// Setting an OR condition the regular way
 	    	} else {
 	    	 	$this->_bootstrapCondition($fieldOrCondition, $operator, $value);
            	$this->_condition['or'][] = $this->_setStatement();
 	    	}
 	    	return $this;
 	    }
 	    
 	    /*
 	     * Can only be used to override previously initialised value
 	     */
 	    public function setField ($field = null) {
 	    	$this->_setField($field);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /*
 	     * Can only be used to override previously initialised value
 	     */
 	    public function setOperator ($operator = null) {
 	    	$this->_setOperator($operator);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    /*
 	     * Can only be used to override previously initialised value
 	     */
 	    public function setValue ($value = null) {
 	    	$this->_setValue($value);
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
		/**
 	     * Creates a negated condition if parameter equals 'not';
 	     * else removes negation (as per to Java client)
 	     */
 	    public function setNot ($not = 'NOT') {
  	    	$this->_not = strtoupper($not) == 'NOT' ? 'NOT' : false;
  	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
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
 	     * Switches _isNot parameter (as per Java client)
 	     */
 	    public function negate () {
 	    	$this->_not = !$this->_not ? 'NOT' : false;
 	    	$this->_setCondition();
 	    	return $this;
 	    }
 	    
 	    public function getCondition () {
 	    	return json_encode($this->_condition);
  	    }
 	    
 	    public function getAnd () {
 	    	if (isset($this->_condition['and'])) {
 	    		return json_encode($this->_condition['and']);
 	    	}
 	    	return null;
 	    }
 	    
 	    public function getOr () {
 	    	if (isset($this->_condition['or'])) {
 	    		return json_encode($this->_condition['or']);
 	    	}
 	    	return null;
 	    }
 	    
 	    public function getField () {
 	    	return json_encode($this->_field);
        }
        
        public function getOperator () {
        	return json_encode($this->_operator);
        }
        
        public function getValue () {
        	return json_encode($this->_value);
        }
        
        public function getBoost () {
        	return json_encode($this->_boost);
        }

        public function getNot () {
        	return json_encode($this->_not);
        }
        
        public function isNegated () {
        	return $this->_not == 'NOT';
        }
        
        public function isConstantScore () {
        	return $this->_constantScore;
        }
        
        private function _bootstrapCondition ($field, $operator, $value) {
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
         * empty/not-empty queries. Should only be called after _bootstrapCondition().
         */
        private function _setStatement () {
        	$this->_statement['field'] = $this->_field;
        	$this->_statement['operator'] = $this->_operator;
        	if (!is_null($this->_value)) {
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
        	// Save and/or subqueries before resetting condition
        	$and = isset($this->_condition['and']) ? $this->_condition['and'] : false;
        	$or = isset($this->_condition['or']) ? $this->_condition['or'] : false;
        	// Reset condition to current statement
        	$this->_condition = $this->_setStatement();
        	// Reappend and/or subqueries if necessary
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
