<?php
/**
 * Example of some code that could do with test cases
*/
class Example {
	/**
	 * Faulty palindrome checker. A palindrome is a word which is the same when read orward and backward.
	 *
	 * @param string $str
	 * @return boolean True if $str is a palindrome, false if it is not.
	 */
	function isPalindrone($str) {
		$halfLen = (int)(strlen($str) / 2);
		for($i = 0; $i < $halfLen; $i++) {
			// Compare characters from the front and back of the string, and move toward the middle.
			$c1 = substr($str, $i, 1);
			$c2 = substr($str, (2 * $halfLen - 1) - $i, 1);
			if($c1 != $c2) {
				return false;
			}
		}
		return true;
	}

}