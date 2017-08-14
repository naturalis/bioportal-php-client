<?php
    namespace nl\naturalis\bioportal;

    class Common
    {
        public function __construct () {}

        /**
         * Valid NBA operators
         * 
         * Can be used to validate a given operator.
         * 
         * @var array Valid NBA operators
         */
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
			'STARTS_WITH',
			'NOT_STARTS_WITH',
			'STARTS_WITH_IC',
			'NOT_STARTS_WITH_IC'
		];

		/**
		 * Valid NBA logical operators
		 *
		 * Can be used to validate a given logical operator.
		 *
		 * @var array Valid NBA logical operators
		 */
		public static $logicalOperators = [
            'AND',
		    'OR',
		];

		/**
		 * Valid NBA sort directions
		 *
		 * Can be used to validate a given sort direction.
		 *
		 * @var array Valid NBA sort directions
		 */
		public static $sortDirections = [
		    'ASC',
		    'DESC',
		];
		
		/**
		 * Valid NBA groupSort directions
		 *
		 * Can be used to validate a given groupSort direction.
		 *
		 * @var array Valid NBA groupSort directions
		 */
		public static $_groupSortDirections = [
			'COUNT_DESC', 
			'COUNT_ASC', 
			'NAME_ASC', 
			'NAME_DESC',	
		];

        /**
         * Validate integer input
         * 
         * Can be used to check if input is integer or string that
         * can be safely cast to integer (25 vs "25").
         * 
         * @param unknown $i
         * @return boolean
         */
        public function isInteger ($i) {
            if (ctype_digit(strval($i))) {
                return true;
            }
            return false;
        }

        /**
         * Convert string with underscores to camelCased version
         * 
         * @example this_is_a_string to thisIsAString
         * @param string $str
         * @return string
         */
        public function camelCase ($str) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }
		
		/**
		 * Cleans input string with commas or converts array to comma-separated string:
		 * trims values and rawurldecodes these for use in url path.
		 *
		 * @param string|array $data        	
		 * @return string Comma-separated string
		 */
		public function commaSeparate ($data = '') {
			if (!is_array($data)) {
				$p = array_map(function ($s) {
					return rawurldecode(trim($s));
				}, explode(',', $data));
				if (count($p) == 1) {
					$ids[] = rawurldecode(trim($data));
				} else {
					$ids = $p;
				}
			} else {
				$ids = array_map(function ($s) {
					return rawurldecode(trim($s));
				}, explode(',', $data));
			}
			return implode(',', $ids);
		}

    }
