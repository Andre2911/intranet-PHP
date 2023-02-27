<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function qrCode(Request $request){

        $image = \QrCode::format('png')
                         ->merge('public/image/logo.png', 0.2, true)
                         ->size(500)->errorCorrection('H')
                         ->generate('A http://csjpuno.pe/consultacert/');
                         
        return response($image)->header('Content-type','image/png');
    }
}
