<?php
    namespace nl\naturalis\bioportal;

    class AbstractClass
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
		    'MATCHES',
		    'NOT_MATCHES',
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

        public function isInteger ($i) {
            if (ctype_digit(strval($i))) {
                return true;
            }
            return false;
        }

        /**
         * Input: string_with_underscores, output: stringWithUnderscores
         */
        public function camelCase ($str) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }
    }
