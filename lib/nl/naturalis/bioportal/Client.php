<?php
	namespace nl\naturalis\bioportal;
	use nl\naturalis\bioportal\QuerySpec as QuerySpec;
	use nl\naturalis\bioportal\NameGroupQuerySpec as NameGroupQuerySpec;
	use JMS\Serializer\Tests\Fixtures\GetSetObject;
	use phpDocumentor\Plugin\Core\Descriptor\Validator\Constraints\Functions\IsArgumentInDocBlock;
	use Symfony\Component\Finder\Iterator\SizeRangeFilterIterator;
												
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

		/**
		 * NBA clients
		 * 
		 * Currently available clients.
		 * 
		 * @var array
		 */
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
		 * Set ini values for $_nbaUrl, $_nbaTimeout and $_maxBatchSize;
		 * implicitly reads config.ini and set $_config through either method.
		 *
         * @return void
		 */
		public function __construct () {
			parent::__construct();
			$this->setNbaUrl();
            $this->setNbaTimeout(); 
            $this->setMaxBatchSize();
		}
		
		/**
		 * Catch common error where client is set without brackets
		 * 
		 * __get magic method is used to catch the common error where the client 
		 * is set without brackets. In this case, the client is set as an unknown property.
		 * This is matched against the available clients. In case of a match, an exception
		 * is thrown to warn the user.
		 * 
		 * @param string $value
		 * @throws \BadMethodCallException
		 */
		public function __get ($value) {
			if (!property_exists($this, $value) && in_array($value, $this::$nbaClients)) {
				throw new \BadMethodCallException(ucfirst($value) . ' client should be ' .
					"called using ->{$value}()->, not ->{$value}->.");
			}
		}

		/**
		 * Set client to taxon
		 * 
		 * Disables all previously set clients and resets to taxon
		 * 
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function taxon () {
			$this->_clients = [];
			$this->_clients[] = 'taxon';
			return $this;
		}

		/**
		 * Set client to specimen
		 *
		 * Disables all previously set clients and resets to specimen
		 * 
         * @return \nl\naturalis\bioportal\Client
		 */
		public function specimen () {
			$this->_clients = [];
			$this->_clients[] = 'specimen';
			return $this;
		}
		
		/**
		 * Set client to names
		 *
		 * Disables all previously set clients and resets to names
		 * 
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function names () {
			$this->_clients = [];
			$this->_clients[] = 'names';
			return $this;
		}

 		/**
		 * Set client to multimedia
		 *
		 * Disables all previously set clients and resets to multimedia
		 * 
         * @return \nl\naturalis\bioportal\Client
		 */
		public function multimedia () {
			$this->_clients = [];
			$this->_clients[] = 'multimedia';
			return $this;
		}

 		/**
		 * Set client to geo
		 *
		 * Disables all previously set clients and resets to geo
		 * 
         * @return \nl\naturalis\bioportal\Client
		 */
		public function geo () {
			$this->_clients = [];
			$this->_clients[] = 'geo';
			return $this;
		}

		/**
		 * Set all clients for global query
		 *
		 * Sets all clients, allowing for distributed query (mostly metadata queries). 
		 *
         * @return \nl\naturalis\bioportal\Client
		 */
		public function all () {
			$this->_clients = $this::$nbaClients;
			return $this;
		}

		/**
		 * Set QuerySpec object
		 * 
		 * @param object $querySpec QuerySpec object
		 * @throws \InvalidArgumentException In case $querySpec is not a valid QuerySpec object
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function querySpec ($querySpec) {
		    if (!$querySpec || !($querySpec instanceof QuerySpec)) {
                throw new \InvalidArgumentException('Error: invalid querySpec, ' .
                	'should be created using the QuerySpec or NameGroupQuerySpec class.');
		    }
            $this->_querySpec = $querySpec;
            return $this;
		}
		
		/**
		 * Set QuerySpec object
		 * 
		 * Wrapper method for querySpec(); setQuerySpec may be a more logical name...
		 */
		public function setQuerySpec ($querySpec) {
			$this->querySpec($querySpec);
		}

		/**
         * Perform a _querySpec NBA query
         * 
         * 1. Sets the curl channels for one or more clients
         * 2. Performs the NBA query using multicurl
         * 3. Returns NBA response
         *
         * Depending on the number of clients, the result is returned
         * either as json or an array of json responses.
         *
		 * @throws \RuntimeException In case QuerySpec is not set
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients 
		 * (formatted as [client1 => json, client2 => json]).
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
						'?_querySpec=' . $this->_querySpec->getQuerySpec(true)
					];
			}
			return $this->_performQueryAndReturnRemoteData();
		}
		
		/**
		 * Perform a getFieldInfo NBA metadata query
		 *
		 * 1. Sets the curl channels for one or more clients
		 * 2. Performs the NBA query using multicurl
		 * 3. Returns NBA response
		 *
		 * Depending on the number of clients, the result is returned
		 * either as json or an array of json responses.
		 *
		* @param string|array $fields Comma-separated string of fields; if input is 
		 * an array, this is converted to a comma-separated string
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients 
		 * (formatted as [client1 => json, client2 => json]).
		 */
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
			return $this->_performQueryAndReturnRemoteData();
		}
		
		/**
		 * Perform a find/findByIds NBA query
		 * 
		 * Depending on the provided $id(s), either the NBA find or findByIds
		 * method is used to retrieve records. Depending on the number of clients, 
		 * the result is returned either as json or an array of json responses.
		 *
		 * @param string|array $fields Comma-separated string of fields; if input is
		 * an array, this is converted to a comma-separated string
		 * @throws \InvalidArgumentException In case no $id is provided
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients
		 * (formatted as [client1 => json, client2 => json]).
		 */
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
			return $this->_performQueryAndReturnRemoteData();
		}
		
		/**
		 * Get specimen(s) by unitID
		 * 
		 * Unlike find, this method returns a json-encoded object with specimen data, 
		 * extracted from NBA response (so not a direct NBA response). Returns
		 * json-encoded empty array in case nothing is found. Can be called 
		 * with or without setting ->specimen(). Cannot be used with other services,
		 * even if they contain a unitID!
		 * 
		 * @param string $unitId
		 * @throws \InvalidArgumentException In case no $unitId is provided
		 * @throws \RuntimeException In case this method is used for other service 
		 * but specimen
		 * @return string|bool Item from NBA response as json; false if no result
		 */
		public function findByUnitId ($unitId = false) {
			if (!$unitId) {
				throw new \InvalidArgumentException('Error: no UnitID ' .
					'provided for exists/findByUnitId method.');
			}
			// Only works for specimens; return exception if called for other service
			if (!empty($this->_clients) && !in_array('specimen', $this->_clients)) {
				throw new \RuntimeException('Error: exists/findByUnitId method ' .
					'can only be used to query specimens.');
			}
			$this->_reset();
			$query = new QuerySpec();
			$query
				->addCondition(new Condition('unitID', 'EQUALS_IC', $unitId))
				->setConstantScore();
			$this->_channels[] =
				[
					'url' => $this->_nbaUrl . 'specimen/query/?_querySpec=' .
						$query->getQuerySpec(true)
				];
			$this->_query();
			$data = json_decode($this->_remoteData[0]);
			return isset($data->resultSet[0]->item) ?
				json_encode($data->resultSet[0]->item) : false;
		}
		
		/**
		 * Determine if specimen with given unitID exists
		 * 
		 * @param string $unitId
		 * @return boolean
		 */
		public function exists ($unitId = false) {
			return !empty(json_decode($this->findByUnitId($unitId))) ;
		}
		
		/**
		 * Perform an getNamedCollections NBA query
		 * 
		 * Uses the NBA query getNamedCollections to get all "special collections" 
		 * defined within the specimen dataset. Can be called with or without 
		 * setting ->specimen().
		 * 
		 * @return string Collections as json-encoded string
		 */
		public function getNamedCollections () {
			$this->_channels = [];
			$this->_channels[] = ['url' => $this->_nbaUrl . 'specimen/getNamedCollections'];
			$this->_query();
			return $this->_remoteData[0];
		}
		
		/**
		 * Retrieve NBA geo areas 
		 * 
		 * Output is comparable to getDistinctValuesPerGroup() in the Java client. 
		 * This method additionally inserts both the id and Dutch name, which are absent 
		 * from the getDistinctValuesPerGroup() output. The results are formatted in a 
		 * slightly different way, grouping localities per language.
		 * 
		 * @return string|bool Result as json-encoded string; false if no result
		 */
		public function getGeoAreas ($trimGidSuffix = false) {
			$query = new QuerySpec();
			$query
				->setSize(2000)
				->setFields(['sourceSystemId', 'areaType', 'locality', 'countryNL'])
				->setConstantScore();
			$data = json_decode($this->geo()->querySpec($query)->query());
			if (isset($data->resultSet)) {
				// Enhance data
				foreach ($data->resultSet as $i => $row) {
					// Strip @GEO off id because this causes problems with CSS and jQuery
					$result[$row->item->areaType][$i]['id'] = $trimGidSuffix ?
						strstr($row->item->id, '@', true) : $row->item->id;
					$result[$row->item->areaType][$i]['locality']['en'] =
						$row->item->locality;
					$result[$row->item->areaType][$i]['locality']['nl'] =
						!empty($row->item->countryNL) && $row->item->countryNL != '\N' ?
						$row->item->countryNL : $row->item->locality;
				}
			}
			return isset($result) ? json_encode($result) : false;
		}
		
		/**
		 * Perform a getGeoJsonForLocality NBA query
		 * 
		 * The NBA method is case-sensitive. Use a dedicated query to check if a locality 
		 * exists in the geo index.
		 * 
		 * @param string $locality
		 * @throws \InvalidArgumentException In case of empty $locality
		 * @return string|bool Returns geojson; false if no result
		 */
		public function getGeoJsonForLocality ($locality) {
			if (!$locality) {
				throw new \InvalidArgumentException('Error: no locality provided for ' .
					'getGeoJsonForLocality.');
			}
			$this->_reset();
			$this->_channels[] = ['url' => $this->_nbaUrl . 'geo/getGeoJsonForLocality/' . 
				$locality];
			$this->_query();
			$data = json_decode($this->_remoteData[0]);
			return isset($data->coordinates) ? $this->_remoteData[0] : false;
		}
		
    	 /**
		 * Shorthand method to return geojson for a geographic NBA id
		 * 
		 * Does a find and returns the shape of the geo object found.
		 * 
		 * @param string $gid
		 * @throws \InvalidArgumentException In case of empty $gid
		 * @return string|bool Returns geojson; false if no result
		 * @see \nl\naturalis\bioportal\Client::getGeoAreas()
		 */
		public function getGeoJsonForGid ($gid) {
			if (!$gid) {
				throw new \InvalidArgumentException('Error: no geographic id 
					provided for getGeoJsonForGid.');
			}
			$this->_reset();
			// Test if id suffix has not been stripped in getGeoAreas(); append if so
			$gid .= strpos($gid, '@') === false ? '@GEO' : '';
			$data = json_decode($this->geo()->find($gid));
			return isset($data->shape) ? json_encode($data->shape) : false;
		}
		
		/**
		 * Performs a getSpeciesWithSpecimens NBA query
		 * 
		 * A critically import method for BioPortal: it returns specimens aggregated by taxon.
		 * This is an extension of the regular names service query, which will return _all_
		 * specimens for a particular taxon. This method filters out those specimens per taxon
		 * that do not match the search criteria. Can be used without setting the names 
		 * service first. Make sure to pass a NameGroupQuerySpec instead of a 
		 * regular QuerySpec.
		 * 
		 * @param string $nameGroupQuerySpec NameGroupQuerySpec
		 * @return string NBA response as json
		 */
		public function getSpeciesWithSpecimens ($nameGroupQuerySpec = false) {
			$this->_reset();
			if ($nameGroupQuerySpec) {
				$this->querySpec($nameGroupQuerySpec);
			}
			$url = $this->_nbaUrl . 'names/getSpeciesWithSpecimens/';
			if ($this->_querySpec) {
				$url .= '?_querySpec=' . $this->_querySpec->getQuerySpec(true);
			}
			$this->_channels[] = ['url' => $url];
			$this->_query();
			return $this->_remoteData[0];
		}
				
		/**
		 * Perform a getDistinctValues NBA query
		 * 
		 * Uses NBA getDistinctValues method to get distinct values for a specific field.
		 * Supports multiple clients in case the same fields occurs in multiple services. 
		 * Can be used with or without setting a QuerySpec. The QuerySpec must be set 
		 * _before_ calling getDistinctValues. Depending on the number of clients, 
		 * the result is returned either as json or an array of json responses.
		 *
		 * @param string $field
		 * @example $client->multimedia()->querySpec($query)->getDistinctValues('creator');
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients
		 * (formatted as [client1 => json, client2 => json]).
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
					$url .= '?_querySpec=' . $this->_querySpec->getQuerySpec(true);
				}
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $url,
					];
			}
			return $this->_performQueryAndReturnRemoteData();
		}
		
		/**
		 * Perform an NBA count query
		 * 
		 * Uses NBA count method to count number of results. Supports multiple clients. 
		 * Can be used with or without setting a QuerySpec. The QuerySpec must be set 
		 * _before_ calling count. Depending on the number of clients, the result is 
		 * returned either as json or an array of json responses.
		 *
		 * @example $client->multimedia()->querySpec($query)->count('creator');
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients
		 * (formatted as [client1 => json, client2 => json]).
		 */
		public function count () {
			$this->_bootstrap();
			foreach ($this->_clients as $client) {
				$url = $this->_nbaUrl . $client . '/count/';
				if ($this->_querySpec) {
					$url .= '?_querySpec=' . $this->_querySpec->getQuerySpec(true);
				}
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $url,
					];
			}
			return $this->_performQueryAndReturnRemoteData();
		}
		
		
		/**
		 * Perform an NBA getPaths metadata query
		 * 
		 * Gets NBA path for the selected service(s)
		 * 
		 * @param string $sort Sort alphabetically?
		 * @return string|string[] NBA response as json if a single client has been
		 * set, or as an array of responses in case of multiple clients
		 * (formatted as [client1 => json, client2 => json]).
		 */
		public function getPaths ($sort = false) {
			$this->_bootstrap();
			foreach ($this->_clients as $client) {
				$url = $this->_nbaUrl . $client . '/metadata/getPaths';
				if ($sort) {
					$url .= '/?sorted=true';
				}
				$this->_channels[] =
					[
						'client' => $client,
						'url' => $url,
					];
			}
			return $this->_performQueryAndReturnRemoteData();
		}
		
		/**
		 * Perform NBA queries in batch
		 * 
		 * Takes an array of QuerySpecs and simultaneously queries the NBA. The
		 * maximum number of queries is capped by $_maxBatchSize. The more
		 * queries in batch, the more memory is used. This method only takes
		 * a single client. The array with results uses the same keys as the
		 * input array of QuerySpec objects.
		 * 
		 * @param array $querySpecs Array of QuerySpec objects
		 * @throws \RuntimeException In case multiple clients are provided
		 * @throws \RangeException In case batch size exceeds $_maxBatchSize
		 * @throws \InvalidArgumentException In case of invalid QuerySpec object
		 * @return string[] NBA response as an array of responses 
		 * (formatted as [key1 => json, key2 => json]).
		 */
		public function batchQuery ($querySpecs = []) {
			if (empty($this->_clients)) {
				throw new \RuntimeException('Error: batch client not set.');
			}
			if (count($this->_clients) > 1) {
				throw new \RuntimeException('Error: batch accepts a single client only.');
			}
			// Warn for batch size limit if test runs successfully
			// This is merely an indication -- successs not guaranteed!
			if (count($querySpecs) > $this->getMaxBatchSize()) {
				throw new \RangeException('Error: batch size too large, maximum exceeds ' . 
					$this->getMaxBatchSize() . '.');
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
							'?_querySpec=' . $querySpec->getQuerySpec(true)
					];
			}
			$this->_query();
			return $this->_remoteData;
		}
		
		/**
		 * Get all publicly available clients
		 *
		 * @return array All clients
		 */
		public function getAllClients () {
			return $this::$nbaClients;
		}
		
		/**
		 * Get current clients
		 *
		 * @return array Set clients
		 */
		public function getClients () {
			return $this->_clients;
		}
		
		/**
		 * Get $_querySpec
		 *
         * Gets QuerySpec either as json or url-encoded json (default).
         * 
		 * @param string $encoded Url encode QuerySpec json-encoded string?
		 * @return string|boolean $_querySpec QuerySpec as json-encoded string
		 */
		public function getQuerySpec ($encoded = false) {
			return !$encoded ? $this->_querySpec->getQuerySpec() :
				$this->_querySpec->getQuerySpec(true);
		}
		
		/**
		 * Get current configuration
         *
		 * @return string Configuration settings as json-encoded string
		 */
		public function getConfig () {
		    return json_encode($this->_config);
		}

		/**
		 * Set $_nbaUrl configuration setting, overriding setting in client.ini
		 * 
		 * 1. Reads client.ini and sets $_config if this hasn't been set prior
		 * 2. Validates url
		 * 3. Overrides client.ini setting, appending slash if missing
		 * 
		 * @param string $url
		 * @throws \RuntimeException In case client.ini does not contain this setting
		 * @throws \InvalidArgumentException In case url is invalid
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function setNbaUrl ($url = false) {
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
			return $this;
		}
		
		/**
		 * Get $_nbaUrl configuration setting
		 * 
		 * @return string NBA url
		 */
		public function getNbaUrl () {
			return $this->_nbaUrl;
		}

		/**
		 * Set $_nbaTimeout configuration setting, overriding setting in client.ini
		 * 
		 * 1. Reads client.ini and sets $_config if this hasn't been set prior
		 * 2. Validates timeout (accepts string if this can be cast to proper integer)
		 * 3. Overrides client.ini setting
		 * 
		 * @param int|string $timeout
		 * @throws \RuntimeException In case client.ini does not contain this setting
		 * @throws \InvalidArgumentException In case $timeout is invalid
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function setNbaTimeout ($timeout = false) {
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
			return $this;
		}
		
		/**
		 * Get $_nbaTimeout configuration setting
         *
         * @return int $_nbaTimeout
		 */
		public function getNbaTimeout () {
 	 	    return $this->_nbaTimeout;
		}
		
		/**
		 * Set $_maxBatchQuery configuration setting, overriding setting in client.ini
		 *
		 * 1. Reads client.ini and sets $_config if this hasn't been set prior
		 * 2. Validates size (accepts string if this can be cast to proper integer)
		 * 3. Overrides client.ini setting
		 *
		 * @param int|string $size
		 * @throws \RuntimeException In case client.ini does not contain this setting
		 * @throws \InvalidArgumentException In case $size is invalid
		 * @return \nl\naturalis\bioportal\Client
		 */
		public function setMaxBatchSize ($size = false) {
			if (empty($this->_config)) {
				$this->_setConfig();
			}
			if (!isset($this->_config['max_batch_size'])) {
				throw new \RuntimeException('Error: max_batch_size is not set in client.ini!');
			}
			$maxBatchSize = $size ? $size : $this->_config['max_batch_size'];
			// Only override default if $nbaTimeout is valid
			if (!$this->isInteger($maxBatchSize) || (int) $maxBatchSize < 0) {
				throw new \InvalidArgumentException('Error: max_batch_size "' . $maxBatchSize .
					'" is not a valid integer!');
			}
			$this->_maxBatchSize = $maxBatchSize;
			return $this;
		}

		/**
		 * Get $_maxBatchSize configuration setting
		 *
		 * @return int $_maxBatchSize
		 */
		public function getMaxBatchSize () {
			return $this->_maxBatchSize;
		}
		
		/*
		 * Check if clients have been set and if names service specific parameters
		 * are not used for other service.
		 */
		private function _bootstrap () {
			// Cannot proceed if no client has been set
			if (empty($this->_clients)) {
				throw new \RuntimeException('Error: client(s) not set.');
			}
			// Names service requires NameGroupQuerySpec
			if (!empty($this->_querySpec) && 
				!($this->_querySpec instanceof NameGroupQuerySpec) && 
				in_array('names', $this->_clients)) {
				throw new \RuntimeException('Error: names service requires NameGroupQuerySpec ' .
					'instead of QuerySpec (offering dedicated methods for paginating and sorting).');
			}
			// NameGroupQuerySpec can only be used for names service
			if (!empty($this->_querySpec) && 
				$this->_querySpec instanceof NameGroupQuerySpec) {
				foreach ($this->_clients as $client) {
					if ($client != 'names') {
						throw new \RuntimeException('Error: NameGroupQuerySpec ' .
							'used for ' . $client . ' service. NameGroupQuerySpec ' .
							'is strictly used for names service.');
					}
				}
			}
			return true;
		}
		
		/*
		 * Reset values
		 */
		private function _reset () {
		    $reset = ['_remoteData', '_querySpec', '_channels'];
		    foreach ($this as $k => $v) {
		        if (in_array($k, $reset)) {
		            $this->{$k} = null;
		        }
		    }
		}

		/* 
		 * Shorthand method to query and return the remote data either directly
		 * if a single client has been used, or an array of client => result 
		 * if multiple clients have been used.
		 */
		private function _performQueryAndReturnRemoteData () {
			$this->_query();
			if (count($this->_channels) == 1) {
				return $this->_remoteData[$this->_clients[0]];
			}
			return $this->_remoteData;
		}
		
		/*
		 * Multicurl query
		 */
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
		 * Parse client.ini file and sets $_config parameters.
		 */
		private function _setConfig ($config = false) {
		    $ini = dirname(__FILE__) . '/../../../../config/client.ini';
            if (!file_exists($ini)) {
                throw new \RuntimeException('Error: client.ini is missing! ' .
                    'Please create a copy of "config/client.ini.tpl".');
            }
            $this->_config = parse_ini_file($ini);
		}

 
 	}
