<?php
/**
 * Contains tests for the GenericEvent class.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents\Tests;

use IronBound\WPEvents\GenericEvent;
use IronBound\WPEvents\Tests\Framework\TestCase;

/**
 * Class TestGenericEvent
 * @package IronBound\WPEvents\Tests
 *
 * This is from the Symfony EventDispatcher package which is under the MIT license.
 */
class TestGenericEvent extends TestCase {

	/**
	 * @var GenericEvent
	 */
	private $event;

	private $subject;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();

		$this->subject = new \stdClass();
		$this->event   = new GenericEvent( $this->subject, array( 'name' => 'Event' ) );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->subject = null;
		$this->event   = null;

		parent::tearDown();
	}

	public function testConstruct() {
		$this->assertEquals( $this->event, new GenericEvent( $this->subject, array( 'name' => 'Event' ) ) );
	}

	/**
	 * Tests Event->getArgs().
	 */
	public function testGetArguments() {
		// test getting all
		$this->assertSame( array( 'name' => 'Event' ), $this->event->get_arguments() );
	}

	public function testSetArguments() {
		$result = $this->event->set_arguments( array( 'foo' => 'bar' ) );
		$this->assertAttributeSame( array( 'foo' => 'bar' ), 'arguments', $this->event );
		$this->assertSame( $this->event, $result );
	}

	public function testSetArgument() {
		$result = $this->event->set_argument( 'foo2', 'bar2' );
		$this->assertAttributeSame( array( 'name' => 'Event', 'foo2' => 'bar2' ), 'arguments', $this->event );
		$this->assertEquals( $this->event, $result );
	}

	public function testGetArgument() {
		// test getting key
		$this->assertEquals( 'Event', $this->event->get_argument( 'name' ) );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetArgException() {
		$this->event->get_argument( 'nameNotExist' );
	}

	public function testOffsetGet() {
		// test getting key
		$this->assertEquals( 'Event', $this->event['name'] );

		// test getting invalid arg
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->assertFalse( $this->event['nameNotExist'] );
	}

	public function testOffsetSet() {
		$this->event['foo2'] = 'bar2';
		$this->assertAttributeSame( array( 'name' => 'Event', 'foo2' => 'bar2' ), 'arguments', $this->event );
	}

	public function testOffsetUnset() {
		unset( $this->event['name'] );
		$this->assertAttributeSame( array(), 'arguments', $this->event );
	}

	public function testOffsetIsset() {
		$this->assertTrue( isset( $this->event['name'] ) );
		$this->assertFalse( isset( $this->event['nameNotExist'] ) );
	}

	public function testHasArgument() {
		$this->assertTrue( $this->event->has_argument( 'name' ) );
		$this->assertFalse( $this->event->has_argument( 'nameNotExist' ) );
	}

	public function testGetSubject() {
		$this->assertSame( $this->subject, $this->event->get_subject() );
	}

	public function testHasIterator() {
		$data = array();
		foreach ( $this->event as $key => $value ) {
			$data[ $key ] = $value;
		}
		$this->assertEquals( array( 'name' => 'Event' ), $data );
	}
}