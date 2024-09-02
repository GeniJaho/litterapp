<?php

namespace Tests;

use App\Actions\Photos\ExtractsExifFromPhoto;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Doubles\FakeExtractExifFromPhotoAction;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->swap(ExtractsExifFromPhoto::class, new FakeExtractExifFromPhotoAction);
    }
}
