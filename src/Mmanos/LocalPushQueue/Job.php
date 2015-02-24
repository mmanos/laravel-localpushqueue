<?php namespace Mmanos\LocalPushQueue;

use Illuminate\Queue\Jobs\Job as AbstractJob;
use Illuminate\Container\Container;
use Exception;

class Job extends AbstractJob {

	/**
	 * The Local config array.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The Local job array data.
	 *
	 * @var array
	 */
	protected $job;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Illuminate\Container\Container  $container
	 * @param  array  $config
	 * @param  array   $job
	 * @return void
	 */
	public function __construct(Container $container,
                                array $config,
                                array $job)
	{
		$this->config = $config;
		$this->job = $job;
		$this->container = $container;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$body = json_decode($this->getRawBody(), true);
		$this->resolveAndFire($body);
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->job['Body'];
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		//
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return 1;
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
		return $this->job['MessageId'];
	}

}
