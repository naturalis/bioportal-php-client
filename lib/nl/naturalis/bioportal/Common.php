<?php
    namespace nl\naturalis\bioportal;

    class Common
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
		    'ASC',
		    'DESC',
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

    	 /**
		 * Takes either an array or (comma-separated) string;
		 * trims spaces and returns object with string
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
