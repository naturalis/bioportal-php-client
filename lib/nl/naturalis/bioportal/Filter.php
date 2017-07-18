<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\Common as Common;
    
 	final class Filter extends Common
 	{
        private $acceptRegexp;
        private $rejectRegexp;
        private $acceptValues;
        private $rejectValues;
       
        
        public function construct () {
            parent::construct();
        }

        /**
         * Set filter accept values
         * 
         * @param string|array $values Array is converted to comma separated string
         * @throws \InvalidArgumentException In case $values is empty
         * @return \nl\naturalis\bioportal\Filter
         */
        public function acceptValues ($values = []) {
            if (empty($values)) {
                throw new \InvalidArgumentException('Error: filter accept values are not set.');
            }
            if (!is_array($values)) {
            	$values = [$values];
            }
  	    	$this->acceptValues = $values;
 	    	return $this;
        }
 
        /**
         * Set filter reject values
         * 
         * @param string|array $values Array is converted to comma separated string
         * @throws \InvalidArgumentException In case $values is empty
         * @return \nl\naturalis\bioportal\Filter
         */
        public function rejectValues ($values = []) {
 	        if (empty($values)) {
                throw new \InvalidArgumentException('Error: filter reject values are not set.');
            }
            if (!is_array($values)) {
            	$values = [$values];
            }
            $this->rejectValues = $values;
 	    	return $this;
 	    }

        /**
         * Set filter accept regular expression
         * 
         * @param string $regex Regular expression
         * @throws \InvalidArgumentException In case $regex is empty or not a string
         * @return \nl\naturalis\bioportal\Filter
         */
 	    public function acceptRegexp ($regex = null) {
 	        if (empty($regex) || !is_string($regex)) {
                throw new \InvalidArgumentException('Error: filter accept regex is empty ' . 
                	' or incorrectly set.');
            }
 	    	$this->acceptRegexp = $regex;
 	    	return $this;
 	    }
 
        /**
         * Set filter reject regular expression
         * 
         * @param string $regex Regular expression
         * @throws \InvalidArgumentException In case $regex is empty or not a string
         * @return \nl\naturalis\bioportal\Filter
         */
 	    public function rejectRegexp ($regex = null) {
 	        if (empty($regex) || !is_string($regex)) {
                throw new \InvalidArgumentException('Error: filter reject regex is empty ' . 
                	' or incorrectly set.');
 	        }
 	    	$this->rejectRegexp = $regex;
 	    	return $this;
 	    }
 	    
 	    /**
 	     * Get "clean" Filter object with just the filters that have been set 
 	     * 
 	     * @return string Filter as json-encoded string
 	     */
 	    public function getFilter () {
	        $reflection = new \ReflectionClass($this);
	        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
	        $filter = new \stdClass();
	 	    foreach ($properties as $property) {
	 	    	$method = 'get' . ucfirst($property->getName());
	 	    	if (!empty(json_decode($this->{$method}(), true))) {
	 	    		$filter->{$property->getName()} = json_decode($this->{$method}(), true);
	 	    	}
	 	    }
	 	    return json_encode($filter);
 	    }
 
        /**
         * Get filter accept values
         *
         * @return string Accept values as json-encoded string
         */
 	    public function getAcceptValues () {
        	return json_encode($this->acceptValues);
        }
 
        /**
         * Get filter accept values
         *
         * @return string Accept values as json-encoded string
         */
        public function getRejectValues () {
        	return json_encode($this->rejectValues);
        }

        /**
         * Get filter accept regular expression
         *
         * @return string Regular expression as json-encoded string
         */
        public function getAcceptRegexp () {
        	return json_encode($this->acceptRegexp);
        }
 
        /**
         * Get filter reject regular expression
         *
         * @return string Regular expression as json-encoded string
         */
        public function getRejectRegexp () {
        	return json_encode($this->rejectRegexp);
        }
        
 	}
