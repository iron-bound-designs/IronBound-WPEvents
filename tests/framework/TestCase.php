<?php
/**
 * Contains the base TestCase class.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents\Tests\Framework;

/**
 * Class TestCase
 * @package IronBound\WPEvents\Tests\Framework
 */
class TestCase extends \PHPUnit_Framework_TestCase {
	
	protected function setUp() {
		\WP_Mock::setUp();
	}

	protected function tearDown() {
		\WP_Mock::tearDown();
	}
}