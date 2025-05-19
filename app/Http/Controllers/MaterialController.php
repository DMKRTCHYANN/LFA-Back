<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Enums\Medium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MatanYadaev\EloquentSpatial\Objects\Point;

class MaterialController extends Controller
{

    public function index()
    {
        $materials = Material::with(['language',  'topic', 'country', 'tags'])->get();

        return response()->json($materials, 200);
    }


    public function show($id)
    {
        $material = Material::with(['language', 'topic', 'country', 'tags'])->find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        return response()->json($material, 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'topic_id' => 'required|exists:topics,id',
            'country_id' => 'required|exists:countries,id',
            'poster' => 'nullable|string',
            'title' => 'required|array',
            'title.*' => 'required|string|max:255',
            'author' => 'required|array',
            'author.*' => 'required|string|max:255',
            'short_description' => 'required|array',
            'short_description.*' => 'required|string|max:255',
            'start_year' => 'required|integer',
            'end_year' => 'required|integer',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'medium' => [Rule::enum(Medium::class)],
            'full_text' => 'required|array',
            'full_text.*' => 'required|string|max:255',
            'book_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
            'video' => 'required|string',
            'source_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
            'source' => 'required|array',
            'source.*' => 'required|string|max:255',
            'author_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
        ]);

        $validated['location'] = new Point($validated['latitude'], $validated['longitude']);

        $material = Material::create($validated);

        if ($request->has('tags')) {
            $material->tags()->sync($validated['tags']);
        }

        return response()->json([
            'data' => $material->load(['language', 'topic', 'country', 'tags']),
            'message' => 'Material created successfully.',
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language_id' => 'sometimes|exists:languages,id',
            'topic_id' => 'sometimes|exists:topics,id',
            'country_id' => 'sometimes|exists:countries,id',
            'poster' => 'nullable|string',
            'title' => 'nullable|json',
            'author' => 'nullable|json',
            'short_description' => 'nullable|json',
            'start_year' => 'sometimes|integer',
            'end_year' => 'sometimes|integer',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'latitude' => 'sometimes|required_with:longitude|numeric',
            'longitude' => 'sometimes|required_with:latitude|numeric',
            'medium' => [Rule::enum(Medium::class)],
            'full_text' => 'nullable|json',
            'book_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
            'video' => 'sometimes|string',
            'source_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
            'source' => 'sometimes|json',
            'author_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $material->update($request->except(['tags', 'latitude', 'longitude']));

        if ($request->has('tags')) {
            $material->tags()->sync($request->input('tags', []));
        }

        if ($request->filled(['latitude', 'longitude'])) {
            $material->location = new Point($request->input('latitude'), $request->input('longitude'));
            $material->save();
        }

        return response()->json($material->load(['language', 'topic', 'country', 'tags']), 200);
    }

    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted'], 200);
    }
}
