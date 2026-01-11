<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    // Filament redirects to admin panel, so expect redirect
    $response->assertRedirect();
});
