<?php
/**
 * Test the EventDisaptcher class.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents\Tests;

use IronBound\WPEvents\Event;
use IronBound\WPEvents\EventDispatcher;
use IronBound\WPEvents\EventSubscriber;
use IronBound\WPEvents\Exception\InvalidListenerException;
use IronBound\WPEvents\GenericEvent;
use IronBound\WPEvents\Tests\Framework\TestCase;
use WP_Mock\Functions;

/**
 * Class EventDispatcher
 * @package IronBound\WPEvents\Tests
 */
class TestEventDispatcher extends TestCase {

	public function test_dispatch() {

		$dispatcher = new EventDispatcher( 'my_prefix.' );

		$event = new Event();

		\WP_Mock::expectAction( 'my_prefix.event', $event, 'event', $dispatcher );

		$this->assertEquals( $event, $dispatcher->dispatch( 'event', $event ) );
	}

	public function test_dispatch_event_constructed_if_none_provided() {

		$dispatcher = new EventDispatcher( 'my_prefix.' );

		\WP_Mock::expectAction( 'my_prefix.event', Functions::type( 'IronBound\WPEvents\Event' ), 'event', $dispatcher );

		$this->assertInstanceOf( 'IronBound\WPEvents\Event', $dispatcher->dispatch( 'event' ) );
	}

	public function test_filter_with_simple_value() {

		$dispatcher = new EventDispatcher( 'my_prefix.' );

		\WP_Mock::onFilter( 'my_prefix.filter' )
		        ->with( 'value', Functions::type( 'IronBound\WPEvents\Event' ), 'filter', $dispatcher )
		        ->reply( 'newValue' );

		$this->assertEquals( 'newValue', $dispatcher->filter( 'filter', 'value' ) );
	}

	public function test_filter_where_value_retrieve_from_generic_event() {

		$event = new GenericEvent( 'value' );

		$dispatcher = new EventDispatcher( 'my_prefix.' );

		\WP_Mock::onFilter( 'my_prefix.filter' )
		        ->with( 'value', $event, 'filter', $dispatcher )
		        ->reply( 'newValue' );

		$this->assertEquals( 'newValue', $dispatcher->filter( 'filter', $event ) );
	}

	public function test_filter_with_custom_event() {

		$event = new Event();

		$dispatcher = new EventDispatcher( 'my_prefix.' );

		\WP_Mock::onFilter( 'my_prefix.filter' )
		        ->with( 'value', $event, 'filter', $dispatcher )
		        ->reply( 'newValue' );

		$this->assertEquals( 'newValue', $dispatcher->filter( 'filter', 'value', $event ) );
	}

	/**
	 * @expectedException \IronBound\WPEvents\Exception\InvalidListenerException
	 */
	public function test_add_listener_invalid_listener_rejected() {

		$dispatcher = new EventDispatcher();
		$dispatcher->add_listener( 'event', new \stdClass() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_add_listener_invalid_priority() {

		$dispatcher = new EventDispatcher();
		$dispatcher->add_listener( 'event', 'strlen', 'stuff' );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_add_listener_invalid_accepted_args() {

		$dispatcher = new EventDispatcher();
		$dispatcher->add_listener( 'event', 'strlen', 10, 'stuff' );
	}

	public function test_add_listener_event_not_prefixed() {

		\WP_Mock::expectFilterAdded( 'wp_head', 'myFunction', 5, 2 );
		\WP_Mock::expectFilterAdded( 'custom_event', 'myCustomFunction', 5, 2 );

		$dispatcher = new EventDispatcher( 'my_prefix.' );
		$dispatcher->add_listener( 'wp_head', 'myFunction', 5, 2 );
		$dispatcher->add_listener( 'custom_event', 'myCustomFunction', 5, 2 );
	}

	public function test_add_listener_with_prefix() {

		\WP_Mock::expectFilterAdded( 'my_prefix.event', 'myFunction', 5, 2 );

		$dispatcher = new EventDispatcher( 'my_prefix.' );
		$dispatcher->add_listener( 'my_prefix.event', 'myFunction', 5, 2 );
	}

	/**
	 * @expectedException \IronBound\WPEvents\Exception\InvalidListenerException
	 */
	public function test_remove_listener_invalid_listener_rejected() {

		$dispatcher = new EventDispatcher();
		$dispatcher->remove_listener( 'event', new \stdClass() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_remove_listener_invalid_priority() {

		$dispatcher = new EventDispatcher();
		$dispatcher->remove_listener( 'event', 'strlen', 'stuff' );
	}

	public function test_remove_listener_event_not_prefixed() {

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'wp_head', 'myFunction', 5 )
		) );

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'custom_event', 'myCustomFunction', 5 )
		) );

