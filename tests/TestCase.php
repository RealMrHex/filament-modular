<?php

namespace RealMrHex\FilamentModular\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RealMrHex\FilamentModular\FilamentModularServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	use RefreshDatabase;

	public function setUp(): void
	{
		parent::setUp();
	}

	protected function getPackageProviders($app)
	{
		return [
			FilamentModularServiceProvider::class,
		];
	}
}