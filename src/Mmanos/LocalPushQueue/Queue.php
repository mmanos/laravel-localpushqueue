<?php namespace Mmanos\LocalPushQueue;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\Queue as AbstractQueue;
use Illuminate\Queue\QueueInterface;
use Exception;

class Queue extends AbstractQueue implements QueueInterface {

	/**
	 * The config array.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The current request instance.
	 *
	 * @var \Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * Create a new Local queue instance.
	 *
	 * @param  array  $config
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct(array $config, Request $request)
	{
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return mixed
	 */
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}

	/**
	 * Push a raw payload onto the queue.
	 *
	 * @param  string  $payload
	 * @param  string  $queue
	 * @param  array   $options
	 * @return mixed
	 */
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		$host = array_get($this->config, 'url');
		$host = $path = str_replace(array('https://', 'http://'), array('ssl://', ''), $host);
		$host = explode('/', $host);
		$host = $host[0];

		$port = (false !== strstr($host, 'ssl://')) ? 443 : 80;

		$path = str_replace($host, '', $path);
		$path = empty($path) ? '/' : $path;
		$path .= (false === strstr($path, '?')) ? '?' : '&';
		$path .= 'msgid=' . microtime(true);

		$body = array_get($this->config, 'method', 'GET') . " $path HTTP/1.0\n";
		$body .= "Host: $host\n";
		$body .= "Content-Type: application/x-www-form-urlencoded\n";
		$body .= "Content-Length: " .strlen($payload) ."\n\n";
		$body .= $payload; 

		$socket = pfsockopen($host, $port, $errno, $errstr, 5);
		fwrite($socket, $body);
		fclose($socket);

		return 0;
	}

	/**
	 * Push a new job onto the queue after a delay.
	 *
	 * @param  \DateTime|int  $delay
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return mixed
	 */
	public function later($delay, $job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}

	/**
	 * Pop the next job off of the queue.
	 *
	 * @param  string  $queue
	 * @return \Illuminate\Queue\Jobs\Job|null
	 */
	public function pop($queue = null) {}

	/**
	 * Marshal a push queue request and fire the job.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function marshal()
	{
		try {
			$this->createPushedJob($this->marshalPushedJob())->fire();
		} catch (Exception $e) {
			if ('Release' == $e->getMessage()) {
				// Return a non-200 message to release back onto the queue.
				return new Response('Release job', 412);
			}

			throw $e;
		}

		return new Response('OK');
	}

	/**
	 * Marshal out the pushed job and payload.
	 *
	 * @return object
	 */
	protected function marshalPushedJob()
	{
		$r = $this->request;

		return array(
			'MessageId' => $r->input('msgid'),
			'Body'      => $r->getContent(),
		);
	}

	/**
	 * Create a new LocalPushJob for a pushed job.
	 *
	 * @param  object  $job
	 * @return \Illuminate\Queue\Jobs\Job
	 */
	protected function createPushedJob($job)
	{
		return new Job($this->container, $this->config, $job);
	}

	/**
	 * Get the request instance.
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Set the request instance.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return void
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

}
