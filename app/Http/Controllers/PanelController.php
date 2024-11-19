<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PanelController extends Controller
{
    public function index()
    {
        if (User::esRol('ESTUDIANTE')) {
            return redirect('inscripcion');
        }

        return Inertia::render('Dashboard');
    }
}
