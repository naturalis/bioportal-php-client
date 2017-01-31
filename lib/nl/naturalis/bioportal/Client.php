<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\QuerySpec as QuerySpec;

    final class Client extends AbstractClass
 	{
		private $_nbaUrl;
		private $_nbaTimeout = 5;
		private $_config;

		private $_querySpec;
		private $_channels;
		private $_remoteData;

		public static $nbaClients = [
			'taxon',
			'multimedia',
			'specimen',
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
            $this->_setNbaUrl();
            $this->_setNbaTimeout();
		}

		/**
		 * Sets the client to taxon
		 *
         * @return Returns this instance
		 */
		public function taxon () {
			$this->clients = [];
			$this->clients[] = 'taxon';
			return $this;
		}

		/**
		 * Sets the client to specimen
		 *
         * @return Returns this instance
		 */
		public function specimen () {
			$this->clients = [];
			$this->clients[] = 'specimen';
			return $this;
		}

 			/**
		 * Sets the client to multimedia
		 *
         * @return class This class (allowing chaining)
		 */
		public function multimedia () {
			$this->clients = [];
			$this->clients[] = 'multimedia';
			return $this;
		}

 		/**
		 * Sets the client to geo
		 *
         * @return class This class (allowing chaining)
		 */
		public function geo () {
			$this->clients = [];
			$this->clients[] = 'geo';
			return $this;
		}

		/**
		 * Sets all three clients
		 *
		 * Sets taxon, specimen and multimedia clients (so omits geo!),
		 * allowing distributed query; does not verify query, so use with care!
		 *
         * @return class This class (allowing chaining)
		 */
		public function all () {
			$this->clients = array_diff($this::$nbaClients, array('geo'));
			return $this;
		}

		/**
		 * Returns publicly available clients
		 *
         * @return class This class (allowing chaining)
		 */
		public function getAllClients () {
			return (object) $this::$nbaClients;
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
		public function querySpec ($spec) {
		    if (!$spec || !($spec instanceof QuerySpec)) {
                throw new \Exception('Error: invalid querySpec, should be created ' .
                    'using the QuerySpec class.');
		    }
            $this->_querySpec = $spec->getSpec();
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
			$this->setClientChannels();
			$this->_query();
			if (count($this->_channels) == 1) {
                return $this->_remoteData[$this->clients[0]];
            }
            return $this->_remoteData;
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
		    return !$encoded ? urldecode($this->_querySpec) : $this->_querySpec;
		}

		public function getMapping () {
			$this->_channels = [];
			foreach ($this->clients as $client) {
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $this->_nbaUrl . $client . '/metadata/getMapping',
					];
			}
            $this->_query();
            if (count($this->_channels) == 1) {
                return $this->_remoteData[$this->clients[0]];
            }
            return $this->_remoteData;
		}


		/*
		 * Convenience method to retrieve geo areas. The output is comparable to
		 * getDistinctValuesPerGroup() in the java client. This method additionally
		 * includes both the id and Dutch name, which are absent from the
		 * getDistinctValuesPerGroup() output.
		 */
		public function getGeoAreas () {
		    $query = new QuerySpec();
            $query->setSize(2000)
                  ->setFields(['sourceSystemId', 'areaType', 'locality', 'countryNL']);
			$data = json_decode($this->geo()->querySpec($query)->query(), true);
			// Enhance data
            foreach ($data['resultSet'] as $i => $row) {
                $result[$row['areaType']][$i]['id'] = $row['id'];
                $result[$row['areaType']][$i]['locality_en'] = $row['locality'];
                $result[$row['areaType']][$i]['locality_nl'] =
                    !empty($row['countryNL']) && $row['countryNL'] != '\N' ?
                        $row['countryNL'] : $row['locality'];
            }
            return isset($result) ? json_encode($result) : false;
		}

        /*
         * Use GET
         */
		private function setClientChannels () {
			$this->_channels = [];
			foreach ($this->clients as $client) {
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $this->_nbaUrl . $client . '/query/' .
                            '?_querySpec=' . $this->_querySpec
					];
			}
			return $this->_channels;
		}

		// Assumes queryData has been set as array values
		private function setBatchChannels ($d = []) {
            foreach ((array) $d as $k => $v) {
                $this->_channels[] =
    				[
						'url' => $this->nbaUrl . $this->clients[0] . '/query/',
						'postfields' => $v
    				];
            }
		}

		private function _query () {
			$this->_remoteData = [];
			$mh = curl_multi_init();
			for ($i = 0; $i < count($this->_channels); $i++) {
				$ch[$i] = curl_init();
				curl_setopt($ch[$i], CURLOPT_URL, $this->_channels[$i]['url']);
        		curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array('Expect:'));
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch[$i], CURLOPT_HEADER, false);
			    if (isset($this->_channels[$i]['postfields'])) {
                    curl_setopt($ch[$i], CURLOPT_POST, true);
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $this->_channels[$i]['postfields']);
                }
                if ($this->_nbaTimeout) {
					curl_setopt($ch[$i], CURLOPT_TIMEOUT, $this->_nbaTimeout);
				}
				curl_multi_add_handle($mh, $ch[$i]);
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
			for ($i = 0; $i < count($this->_channels); $i++) {
			    $key = isset($this->_channels[$i]['client']) ? $this->_channels[$i]['client'] : $i;
				$this->_remoteData[$key] = curl_multi_getcontent($ch[$i]);
				curl_multi_remove_handle($mh, $ch[$i]);
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
                throw new \Exception('Error: client.ini is missing! ' .
                    'Please create a copy of "config/client.ini.tpl".');
            }
            $this->_config = parse_ini_file($ini);
            // If $config is provided by user, change settings
            if ($config) {
                foreach ((array) $config as $k => $v) {
                    // setConfigValue() checks if method and variable exist
                    $val = $this->setConfigValue($k, $v);
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
        private function setConfigValue ($var, $val) {
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
                throw new \Exception('Error: nba_url is not set in client.ini!');
            }
            $nbaUrl = $url ? $url : $this->_config['nba_url'];
            // Make sure url contains "http" and ends with a slash
            if (strpos($nbaUrl, 'http') === false) {
                throw new \Exception('Error: nba_url "' . $nbaUrl . '" is not a valid url!');
                return false;
            }
            $this->_nbaUrl = substr($nbaUrl, -1) != '/' ? $nbaUrl . '/' : $nbaUrl;
            return $this->_nbaUrl;
		}

 		private function _setNbaTimeout ($timeout = false) {
		    if (empty($this->_config)) {
                $this->_setConfig();
            }
		    if (!isset($this->_config['nba_timeout'])) {
                throw new \Exception('Error: nba_timeout is not set in client.ini!');
            }
            $nbaTimeout = $timeout ? $timeout : $this->_config['nba_timeout'];
            // Only override default if $nbaTimeout is valid
            if (!$this->isInteger($nbaTimeout) || (int) $nbaTimeout < 0) {
                throw new \Exception('Error: nba_timeout "' . $nbaTimeout .
                    '" is not a valid integer!');
                return false;
            }
            $this->_nbaTimeout = $nbaTimeout;
            return $this->_nbaTimeout;
		}
 	}
