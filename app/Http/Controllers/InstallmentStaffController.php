<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstallmentStaffController extends Controller
{
    public function index()
    {
        return view('staff.installments.index');
    }

    public function create()
    {
        return view('staff.installments.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('staff.installments.show');
    }

    public function edit($id)
    {
        return view('staff.installments.edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
