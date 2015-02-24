<?php namespace Mmanos\LocalPushQueue;

use Illuminate\Http\Request;
use Illuminate\Queue\Connectors\ConnectorInterface;

class Connector implements ConnectorInterface {

	/**
	 * The current request instance.
	 *
	 * @var \Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * Create a new Iron connector instance.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Illuminate\Queue\QueueInterface
	 */
	public function connect(array $config)
	{
		return new Queue($config, $this->request);
	}

}
