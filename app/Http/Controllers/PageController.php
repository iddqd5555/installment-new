<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home(){
        return view('pages.home');
    }

    public function gold(){
        return view('pages.gold');
    }

    public function phone(){
        return view('pages.phone');
    }

    public function contact(){
        return view('pages.contact');
    }
}
