<?php
class ExampleTest extends PHPUnit_Framework_TestCase {
	public function testIsPalindrome() {
		$e = new Example();
		$this -> assertTrue($e -> isPalindrone(""));
		$this -> assertTrue($e -> isPalindrone("a"));
		$this -> assertTrue($e -> isPalindrone("ABBA"));
		$this -> assertFalse($e -> isPalindrone("test"));
	}
}