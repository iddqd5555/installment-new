<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{
    public function upload(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'document' => 'required|file|max:5120', // 5 MB
        ]);
        $file = $request->file('document');
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('user-documents/'.$user->id, $filename, 'public');

        $doc = UserDocument::create([
            'user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'file_url' => Storage::url($path),
        ]);

        return response()->json(['success' => true, 'document' => $doc]);
    }
}
