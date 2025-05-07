<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        $topic = Topic::all();

        return response()->json([
            'error' => false,
            'data' => $topic
        ]);
    }

    public function show($id)
    {
        $topic = Topic::findOrFail($id);

        return response()->json([
            'error' => false,
            'data' => $topic
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $topic = Topic::create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Topic created successfully!',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $topic = Topic::findOrFail($id);
        $topic->update($validated);

        return response()->json([
            'error' => false,
            'message' => 'Topic updated successfully!',
        ], 200);
    }

    public function destroy($id)
    {
        $topic = Topic::findOrFail($id);
        $topic->delete();

        return response()->json([
            'error' => false,
            'message' => 'Topic deleted successfully',
        ]);
    }
}