		$dispatcher = new EventDispatcher( 'my_prefix.' );
		$dispatcher->remove_listener( 'wp_head', 'myFunction', 5 );
		$dispatcher->remove_listener( 'custom_event', 'myCustomFunction', 5 );
	}

	public function test_remove_listener_with_prefixed() {

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'my_prefix.event', 'myFunction', 5 )
		) );

		$dispatcher = new EventDispatcher( 'my_prefix.' );
		$dispatcher->remove_listener( 'my_prefix.event', 'myFunction', 5 );
	}

	public function test_add_subscriber() {

		$subscriber = new MockSubscriber();

		\WP_Mock::expectFilterAdded( 'eventA', array( $subscriber, 'listenerA' ), 10, 3 );
		\WP_Mock::expectFilterAdded( 'eventB', array( $subscriber, 'listenerB' ), 10, 3 );
		\WP_Mock::expectFilterAdded( 'eventC', array( $subscriber, 'listenerC' ), 5, 3 );
		\WP_Mock::expectFilterAdded( 'eventD', array( $subscriber, 'listenerD' ), 15, 2 );

		$dispatcher = new EventDispatcher();
		$dispatcher->add_subscriber( $subscriber );
	}

	public function test_remove_subscriber() {

		$subscriber = new MockSubscriber();

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'eventA', array( $subscriber, 'listenerA' ), 10 )
		) );

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'eventB', array( $subscriber, 'listenerB' ), 10 )
		) );

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'eventC', array( $subscriber, 'listenerC' ), 5 )
		) );

		\WP_Mock::wpFunction( 'remove_filter', array(
			'args' => array( 'eventD', array( $subscriber, 'listenerD' ), 15 )
		) );


		$dispatcher = new EventDispatcher();
		$dispatcher->remove_subscriber( $subscriber );
	}

	public function test_has_listeners() {

		\WP_Mock::wpFunction( 'has_filter', array(
			'args' => array( 'event' )
		) );

		$dispatcher = new EventDispatcher();
		$dispatcher->has_listeners( 'event' );
	}

	public function test_get_listener_priority() {

		\WP_Mock::wpFunction( 'has_filter', array(
			'args'   => array( 'event', 'myFunction' ),
			'return' => 5
		) );

		$dispatcher = new EventDispatcher();
		$this->assertEquals( 5, $dispatcher->get_listener_priority( 'event', 'myFunction' ) );
	}

	public function test_get_listener_priority_returns_null_if_listener_not_listening_for_event() {

		\WP_Mock::wpFunction( 'has_filter', array(
			'args'   => array( 'event', 'myFunction' ),
			'return' => false
		) );

		$dispatcher = new EventDispatcher();
		$this->assertNull( $dispatcher->get_listener_priority( 'event', 'myFunction' ) );
	}

	public function test_current_event() {

		\WP_Mock::wpFunction( 'current_filter', array(
			'return' => 'event'
		) );

		$dispatcher = new EventDispatcher();
		$this->assertEquals( 'event', $dispatcher->current_event() );
	}

	public function test_doing_event() {

		\WP_Mock::wpFunction( 'doing_filter', array(
			'args'   => array( 'event' ),
			'return' => true
		) );

		$dispatcher = new EventDispatcher();
		$this->assertEquals( true, $dispatcher->doing_event( 'event' ) );
	}
}

class MockSubscriber implements EventSubscriber {

	/**
	 * Return a list of the events subscribed to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *      - The method name on this object.
	 *      - An array with the method name and priority.
	 *      - An array with the method name, priority, and accepted arguments number.
	 *
	 * For example:
	 *
	 *      - array( 'event.name' => 'method_name' )
	 *      - array( 'event.name' => array( 'method_name', 15 ) )
	 *      - array( 'event.name' => array( 'method_name', 15, 2 ) )
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'eventA' => 'listenerA',
			'eventB' => array( 'listenerB' ),
			'eventC' => array( 'listenerC', 5 ),
			'eventD' => array( 'listenerD', 15, 2 )
		);
	}
}