<?php
    namespace nl\naturalis\bioportal;

    class AbstractQuery
    {
        public function __construct () {
        }

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

		public static $logicalOperators = [
            'AND',
		    'OR',
		];

		public static $sortDirections = [
		    'ASC' => true,
		    'DESC' => false,
		    'TRUE' => true,
		    'FALSE' => false,
		];

        protected function isPositiveInteger ($i) {
            if (is_int($i) || ctype_digit($i) && (int)$i > 0) {
                return true;
            }
            return false;
        }


    }
