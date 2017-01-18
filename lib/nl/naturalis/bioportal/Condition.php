<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractClass as AbstractClass;
    use Exception;

 	class Condition extends AbstractClass
 	{
        private $_field;
        private $_operator;
        private $_value;
        private $_condition;

        public function __construct ($field = false, $operator = false, $value = false) {
            parent::__construct();
            $this->setCondition($field, $operator, $value);
        }

        public function addAnd ($field, $operator, $value) {
            if (empty($this->_condition)) {
                throw new \Exception('Error: cannot add "and" statement to empty condition.');
            }
            if ($this->bootstrapCondition($field, $operator, $value)) {
                $this->_condition['and'][] =
                    [
        				'field' => $this->_field,
        				'operator' => $this->_operator,
        				'value' => $this->_value
        			];
            }
            return $this;
        }

 	    public function addOr ($field, $operator, $value) {
            if (empty($this->_condition)) {
                throw new \Exception('Error: cannot add "or" statement to empty condition.');
            }
            if ($this->bootstrapCondition($field, $operator, $value)) {
                $this->_condition['or'][] =
                    [
        				'field' => $this->_field,
        				'operator' => $this->_operator,
        				'value' => $this->_value
        			];
            }
            return $this;
 	    }

        public function getCondition () {
            return json_encode($this->_condition);
        }

        private function setCondition ($field, $operator, $value) {
            if ($this->bootstrapCondition($field, $operator, $value)) {
                $this->_condition =
                    [
        				'field' => $this->_field,
        				'operator' => $this->_operator,
        				'value' => $this->_value
        			];
            }

        }

        private function bootstrapCondition ($field, $operator, $value) {
            if (!$field || !$operator || !$value) {
                throw new \Exception('Error: condition incomplete! Condition should be ' .
                    'initialised as a triplet: "path.to.field", "operator", "value".');
                return false;
            }
            $this->setField($field);
            $this->setOperator($operator);
            $this->setValue($value);
            return true;
        }

        private function setField ($field) {
            if (empty($field)) {
                throw new \Exception('Error: condition field is not set.');
                return false;
            }
            $this->_field = $field;
        }

 	    private function setOperator ($operator) {
            if (empty($operator)) {
                throw new \Exception('Error: condition operator is not set.');
                return false;
            }
            if (!in_array(strtoupper($operator), self::$operators)) {
                throw new \Exception('Error: condition operator should match one
                    of the following: ' . implode(', ', self::$operators));
                return false;
            }
            $this->_operator = strtoupper($operator);
        }

 	 	private function setValue ($value) {
            if (!$value || $value == '') {
                throw new \Exception('Error: condition value is not set.');
                return false;
            }
            $this->_value = $value;
        }
 	}
