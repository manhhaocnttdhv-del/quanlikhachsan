<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredRooms = Room::available()->with('images')->take(6)->get();
        return view('user.home', compact('featuredRooms'));
    }

    public function about()
    {
        return view('user.about');
    }

    public function agodaClone()
    {
        return view('user.agoda-clone');
    }
}
