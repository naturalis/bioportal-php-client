<?php
    namespace nl\naturalis\bioportal;

    class Common
    {
        public function __construct () {
        }

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
         * @param unknown $str
         * @return string
         */
        public function camelCase ($str) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }

        /**
         * Cleans input string with commas or converts array to comma-separated string
         * 
         * @param string|array $data
         * @return string Comma-separated string
         */
		 public function commaSeparate ($data = '') {
            if (!is_array($data)) {
                $p = array_map('trim', explode(',', $data));
                if (count($p) == 1) {
                    $ids[] = trim($data);
                } else {
                    $ids = $p;
                }
            } else {
                $ids = array_map('trim', $data);
            }
            return implode(',', $ids);
		}

    }
