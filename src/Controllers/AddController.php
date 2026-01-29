<?php

namespace App\Controllers;

class AddController
{
    /**
     * Display the unified add music page (YouTube search + Spotify CSV import)
     */
    public function index(): void
    {
        view('layouts/main', [
            'title' => 'Add Music',
            'content' => 'pages/add',
            'data' => []
        ]);
    }
}
