<?php


namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::all();

        return response()->json([
            'error' => false,
            'data' => $languages
        ]);
    }

    public function show($id)
    {
        $language = Language::findOrFail($id);

        return response()->json([
            'error' => false,
            'data' => $language,
        ], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'code']);
        $data['image'] = $request->hasFile('image') ? $this->handleImageUpload($request) : null;

        $language = Language::create($data);

        return response()->json([
            'error' => false,
            'data' => $language,
        ], 201);
    }


    public function update(Request $request, $id)
    {
            $language = Language::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $data = $request->only(['name', 'code']);

            if ($request->hasFile('image')) {
                $data['image'] = $this->handleImageUpload($request, $language->image);
            }

            $language->update($data);

            return response()->json([
                'error' => false,
                'data' => $language
            ]);

    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);

        if ($language->image && file_exists(public_path($language->image))) {
            unlink(public_path($language->image));
        }

        $language->delete();

        return response()->json([
            'error' => false,
            'message' => 'Language deleted successfully'
        ]);
    }

    private function handleImageUpload(Request $request, $existingImage = null)
    {
        if ($request->hasFile('image')) {
            if ($existingImage) {
                \Storage::disk('public')->delete($existingImage);
            }
            return $request->file('image')->store('images', 'public');
        }

        return $existingImage;
    }
}
