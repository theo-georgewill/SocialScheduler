<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedFile;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validate input
        $request->validate([
            'text' => 'nullable|string|max:5000', // Optional text input
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,avi,mov|max:51200',
        ]);

        // Store text input (if provided)
        $uploadedFiles = [];
        if ($request->text) {
            $textRecord = UploadedFile::create([
                'file_name' => 'Text Post',
                'file_path' => $request->text, // Store the text instead of a file path
            ]);
            $uploadedFiles[] = $textRecord;
        }

        // Store uploaded files (if provided)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $fileName, 'public'); // Save file

                $fileRecord = UploadedFile::create([
                    'file_name' => $fileName,
                    'file_path' => "/storage/$path",
                ]);

                $uploadedFiles[] = $fileRecord;
            }
        }

        return response()->json([
            'message' => 'Post created successfully!',
            'files' => $uploadedFiles
        ]);
    }

    // Retrieve uploaded files
    public function index()
    {
        return response()->json(UploadedFile::all());
    }
}
