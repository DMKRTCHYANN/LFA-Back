<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Material;
use App\Enums\Medium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

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
            return response()->json(['message' => 'Material not found'], 404);
        }

        return response()->json($material, 200);
    }


    public function store(Request $request)
    {
        $languageCodes = Language::pluck('code')->toArray();

        $rules = [
            'language_id' => 'required|exists:languages,id',
            'topic_id' => 'required|exists:topics,id',
            'country_id' => 'required|exists:countries,id',
            'poster' => 'nullable|string|max:255',
            'title' => 'required|array',
            'author' => 'required|array',
            'short_description' => 'required|array',
            'full_text' => 'required|array',
            'source' => 'required|array',
            'start_year' => 'required|numeric',
            'end_year' => 'required|numeric',
            'medium' => [Rule::enum(Medium::class)],
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'book_url' => 'required|url',
            'video' => 'required|url',
            'image' => 'required',
            'source_url' => 'required|url',
            'author_url' => 'required|url',
            'location' => 'required|array',
            'location.type' => 'required|string|in:Point',
            'location.coordinates' => 'required|array|size:2',
            'location.coordinates.0' => 'required|numeric|between:-90,90',
            'location.coordinates.1' => 'required|numeric|between:-180,180',
            'author_location' => 'required|array',
            'author_location.type' => 'required|string|in:Point',
            'author_location.coordinates' => 'required|array|size:2',
            'author_location.coordinates.0' => 'required|numeric|between:-90,90',
            'author_location.coordinates.1' => 'required|numeric|between:-180,180',
        ];

        foreach ($languageCodes as $code) {
            $rules["title.$code"] = 'nullable|string|max:255';
            $rules["author.$code"] = 'nullable|string|max:255';
            $rules["short_description.$code"] = 'nullable|string|max:255';
            $rules["full_text.$code"] = 'nullable|string|max:255';
            $rules["source.$code"] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($languageCodes, $request) {
            $hasValidLanguage = false;
            foreach ($languageCodes as $code) {
                if (
                    $request->input("title.$code") &&
                    $request->input("author.$code") &&
                    $request->input("short_description.$code") &&
                    $request->input("full_text.$code") &&
                    $request->input("source.$code")
                ) {
                    $hasValidLanguage = true;
                    break;
                }
            }
            if (!$hasValidLanguage) {
                $validator->errors()->add(
                    'language_fields',
                    'At least one language must have all fields (title, author, short description, full text, source) filled.'
                );
            }
        });

        $validated = $validator->validate();

        $tags = $validated['tags'];

        $validated['location'] = new Point(
            $validated['location']['coordinates'][0],
            $validated['location']['coordinates'][1]
        );

        $validated['author_location'] = new Point(
            $validated['author_location']['coordinates'][0],
            $validated['author_location']['coordinates'][1],
        );

        $validated['image'] = $request->hasFile('image') ? $this->handleImageUpload($request) : null;


        $material = Material::create($validated);

        if (!empty($tags)) {
            $material->tags()->sync($tags);
        }

        return response()->json([
            'data' => $material->load(['language', 'topic', 'country', 'tags']),
            'message' => 'Material created successfully.',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);


        $languageCodes = Language::pluck('code')->toArray();

        $rules = [
            'language_id' => 'required|exists:languages,id',
            'topic_id' => 'required|exists:topics,id',
            'country_id' => 'required|exists:countries,id',
            'poster' => 'nullable|string',
            'title' => 'required|array',
            'author' => 'required|array',
            'short_description' => 'required|array',
            'full_text' => 'required|array',
            'source' => 'required|array',
            'start_year' => 'required|numeric',
            'end_year' => 'required|numeric',
            'medium' => ['required', Rule::enum(Medium::class)],
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'book_url' => 'required|url',
            'video' => 'required|url',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'source_url' => 'required|url',
            'author_url' => 'required|url',
            'location' => 'required|array',
            'location.type' => 'required|string|in:Point',
            'location.coordinates' => 'required|array|size:2',
            'location.coordinates.0' => 'required|numeric|between:-180,180',
            'location.coordinates.1' => 'required|numeric|between:-90,90',
            'author_location' => 'required|array',
            'author_location.type' => 'required|string|in:Point',
            'author_location.coordinates' => 'required|array|size:2',
            'author_location.coordinates.0' => 'required|numeric|between:-90,90',
            'author_location.coordinates.1' => 'required|numeric|between:-180,180',
        ];

        foreach ($languageCodes as $code) {
            $rules["title.$code"] = 'nullable|string|max:255';
            $rules["author.$code"] = 'nullable|string|max:255';
            $rules["short_description.$code"] = 'nullable|string|max:255';
            $rules["full_text.$code"] = 'nullable|string|max:255';
            $rules["source.$code"] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules, [
            'title.*' => 'The title for :attribute is required.',
            'author.*' => 'The author for :attribute is required.',
            'short_description.*' => 'The short description for :attribute is required.',
            'full_text.*' => 'The full text for :attribute is required.',
            'source.*' => 'The source for :attribute is required.',
        ]);

        $validator->after(function ($validator) use ($languageCodes, $request) {
            $hasValidLanguage = false;
            foreach ($languageCodes as $code) {
                if (
                    $request->input("title.$code") &&
                    $request->input("author.$code") &&
                    $request->input("short_description.$code") &&
                    $request->input("full_text.$code") &&
                    $request->input("source.$code")
                ) {
                    $hasValidLanguage = true;
                    break;
                }
            }
            if (!$hasValidLanguage) {
                $validator->errors()->add(
                    'language_fields',
                    'At least one language must have all fields (title, author, short description, full text, source) filled.'
                );
            }
        });

        $validated = $validator->validate();

        $validated['location'] = new Point(
            $validated['location']['coordinates'][1],
            $validated['location']['coordinates'][0]
        );

        $validated['author_location'] = new Point(
            $validated['author_location']['coordinates'][1],
            $validated['author_location']['coordinates'][0],
        );

        if ($request->hasFile('image')) {
            $validated['image'] = $this->handleImageUpload($request, $material->image);
        }

        $material->update($validated);

        if ($request->has('tags')) {
            $material->tags()->sync($validated['tags']);
        } else {
            $material->tags()->sync([]);
        }

        return response()->json([
            'data' => $material->load(['language', 'topic', 'country', 'tags']),
            'message' => 'Material updated successfully.',
        ], 200);
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


    private function handleImageUpload(Request $request, $existingImage = null)
    {
        if ($request->hasFile('image')) {
            if ($existingImage) {
                Storage::disk('public')->delete($existingImage);
            }
            return $request->file('image')->store('images', 'public');
        }
        return $existingImage;
    }

}
