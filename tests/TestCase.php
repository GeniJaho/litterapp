<?php

namespace Tests;

use App\Actions\Photos\ExtractsLocationFromPhoto;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Doubles\FakeExtractLocationFromPhotoAction;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->swap(ExtractsLocationFromPhoto::class, new FakeExtractLocationFromPhotoAction());
    }
}
