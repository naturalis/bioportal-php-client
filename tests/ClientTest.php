<?php
namespace nl\naturalis\bioportal\Test;
use nl\naturalis\bioportal\Condition as Condition;
use nl\naturalis\bioportal\QuerySpec as QuerySpec;
use nl\naturalis\bioportal\Client as Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
	/*
	 * Clients
	 */
	public function testSetTaxonClient () {
		$expected = json_encode(['taxon']);
		$client = new Client();
		$client->taxon();
		$this->assertEquals($expected, $client->getClients());
	}

	public function testSetTaxonClientWithoutBrackets () {
		$e = new \stdClass();
		try {
			$client = new Client();
			$client->taxon;
		} catch (\Exception $e) {}
		$this->assertEquals('BadMethodCallException', get_class($e));
	}
	
	/*
	 * NBA url
	 */
	public function testSetNbaUrlInputWithoutSlash () {
		$expected = 'http://api.biodiversitydata.nl/v2/';
		$client = new Client();
		$client->setNbaUrl('http://api.biodiversitydata.nl/v2');
		$this->assertEquals($expected, $client->getNbaUrl());
	}

	public function testSetNbaUrlWithIncorrectUrl () {
		$e = new \stdClass();
		try {
			$client = new Client();
			$client->setNbaUrl('www.incorrect-url.nl');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * QuerySpec
	 */
	public function testSetCorrectlyConstructedQuerySpec () {
		$expected = '{"conditions":[{"field":"sourceSystem.name","operator":"EQUALS",' .
				'"value":"Naturalis - Nederlands Soortenregister"},{"field":"defaultClassification.genus",' .
				'"operator":"EQUALS_IC","value":"larus","or":[{"field":"acceptedName.genusOrMonomial",' .
				'"operator":"LIKE","value":"larus"}]}],"from":5,"logicalOperator":"AND","size":25}';
		$c = new Condition('defaultClassification.genus', 'EQUALS_IC', 'larus');
		$c->addOr('acceptedName.genusOrMonomial', 'LIKE', 'larus');
		$d = new Condition('sourceSystem.name', 'EQUALS', 'Naturalis - Nederlands Soortenregister');
		$query = new QuerySpec();
		$query
			->setFrom('5')
			->setSize(25)
			->setLogicalOperator('and')
			->addCondition($d)
			->addCondition($c);
		$client = new Client();
		$client->querySpec($query);
		$this->assertEquals($expected, $client->getQuerySpec());
	}

	public function testSetQuerySpecAsJsonString () {
		$query = '{"conditions":[{"field":"sourceSystem.name","operator":"EQUALS",' .
				'"value":"Naturalis - Nederlands Soortenregister"},{"field":"defaultClassification.genus",' .
				'"operator":"EQUALS_IC","value":"larus","or":[{"field":"acceptedName.genusOrMonomial",' .
				'"operator":"LIKE","value":"larus"}]}],"from":5,"logicalOperator":"AND","size":25}';
		$e = new \stdClass();
		try {
			$client = new Client();
			$client->querySpec($query);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * Batch query
	 */
	public function testBatchQuerySizeExceedsMaxBatchSize () {
		$query = new QuerySpec();
		$client = new Client();
		for ($i = 0; $i < $client->getMaxBatchSize() + 1; $i++) {
			$batch[] = $query;
		}
		$e = new \stdClass();
		try {
			$result = $client->taxon()->batchQuery($batch);
		} catch (\Exception $e) {}
		$this->assertEquals('RangeException', get_class($e));
	}

	/*
	 * Config
	 */
	public function testSetNbaTimeoutWithValidInteger () {
		$expected = 30;
		$client = new Client();
		$client->setNbaTimeout(30);
		$this->assertEquals($expected, $client->getNbaTimeout());
	}

	public function testSetNbaTimeoutWithInvalidInteger () {
		$e = new \stdClass();
		try {
			$client = new Client();
			$client->setNbaTimeout(-1);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	
}