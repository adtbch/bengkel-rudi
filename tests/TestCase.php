<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * The admin panel authenticates through the `admin_token` cookie, and the
     * direct-upload client sends AJAX with `credentials: same-origin`. Mirror
     * that here so JSON requests carry cookies like the browser does.
     *
     * @var bool
     */
    protected $withCredentials = true;
}
