<?php

use Database\Factories\ChirpFactory;

test('definition in factory should return an array with message and user_id', function () {
    $result = (new ChirpFactory)->definition();
    
    expect($result)->toBeArray();
    expect($result)->toHaveKeys(['user_id', 'message']);
});
