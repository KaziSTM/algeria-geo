<?php

use Illuminate\Support\Facades\Schema;
use KaziSTM\AlgeriaGeo\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

beforeEach(function () {
    Schema::dropAllTables();
});