<?php
    namespace nl\naturalis\bioportal;
    use nl\naturalis\bioportal\QuerySpec as QuerySpec;
    use Exception;

    class Client
 	{
		private $_baseUrl;
		private $_querySpec;
		private $_channels;
		private $_curlTimeout;
		private $_remoteData;

		public static $nbaClients = [
			'taxon',
			'multimedia',
			'specimen',
		    'geo'
		];


    	public function setBaseUrl ($a) {
			$this->baseUrl = (string) $a;
		}

		public function taxon () {
			$this->clients = [];
			$this->clients[] = 'taxon';
		}

		public function specimen () {
			$this->clients = [];
			$this->clients[] = 'specimen';
		}

		public function multimedia () {
			$this->clients = [];
			$this->clients[] = 'multimedia';
		}

		public function all () {
			$this->clients = $this::$nbaClients;
		}

		public function getAllClients () {
			return (object) $this::$nbaClients;
		}

		public function setQuerySpec ($a) {
			$this->_querySpec = $a;
		}

		public function query () {
			$this->setClientChannels();
			$this->queryNda();
			die(print_r($this->_remoteData));
		}

		private function setClientChannels () {
			foreach ($this->clients as $client) {
				$this->channels[] =
					[
						'client' => $client,
						'url' => $this->baseUrl . $client . '/query/',
						'postfields' => $this->setQuery()
					];
			}
			return $this->channels;
		}

		// Assumes queryData has been set as array values
		private function setBatchChannels ($d = []) {
            foreach ((array) $d as $k => $v) {
                $this->channels[] =
    				[
						'url' => $this->baseUrl . $this->clients[0] . '/query/',
						'postfields' => $v
    				];
            }
		}

		private function setQuery () {
			$this->_query = '_querySpec=' . urlencode(json_encode($this->_querySpec));
			return $this->_query;
		}


		private function queryNda () {
			$this->_remoteData = [];
			$mh = curl_multi_init();
			for ($i = 0; $i < count($this->channels); $i++) {
				$ch[$i] = curl_init();
				curl_setopt($ch[$i], CURLOPT_URL, $this->channels[$i]['url']);
				curl_setopt($ch[$i], CURLOPT_POST, true);
				curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $this->channels[$i]['postfields']);
				curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array('Expect:'));
				curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch[$i], CURLOPT_HEADER, false);
				if ($this->_curlTimeout) {
					curl_setopt($ch[$i], CURLOPT_TIMEOUT, $this->_curlTimeout);
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
			for ($i = 0; $i < count($this->channels); $i++) {
			    $key = isset($this->channels[$i]['client']) ? $this->channels[$i]['client'] : $i;
				$this->_remoteData[$key] = curl_multi_getcontent($ch[$i]);
				curl_multi_remove_handle($mh, $ch[$i]);
			}
			curl_multi_close($mh);
			return $this->_remoteData;
		}
	}
