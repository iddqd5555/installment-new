<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;

class InstallmentController extends Controller
{
    public function index()
    {
        $requests = InstallmentRequest::where('user_id', Auth::id())->get();
        return view('user.installments.index', compact('requests'));
    }

    public function create()
    {
        return view('user.installments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'price' => 'required|numeric',
            'installment_months' => 'required|integer',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = time().'.'.$request->product_image->extension();  
        $request->product_image->storeAs('public/products', $imageName);

        InstallmentRequest::create([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'installment_months' => $request->installment_months,
            'product_image' => $imageName,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('user.installments.index')->with('success', 'ส่งคำขอผ่อนสำเร็จ!');
    }

    public function show($id)
    {
        $request = InstallmentRequest::where('user_id', Auth::id())->findOrFail($id);
        return view('user.installments.show', compact('request'));
    }

    public function edit($id) {
        $installment = InstallmentRequest::findOrFail($id);
        $goldPrices = Cache::get('gold_prices_daily');
        return view('admin.installments.edit', compact('installment', 'goldPrices'));
    }

    public function update(Request $request, $id)
    {
        $installment = InstallmentRequest::findOrFail($id);

        $request->validate([
            'product_name' => 'required|string',
            'price' => 'required|numeric',
            'installment_months' => 'required|integer',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'product_name' => $request->product_name,
            'price' => $request->price,
            'installment_months' => $request->installment_months,
        ];

        if ($request->hasFile('product_image')) {
            $imageName = time().'.'.$request->product_image->extension();
            $request->product_image->storeAs('public/products', $imageName);
            $data['product_image'] = $imageName;
        }

        $installment->update($data);

        return redirect()->route('user.installments.index')->with('success', 'แก้ไขข้อมูลสำเร็จ!');
    }

    public function destroy($id)
    {
        $installment = InstallmentRequest::findOrFail($id);

        // เช็คเจ้าของคำขอ
        if (auth()->id() !== $installment->user_id) {
            abort(403);
        }

        $installment->delete();

        return redirect()->route('user.installments.index')->with('success', 'ลบคำขอผ่อนสินค้าสำเร็จ!');
    }

}
