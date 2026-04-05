<?php

test('the application root is not found', function () {
    $response = $this->get('/');

    $response->assertNotFound();
});
