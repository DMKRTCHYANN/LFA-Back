<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tag = Tag::all();

        return response()->json([
            'error' => false,
            'data' => $tag
        ]);
    }

    public function show($id)
    {
        $tag = Tag::findOrFail($id);

        return response()->json([
            'error' => false,
            'data' => $tag
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Tag created successfully!',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $tag = Tag::findOrFail($id);
        $tag->update($validated);

        return response()->json([
            'error' => false,
            'message' => 'Tag updated successfully!',
        ], 200);
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'error' => false,
            'message' => 'Tag deleted successfully',
        ]);
    }
}
