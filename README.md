# Local push queue driver for Laravel 4

This package provides a local laravel queue driver that will open a non-blocking connection to itself, thus offloading the actuall processing of a job.

Limitations

* Retying failed attempts does not work
* Delayed jobs are not delayed and execute immediately

## Installation Via Composer

Add this to you composer.json file, in the require object:

```javascript
"mmanos/laravel-localpushqueue": "dev-master"
```

After that, run composer install to install the package.

Add the service provider to `app/config/app.php`, within the `providers` array.

```php
'providers' => array(
	// ...
	'Mmanos\LocalPushQueue\LocalPushQueueServiceProvider',
)
```

## Configuration

Update the existing `queue.php` config file and add a new `local` array to the existing `connections` array:

```php
'connections' => array(
	//...
	'local' => array(
		'driver' => 'localpush',
		'method' => 'POST',
		'url'    => url('queue/receive'),
	),
),
```

Then update the `default` queue driver to be `local`.

Next, ensure you have a route defined to listen for your pushed jobs:

```php
Route::post('queue/receive', function() { return Queue::marshal(); });
```

Finally, since this driver makes a request to the URL used by your application, make sure your server can resolve the hostname defined in the `url` config value. On a dev server you may need to ensure your local hostname is in your machine's `hosts` file.
