<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractQuery as AbstractQuery;
    use Exception;

    class QuerySpec extends AbstractQuery
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
            if (!$condition || !($condition instanceof Condition)) {
                throw new Exception('Error: invalid condition, should be created ' .
                    'using Condition class.');
            }
            $this->_conditions[] = json_decode($condition->getCondition(), true);
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
                throw new Exception('Error: sort direction should match one
                    of the following: ' . implode(', ', array_keys(self::$sortDirections)));
            }
            $this->_sortFields[] = [
                'path' => $path,
                'ascending' => $this->setSortDirection($direction),
            ];
            return $this;
        }

 	 	public function setFrom ($from = false) {
 	     	if (!$from || !$this->isPositiveInteger($from)) {
                throw new Exception('Error: "from" should be a positive integer.');
                return false;
 	     	}
 	     	$this->_from = (int)$from;
            return $this;
 	 	}

 	    public function setSize ($size = false) {
 	     	if (!$size || !$this->isPositiveInteger($size)) {
                throw new Exception('Error: "size" should be a positive integer.');
                return false;
 	     	}
 	     	$this->_size = (int)$size;
            return $this;
 	    }

        public function setLogicalOperator ($operator = false) {
            if (!in_array(strtoupper($operator), self::$logicalOperators)) {
                throw new Exception('Error: logical operator should match ' .
                    implode(', ', self::$logicalOperators));
            }
            $this->_logicalOperator = strtoupper($operator);
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

        public function getSpec ($encode = true) {
            $this->setSpec();
            if (!empty($this->_querySpec)) {
                $d = json_encode($this->_querySpec);
                return $encode ? urlencode($d) : $d;
            }
            return false;
        }

 	    private function setSpec () {
 	        $this->_querySpec = [];
            if (!empty($this->_conditions)) {
                $this->_querySpec['conditions'] = $this->_conditions;
            }
            if (!empty($this->_logicalOperator)) {
                $this->_querySpec['logicalOperator'] = $this->_logicalOperator;
            }
            if (!empty($this->_sortFields)) {
                $this->_querySpec['sortFields'] = $this->_sortFields;
            }
            if (is_int($this->_from)) {
                $this->_querySpec['from'] = $this->_from;
            }
            if (is_int($this->_size)) {
                $this->_querySpec['size'] = $this->_size;
            }
        }
 	}