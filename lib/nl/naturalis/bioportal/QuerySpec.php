<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\AbstractClass as AbstractClass;

    final class QuerySpec extends AbstractClass
 	{
		private $_querySpec;
 	    private	$_conditions;
		private $_from;
		private $_size;
		private $_sortFields;
		private $_fields;
		private $_logicalOperator;

 	    public function __construct() {
            parent::__construct();
        }

        public function addCondition ($condition = false) {
            if (!($condition instanceof Condition)) {
                throw new \Exception('Error: invalid condition, should be created ' .
                    'using the Condition class.');
            }
            $this->_conditions[] = json_decode($condition->getCondition(), true);
            $this->_querySpec['conditions'] = $this->_conditions;
            return $this;
        }

        public function sortBy ($path = false, $direction = false) {
            if (!$path || !$direction) {
                throw new \Exception('Error: sort by statement incomplete! Statement ' .
                    'should be initialised as a duplet: "path.to.field", "ASC/DESC".');
            }
            if (!in_array(strtoupper($direction), self::$sortDirections)) {
                throw new \Exception('Error: sort direction should match one of the ' .
                    'following: ' . implode(', ', self::$sortDirections));
            }
            $this->_sortFields[] = [
                'path' => $path,
                'sortOrder' => strtoupper($direction),
            ];
            $this->_querySpec['sortFields'] = $this->_sortFields;
            return $this;
        }

        public function setSortFields ($fields = []) {
            foreach ($fields as $sortBy) {
                $this->sortBy($sortBy[0], $sortBy[1]);
            }
        }

 	 	public function setFrom ($from = null) {
 	     	if (!$this->isInteger($from)) {
                throw new \Exception('Error: from parameter "' . $from .
                    '" is not an integer.');
                return false;
 	     	}
 	     	$this->_from = (int)$from;
 	     	$this->_querySpec['from'] = $this->_from;
            return $this;
 	 	}

 	    public function setSize ($size = null) {
 	     	if (!$this->isInteger($size)) {
                throw new \Exception('Error: size parameter "' . $size .
                    '" is not an integer.');
                return false;
 	     	}
 	     	$this->_size = (int)$size;
 	     	$this->_querySpec['size'] = $this->_size;
            return $this;
 	    }

        public function setLogicalOperator ($operator = false) {
            if (!in_array(strtoupper($operator), self::$logicalOperators)) {
                throw new \Exception('Error: logical operator should match ' .
                    implode(', ', self::$logicalOperators));
            }
            $this->_logicalOperator = strtoupper($operator);
            $this->_querySpec['logicalOperator'] = $this->_logicalOperator;
            return $this;
        }

        public function setFields ($fields = null) {
            if (!is_array($fields)) {
                throw new \Exception('Error: fields should be a non-empty array');
            }
            $this->_fields = $fields;
            $this->_querySpec['fields'] = $fields;
            return $this;
        }

 	    public function getFields () {
            return json_encode($this->_fields);
        }

 	    public function getConditions () {
            return json_encode($this->_conditions);
        }

        public function getSortFields () {
            return json_encode($this->_sortFields);
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
