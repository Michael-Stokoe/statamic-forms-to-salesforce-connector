<?php

namespace Stokoe\FormsToSalesforceConnector\Tests;

use Stokoe\FormsToSalesforceConnector\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
