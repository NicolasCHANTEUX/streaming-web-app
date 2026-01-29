<?php

namespace App\Controllers;

use App\Core\Controller;

class AddController extends Controller
{
    /**
     * Display the unified add music page (YouTube search + Spotify CSV import)
     */
    public function index()
    {
        $this->view('pages/add', [
            'title' => 'Add Music'
        ]);
    }
}
