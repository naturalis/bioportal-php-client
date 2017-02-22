<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractClass as AbstractClass;

 	final class Condition extends AbstractClass
 	{
        private $_field;
        private $_operator;
        private $_value;
        private $_condition;

        public function __construct ($field = false, $operator = false, $value = null) {
            parent::__construct();
            $this->_bootstrapCondition($field, $operator, $value);
            $this->_condition = $this->_setCondition();
        }

        public function addAnd ($fieldOrCondition, $operator = false, $value = null) {
        	// Allow setting a previously constructed Condition, cf Java client
        	if (is_object($fieldOrCondition)) {
        		if (!($fieldOrCondition instanceof Condition)) {
        			throw new \InvalidArgumentException('Error: invalid condition.');
        		}
        		$this->_condition['and'][] = 
        			json_decode($fieldOrCondition->getCondition(), true);
        	// Setting an AND condition the regular way
        	} else {
	        	$this->_bootstrapCondition($fieldOrCondition, $operator, $value);
	            $this->_condition['and'][] = $this->_setCondition();
        	}
        	return $this;
        }

 	    public function addOr ($fieldOrCondition, $operator = false, $value = null) {
 	    	// Allow setting a previously constructed Condition, cf Java client
 	    	if (is_object($fieldOrCondition)) {
 	    		if (!($fieldOrCondition instanceof Condition)) {
 	    			throw new \InvalidArgumentException('Error: invalid condition.');
 	    		}
 	    		$this->_condition['or'][] = 
 	    			json_decode($fieldOrCondition->getCondition(), true);
 	    	// Setting an OR condition the regular way
 	    	} else {
 	    	 	$this->_bootstrapCondition($fieldOrCondition, $operator, $value);
            	$this->_condition['or'][] = $this->_setCondition();
 	    	}
 	    	return $this;
 	    }

 	    public function getCondition () {
 	    	if (isset($this->_condition)) {
 	    		return json_encode($this->_condition);
 	    	}
 	    	return false;
 	    }
 	    
 	    public function getAnd () {
 	    	if (isset($this->_condition['and'])) {
 	    		return json_encode($this->_condition['and']);
 	    	}
 	    	return false;
 	    }
 	    
 	    public function getOr () {
 	    	if (isset($this->_condition['or'])) {
 	    		return json_encode($this->_condition['or']);
 	    	}
 	    	return false;
 	    }
 	    
 	    public function getField () {
        	if (isset($this->_condition['field'])) {
        		return json_encode($this->_condition['field']);
        	}
        	return false;
        }
        
        public function getOperator () {
        	if (isset($this->_condition['operator'])) {
        		return json_encode($this->_condition['operator']);
        	}
        	return false;
        }
        
        public function getValue () {
        	if (isset($this->_condition['value'])) {
        		return json_encode($this->_condition['value']);
        	}
        	return false;
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
        private function _setCondition () {
        	$c['field'] = $this->_field;
        	$c['operator'] = $this->_operator;
        	if (!is_null($this->_value)) {
        		$c['value'] = $this->_value;
        	}
        	return $c;
        }
        
        private function _setField ($field) {
            if (empty($field)) {
                throw new \InvalidArgumentException('Error: condition field is not set.');
                return false;
            }
            $this->_field = $field;
        }

 	    private function _setOperator ($operator) {
            if (empty($operator)) {
                throw new \InvalidArgumentException('Error: condition operator is not set.');
                return false;
            }
            if (!in_array(strtoupper($operator), self::$operators)) {
                throw new \UnexpectedValueException('Error: ' . 
                	'condition operator should match one of the following: ' . 
                	implode(', ', self::$operators));
                return false;
            }
            $this->_operator = strtoupper($operator);
        }

 	 	/*
 	 	 * Value can be null but not an empty string
 	 	 */
 	 	private function _setValue ($value) {
            if (empty($value) && !is_null($value)) {
                throw new \InvalidArgumentException('Error: condition value is empty.');
                return false;
            }
            $this->_value = $value;
        }
 	}
