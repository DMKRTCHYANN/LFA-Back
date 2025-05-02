<?php

namespace App\Http\Controllers;

use App\Enums\Medium;
use App\Models\Material;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with(['language', 'topic', 'country', 'tags'])->get();
        return response()->json($materials, 200);
    }
    public function show($id)
    {
        $material = Material::with(['language', 'topic', 'country', 'tags'])->find($id);

        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        return response()->json($material, 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|exists:languages,id',
            'topic_id' => 'required|exists:topics,id',
            'country_id' => 'required|exists:countries,id',
            'poster' => 'nullable|string',
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ru' => 'required|string',
            'author' => 'nullable|array',
            'author.en' => 'nullable|string',
            'author.ru' => 'nullable|string',
            'short_description' => 'nullable|array',
            'short_description.en' => 'nullable|string',
            'short_description.ru' => 'nullable|string',
            'start_year' => 'required|integer',
            'end_year' => 'required|integer',
            'medium' => 'required|in:' . implode(',', array_column(Medium::cases(), 'value')),
            'full_text' => 'nullable|array',
            'full_text.en' => 'nullable|string',
            'full_text.ru' => 'nullable|string',
            'book_url' => 'required|url',
            'video_player' => 'required|string',
            'source_url' => 'required|url',
            'source' => 'required|array',
            'source.en' => 'required|string',
            'source.ru' => 'required|string',
            'author_url' => 'required|url',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only([
                'language_id',
                'topic_id',
                'country_id',
                'poster',
                'title',
                'author',
                'short_description',
                'start_year',
                'end_year',
                'medium',
                'full_text',
                'book_url',
                'video_player',
                'source_url',
                'source',
                'author_url',
            ]);

            if ($request->has('location')) {
                $data['location'] = DB::raw("ST_GeomFromText('{$request->location}')");
            }

            $material = Material::create($data);

            if ($request->has('tag_ids')) {
                $material->tags()->sync($request->input('tag_ids'));
            }

            DB::commit();

            return response()->json($material->load(['language', 'topic', 'country', 'tags']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create material: ' . $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language_id' => 'required|exists:languages,id',
            'topic_id' => 'required|exists:topics,id',
            'country_id' => 'required|exists:countries,id',
            'poster' => 'nullable|string',
            'title' => 'required|array',
            'author' => 'nullable|array',
            'short_description' => 'nullable|array',
            'start_year' => 'required|integer',
            'end_year' => 'required|integer',
            'medium' => 'required|in:' . implode(',', array_column(\App\Enums\Medium::cases(), 'value')),
            'full_text' => 'nullable|array',
            'book_url' => 'required|url',
            'video_player' => 'required|string',
            'source_url' => 'required|url',
            'source' => 'required|array',
            'author_url' => 'required|url',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $material->update($request->only([
                'language_id',
                'topic_id',
                'country_id',
                'poster',
                'title',
                'author',
                'short_description',
                'start_year',
                'end_year',
                'medium',
                'full_text',
                'book_url',
                'video_player',
                'source_url',
                'source',
                'author_url',
            ]));

            if ($request->has('tag_ids')) {
                $material->tags()->sync($request->input('tag_ids'));
            } else {
                $material->tags()->detach();
            }

            DB::commit();

            return response()->json($material->load(['language', 'topic', 'country', 'tags']), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update material'], 500);
        }
    }


    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        try {
            $material->delete();
            return response()->json(['message' => 'Material deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete material'], 500);
        }
    }


}
