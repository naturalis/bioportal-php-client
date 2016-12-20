<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractQuery as AbstractQuery;

 	class Condition extends AbstractQuery
 	{
		public static $operators = [
            'BETWEEN',
            'EQUALS',
            'EQUALS_IC',
            'GT',
            'GTE',
            'IN',
            'LIKE',
            'LT',
            'LTE',
            'NOT_BETWEEN',
            'NOT_EQUALS',
            'NOT_EQUALS_IC',
            'NOT_IN',
            'NOT_LIKE',
		];

        private $_field;
        private $_operator;
        private $_value;

        public function __construct ($field = false, $operator = false, $value = false) {
            $this->setCondition($field, $operator, $value);
        }

        private function setCondition ($field, $operator, $value) {
            if ($this->validateCondition($field, $operator, $value)) {
                $this->setField($field);
                $this->setOperator($operator);
                $this->setValue($value);
            }
        }

        private function validateCondition ($field, $operator, $value) {
            if (!$field || !$operator || !$value) {
                throw new Exception('Error: condition incomplete! Condition should be ' .
                'initialised as a triplet: field, operator, value.');
            }
        }

        private function setField ($field) {
            if (empty($field)) {
                throw new Exception('Error: condition field is not set.');
            }
            $this->_field = $field;
        }

 	    private function setOperator ($operator) {
            if (empty($operator)) {
                throw new Exception('Error: condition operator is not set.');
            }
            if (!in_array(strtoupper($operator), $this::$operators)) {
                throw new Exception('Error: condition operator should match one of the following: ' .
                    implode(', ', $this::$operators));
            }
            $this->_operator = strtoupper($operator);
        }

 	 	private function setValue ($value) {
            if (!$value || $value == '') {
                throw new Exception('Error: condition value is not set.');
            }
            $this->_value = $value;
        }


 	}
