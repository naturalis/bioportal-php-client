<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractClass as AbstractClass;
    use Exception;

    class QuerySpec extends AbstractClass
 	{
		private $_querySpec;
 	    private	$_conditions;
		private $_from;
		private $_size;
		private $_sortFields;
		private $_logicalOperator;

 	    public function __construct() {
            parent::__construct();
        }

        public function addCondition ($condition = false) {
            if (!($condition instanceof Condition)) {
                throw new Exception('Error: invalid condition, should be created ' .
                    'using the Condition class.');
            }
            $this->_conditions[] = json_decode($condition->getCondition(), true);
            $this->_querySpec['conditions'] = $this->_conditions;
            return $this;
        }

        public function sortBy ($path = false, $direction = false) {
            if (!$path || !$direction) {
                throw new Exception('Error: sort by statement incomplete! Statement ' .
                    'should be initialised as a duplet: "path.to.field", ' .
                    '"ASC/DESC" (or [ASC is] true/false).');
                return false;
            }
            if (!is_bool($direction) &&
                !array_key_exists(strtoupper($direction), self::$sortDirections)) {
                throw new Exception('Error: sort direction should match one of the ' .
                    'following: ' . implode(', ', array_keys(self::$sortDirections)));
            }
            $this->_sortFields[] = [
                'path' => $path,
                'ascending' => $this->setSortDirection($direction),
            ];
            $this->_querySpec['sortFields'] = $this->_sortFields;
            return $this;
        }

 	 	public function setFrom ($from = null) {
 	     	if (!$this->isInteger($from)) {
                throw new Exception('Error: from parameter "' . $from .
                    '" is not an integer.');
                return false;
 	     	}
 	     	$this->_from = (int)$from;
 	     	$this->_querySpec['from'] = $this->_from;
            return $this;
 	 	}

 	    public function setSize ($size = null) {
 	     	if (!$this->isInteger($size)) {
                throw new Exception('Error: size parameter "' . $size .
                    '" is not an integer.');
                return false;
 	     	}
 	     	$this->_size = (int)$size;
 	     	$this->_querySpec['size'] = $this->_size;
            return $this;
 	    }

        public function setLogicalOperator ($operator = false) {
            if (!in_array(strtoupper($operator), self::$logicalOperators)) {
                throw new Exception('Error: logical operator should match ' .
                    implode(', ', self::$logicalOperators));
            }
            $this->_logicalOperator = strtoupper($operator);
            $this->_querySpec['logicalOperator'] = $this->_logicalOperator;
            return $this;
        }

        public function getCondition () {
            return json_encode($this->_condition);
        }

 	    public function getSortFields () {
            return json_encode($this->_sortFields);
        }

        private function setSortDirection ($direction) {
            if (is_bool($direction)) {
                return $direction;
            }
            return self::$sortDirections[strtoupper($direction)];
        }

        public function getSpec ($encoded = true) {
            if (!empty($this->_querySpec)) {
                ksort($this->_querySpec);
                $d = json_encode($this->_querySpec);
                return $encoded ? urlencode($d) : $d;
            }
            return false;
        }
 	}