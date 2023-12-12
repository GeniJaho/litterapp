<?php

arch('models')
    ->expect('App\Models')
    ->toNotEagerLoadByDefault();
