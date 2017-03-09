<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\QuerySpec as QuerySpec;

    final class Client extends Common
 	{
		private $_nbaUrl;
		private $_nbaTimeout = 5;
		private $_maxBatchSize = 1000;
		private $_config;

		private $_querySpec;
		private $_channels;
		private $_remoteData;
		private $_clients;

		public static $nbaClients = [
			'taxon',
			'multimedia',
			'specimen',
			'names',
		    'geo',
		];

		/**
		 * Constructor
		 *
		 * Sets ini values for $_nbaUrl and $_nbaTimeout;
		 * implicitly sets $_config through either method
		 *
         * @return void
		 */
		public function __construct () {
			parent::__construct();
			$this->_setNbaUrl();
            $this->_setNbaTimeout();
		}
		
		/*
		 * Catch common mistake where client is called without brackets
		 */
		public function __get ($value) {
			if (!property_exists($this, $value) && in_array($value, $this::$nbaClients)) {
				throw new \BadMethodCallException(ucfirst($value) . ' client should be ' .
					"called using ->{$value}()->, not ->{$value}->.");
			}
		}

		/**
		 * Sets the client to taxon
		 *
         * @return Returns this instance
		 */
		public function taxon () {
			$this->_clients = [];
			$this->_clients[] = 'taxon';
			return $this;
		}

		/**
		 * Sets the client to specimen
		 *
         * @return Returns this instance
		 */
		public function specimen () {
			$this->_clients = [];
			$this->_clients[] = 'specimen';
			return $this;
		}
		
		public function names () {
			$this->_clients = [];
			$this->_clients[] = 'names';
			return $this;
		}

 		/**
		 * Sets the client to multimedia
		 *
         * @return class This class (allowing chaining)
		 */
		public function multimedia () {
			$this->_clients = [];
			$this->_clients[] = 'multimedia';
			return $this;
		}

 		/**
		 * Sets the client to geo
		 *
         * @return class This class (allowing chaining)
		 */
		public function geo () {
			$this->_clients = [];
			$this->_clients[] = 'geo';
			return $this;
		}

		/**
		 * Sets all clients
		 *
		 * Sets taxon, specimen, names and multimedia clients, allowing distributed query.
		 * Does not verify query, so use with care!
		 *
         * @return class This class (allowing chaining)
		 */
		public function all () {
			$this->_clients = array_diff($this::$nbaClients, array('geo'));
			return $this;
		}

		/**
		 * Returns publicly available clients
		 *
         * @return json All available classes in the client
		 */
		public function getAllClients () {
			return json_encode($this::$nbaClients);
		}

		public function getClients () {
			return json_encode($this->_clients);
		}
		
		/**
         * Sets QuerySpec
         *
         * Imports QuerySpec object from QuerySpec class
         *
         * @param class $spec QuerySpec
         *
         * @return class This class (allowing chaining)
         */
		public function querySpec ($querySpec) {
		    if (!$querySpec || !($querySpec instanceof QuerySpec)) {
                throw new \InvalidArgumentException('Error: invalid querySpec, ' .
                	'should be created using the QuerySpec class.');
		    }
            $this->_querySpec = $querySpec;
            return $this;
		}

        /**
         * Queries the NBA
         *
         * 1. Sets the curl channels for one or more clients
         * 2. Performs the NBA query
         * 3. Returns NBA result
         *
         * Depending on the number of clients, the result is returned
         * either as json or an array of json responses (in case multiple
         * channels have been used).
         *
         * @return string|array NBA data as json or array with responses
         * formatted as [client1 => json, client2 => json]
         */
		public function query () {
			$this->_bootstrap();
			if (!$this->_querySpec || empty($this->_querySpec->getQuerySpec())) {
				throw new \RuntimeException('Error: querySpec empty or not set.');
			}
			$this->_channels = [];
			foreach ($this->_clients as $client) {
				$this->_channels[] =
				[
					'client' => $client,
					'url' => $this->_nbaUrl . $client . '/query/' .
					'?_querySpec=' . $this->_querySpec->getQuerySpec()
				];
			}
			return $this->_queryAndReturnRemoteData();
		}

		/**
		 * Shorthand function to override complete config and set variables
		 */
		public function setConfig ($config = false) {
		    $this->_setConfig($config);
		}

		/**
		 * Returns current config
         *
         * @return array config
		 */
		public function getConfig () {
		    return $this->_config;
		}

		/**
		 * Set $_nbaUrl, overriding default value
		 */
		public function setNbaUrl ($url = false) {
			$this->_setNbaUrl($url);
		}

		/**
		 * Returns current $_nbaUrl
         *
         * @return string $_nbaUrl
		 */
		public function getNbaUrl () {
			return $this->_nbaUrl;
		}

		public function setNbaTimeout ($timeout = false) {
 	 	    $this->_setNbaTimeout($timeout);
		}

		/**
		 * Returns current $_nbaTimeout
         *
         * @return string $_nbaTimeout
		 */
		public function getNbaTimeout () {
 	 	    return $this->_nbaTimeout;
		}

		/**
		 * Returns current $_querySpec
         *
         * @return string $_querySpec
		 */
		public function getQuerySpec ($encoded = false) {
		    return !$encoded ? urldecode($this->_querySpec->getQuerySpec()) : 
		    	$this->_querySpec->getQuerySpec();
		}
		
		

		public function getFieldInfo ($fields = false) {
			$this->_bootstrap();
			foreach ($this->_clients as $client) {
				$url = $this->_nbaUrl . $client . '/metadata/getFieldInfo/';
				if ($fields) {
					$url .= '?fields=' . $this->commaSeparate($fields);
				}
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $url,
					];
			}
			return $this->_queryAndReturnRemoteData();
		}
		
		

		public function find ($id = false) {
			$this->_bootstrap();
			if (!$id) {
                throw new \InvalidArgumentException('Error: no id(s) ' . 
                	'provided for find method.');
		    }
		    $r = $this->commaSeparate($id);
            $method = strpos($r, ',') === false ? 'find' : 'findByIds';
			foreach ($this->_clients as $client) {
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $this->_nbaUrl . $client . '/' . $method . '/' . $r,
					];
			}
			return $this->_queryAndReturnRemoteData();
		}
		
		
		/*
		 * Unlike find, this returns array with specimen data or empty array.
		 * Can be called with or without calling ->specimen(). Cannot be used
		 * with other services!
		 */
		
		public function findByUnitId ($unitId = false) {
			if (!$unitId) {
				throw new \InvalidArgumentException('Error: no UnitID ' .
					'provided for exists/findByUnitId method.');
			}
			// Only works for specimens; return exception if called for other  service
			if (!empty($this->_clients) && !in_array('specimen', $this->_clients)) {
				throw new \RuntimeException('Error: exists/findByUnitId method ' .
					'can only be used to query specimens.');
			}
			$query = new QuerySpec();
			$query
				->addCondition(new Condition('unitID', 'EQUALS_IC', $unitId))
				->setConstantScore();
			$this->_channels = [];
			$this->_channels[] = 
				[
					'url' => $this->_nbaUrl . 'specimen/query/?_querySpec=' . 
						$query->getQuerySpec()
				];
			$this->_query();
			$data = json_decode($this->_remoteData[0]);
			return isset($data->resultSet[0]->item) ? 
				json_encode([$data->resultSet[0]->item]) : json_encode([]);
		}
	



		/* Returns whether or not the specified string is a valid UnitID
		 * (i.e. is the UnitID of at least one specimen record). */
		public function exists ($unitId = false) {
			return !empty(json_decode($this->findByUnitId($unitId))) ;
		}
		
		
		
		/*
		 * Returns all "special collections" defined within the specimen dataset.
		 * Can be called with or without calling ->specimen().
		 */
		public function getNamedCollections () {
			$this->_channels = [];
			$this->_channels[] = ['url' => $this->_nbaUrl . 'specimen/getNamedCollections'];
			$this->_query();
			return $this->_remoteData[0];
		}
		
		
		
		
		/*
		 * Convenience method to retrieve geo areas. The output is comparable to
		 * getDistinctValuesPerGroup() in the java client. This method additionally
		 * inserts both the id and Dutch name, which are absent from the
		 * getDistinctValuesPerGroup() output.
		 */
		public function getGeoAreas () {
		    $query = new QuerySpec();
            $query
            	->setSize(2000)
                ->setFields(['sourceSystemId', 'areaType', 'locality', 'countryNL'])
                ->setConstantScore();
			$data = json_decode($this->geo()->querySpec($query)->query(), true);
			// Enhance data
            foreach ($data['resultSet'] as $i => $row) {
                $result[$row['areaType']][$i]['id'] = $row['id'];
                $result[$row['areaType']][$i]['locality']['en'] =
                    $row['locality'];
                $result[$row['areaType']][$i]['locality']['nl'] =
                    !empty($row['countryNL']) && $row['countryNL'] != '\N' ?
                        $row['countryNL'] : $row['locality'];
            }
            return isset($result) ? json_encode($result) : false;
		}

		/*
		 * Can be used with or without setting a querySpec. 
		 * QuerySpec must be set before calling getDistinctValues, e.g.:
		 * $client->multimedia()->querySpec($query)->getDistinctValues('creator');
		 */
		public function getDistinctValues ($field = false) {
			$this->_bootstrap();
			if (!$field) {
                throw new \InvalidArgumentException('Error: no field provided for ' .
                    'getDistinctValues.');
		    }
		    foreach ($this->_clients as $client) {
		    	$url = $this->_nbaUrl . $client . '/getDistinctValues/' . $field;
		    	if ($this->_querySpec) {
		    		$url .= '?_querySpec=' . $this->_querySpec->getQuerySpec();
		    	}
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $url,
					];
			}
            return $this->_queryAndReturnRemoteData();
        }
        
        
        /*
         * Can be used with or without setting a querySpec.
         * QuerySpec must be set before calling getDistinctValues, e.g.:
         * $client->multimedia()->querySpec($query)->count('creator');
         */
        public function count () {
        	$this->_bootstrap();
         	foreach ($this->_clients as $client) {
         		$url = $this->_nbaUrl . $client . '/count/';
         		if ($this->_querySpec) {
         			$url .= '?_querySpec=' . $this->_querySpec->getQuerySpec();
         		}
         		$this->_channels[] =
        			[
        				'client' => $client,
        				'url' => $url,
        			];
        	}
        	return $this->_queryAndReturnRemoteData();
        }
        
        
        

		/**
		 * Accepts an array of querySpec objects
		 *
		 * @param unknown $queries
		 */
		public function batchQuery ($querySpecs = []) {
			$this->_bootstrap();
			if (count($this->_clients) > 1) {
                throw new \RuntimeException('Error: batch accepts a single client only.');
		    }
		    // Warn for batch size limit if test runs successfully
		    // This is merely an indication -- successs not guaranteed!
		    if (count($querySpecs) > $this->getMaxBatchSize()) {
                throw new \RangeException('Error: batch size too large, maximum exceeds '
                    . $this->getMaxBatchSize() . '.');
		    }
		    $this->_reset();
		    foreach ($querySpecs as $key => $querySpec) {
                if (!$querySpec instanceof QuerySpec) {
                    throw new \InvalidArgumentException('Error: ' . 
                    	'batch array should contain valid querySpec objects.');
    		    }
				$this->_channels[$key] =
					[
						'url' => $this->_nbaUrl . $this->_clients[0] . '/query/' .
                            '?_querySpec=' . $querySpec->getQuerySpec()
					];
            }
            return $this->_queryAndReturnRemoteData();
		}
		
		/*
		 * Maximum of simultaneous requests
		 */
		public function getMaxBatchSize () {
			return $this->_maxBatchSize;
		}
		
		private function _reset () {
		    $reset = ['_remoteData', '_querySpec', '_channels'];
		    foreach ($this as $k => $v) {
		        if (in_array($k, $reset)) {
		            $this->{$k} = null;
		        }
		    }
		}

		private function _bootstrap () {
			// Cannot proceed if no client has been set
			if (empty($this->_clients)) {
				throw new \RuntimeException('Error: client(s) not set.');
			}
			// Test if names service querySpec extension has not been used
			// for other service; this would generate an NBA exception.
			if (!empty($this->_querySpec) && $this->_querySpec->usesSpecimensCriteria()) {
				foreach ($this->_clients as $client) {
					if ($client != 'names') {
						throw new \RuntimeException('Error: querySpec includes criteria ' .
							'that are exclusive to names service, yet ' . $client . 
							' service is queried.');
					}
				}
			}
			return true;
		}
		
		/* 
		 * Shorthand method to query and return the remote data either directly
		 * if a single client has been used, or an array of client => result 
		 * if multiple clients have been used.
		 */
		private function _queryAndReturnRemoteData () {
			$this->_query();
			if (count($this->_channels) == 1) {
				return $this->_remoteData[$this->_clients[0]];
			}
			return $this->_remoteData;
		}
		
		private function _query () {
		    $this->_remoteData = [];
			$mh = curl_multi_init();
			foreach ($this->_channels as $key => $channel) {
				$ch[$key] = curl_init();
				curl_setopt($ch[$key], CURLOPT_URL, $this->_channels[$key]['url']);
        		curl_setopt($ch[$key], CURLOPT_HTTPHEADER, array('Expect:'));
                curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch[$key], CURLOPT_HEADER, false);

			    if (isset($this->_channels[$key]['postfields'])) {
                    curl_setopt($ch[$key], CURLOPT_POST, true);
                    curl_setopt($ch[$key], CURLOPT_POSTFIELDS,
                        $this->_channels[$key]['postfields']);
                }
                if ($this->_nbaTimeout) {
					curl_setopt($ch[$key], CURLOPT_TIMEOUT, $this->_nbaTimeout);
				}
				curl_multi_add_handle($mh, $ch[$key]);
			}
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			while ($active && $mrc == CURLM_OK) {
				if (curl_multi_select($mh) == -1) {
					usleep(1);
				}
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
			foreach ($this->_channels as $key => $channel) {
			    $label = isset($this->_channels[$key]['client']) ?
                    $this->_channels[$key]['client'] : $key;
				$this->_remoteData[$label] = curl_multi_getcontent($ch[$key]);
				curl_multi_remove_handle($mh, $ch[$key]);
			}
			curl_multi_close($mh);
			return $this->_remoteData;
		}

		/*
		 * Config file is parsed and variables are set using private methods.
		 * Function can be called publicly through $setConfig(), but config settings are
		 * applied only if option exists in client.ini.
		 */
		private function _setConfig ($config = false) {
		    $ini = dirname(__FILE__) . '/../../../../config/client.ini';
            if (!file_exists($ini)) {
                throw new \RuntimeException('Error: client.ini is missing! ' .
                    'Please create a copy of "config/client.ini.tpl".');
            }
            $this->_config = parse_ini_file($ini);
            // If $config is provided by user, change settings
            if ($config) {
                foreach ((array) $config as $k => $v) {
                    // _setConfigValue() checks if method and variable exist
                    $val = $this->_setConfigValue($k, $v);
                    if ($val) {
                        $this->_config[$k] = $val;
                    }
                }
            }
		}

 	    /**
         * Method to set a value using the appropriate method.
         * 1. $var nba_do_something must translate to _setNbaDoSomething() method
         * 2. _setNbaDoSomething() must return value that has been set
         */
        private function _setConfigValue ($var, $val) {
            $method = '_set' . ucfirst($this->camelCase($var));
            if (method_exists($this, $method)) {
                $res = $this->{$method}($val);
            }
            return isset($res) ? $res : false;
        }

		private function _setNbaUrl ($url = false) {
		    if (empty($this->_config)) {
                $this->_setConfig();
            }
		    if (!isset($this->_config['nba_url'])) {
                throw new \RuntimeException('Error: nba_url is not set in client.ini!');
            }
            $nbaUrl = $url ? $url : $this->_config['nba_url'];
            // Make sure url is valid and ends with a slash
            if (filter_var($nbaUrl, FILTER_VALIDATE_URL) === false) {
                throw new \InvalidArgumentException('Error: nba_url "' . $nbaUrl . 
                	'" is not a valid url!');
            }
            $this->_nbaUrl = substr($nbaUrl, -1) != '/' ? $nbaUrl . '/' : $nbaUrl;
            return $this->_nbaUrl;
		}

 		private function _setNbaTimeout ($timeout = false) {
		    if (empty($this->_config)) {
                $this->_setConfig();
            }
		    if (!isset($this->_config['nba_timeout'])) {
                throw new \RuntimeException('Error: nba_timeout is not set in client.ini!');
            }
            $nbaTimeout = $timeout ? $timeout : $this->_config['nba_timeout'];
            // Only override default if $nbaTimeout is valid
            if (!$this->isInteger($nbaTimeout) || (int) $nbaTimeout < 0) {
                throw new \InvalidArgumentException('Error: nba_timeout "' . $nbaTimeout .
                    '" is not a valid integer!');
            }
            $this->_nbaTimeout = $nbaTimeout;
            return $this->_nbaTimeout;
		}

 	}
