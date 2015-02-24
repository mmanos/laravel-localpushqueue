<?php namespace Mmanos\LocalPushQueue;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;

class LocalPushQueueServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('mmanos/laravel-localpushqueue');
		
		$app = $this->app;
		
		$app['queue']->extend('localpush', function () use ($app) {
			return new Connector($app['request']);
		});
		
		$app->rebinding('request', function ($app, $request) {
			if ($app['queue']->connected('localpush')) {
				$app['queue']->connection('localpush')->setRequest($request);
			}
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
