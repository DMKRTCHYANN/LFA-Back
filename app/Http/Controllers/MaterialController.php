<?php


namespace App\Http\Controllers;

use App\Models\Material;
use App\Enums\Medium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{

    private function ensureUtf8($value)
    {
        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        } elseif (is_array($value)) {
            return array_map([$this, 'ensureUtf8'], $value);
        }
        return $value;
    }


    public function index()
    {
        try {
            $materials = Material::with(['language', 'topic', 'country', 'tags'])->get()->map(function ($material) {
                // Ensure all attributes are UTF-8 compliant
                $attributes = $material->getAttributes();
                foreach ($attributes as $key => $value) {
                    $material->$key = $this->ensureUtf8($value);
                }
                // Handle related models' attributes
                foreach (['language', 'topic', 'country', 'tags'] as $relation) {
                    if ($material->$relation) {
                        if ($relation === 'tags') {
                            $material->$relation->transform(function ($tag) {
                                $tag->setRawAttributes(array_map([$this, 'ensureUtf8'], $tag->getAttributes()));
                                return $tag;
                            });
                        } else {
                            $material->$relation->setRawAttributes(
                                array_map([$this, 'ensureUtf8'], $material->$relation->getAttributes())
                            );
                        }
                    }
                }
                return $material;
            });

            return response()->json($materials, 200);
        } catch (\Exception $e) {
            \Log::error('JSON encoding error in index: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid data encoding'], 500);
        }
    }


    public function show($id)
    {
        $material = Material::with(['language', 'topic', 'country', 'tags'])->find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        $material->setRawAttributes(array_map([$this, 'ensureUtf8'], $material->getAttributes()));
        foreach (['language', 'topic', 'country', 'tags'] as $relation) {
            if ($material->$relation) {
                if ($relation === 'tags') {
                    $material->$relation->transform(function ($tag) {
                        $tag->setRawAttributes(array_map([$this, 'ensureUtf8'], $tag->getAttributes()));
                        return $tag;
                    });
                } else {
                    $material->$relation->setRawAttributes(
                        array_map([$this, 'ensureUtf8'], $material->$relation->getAttributes())
                    );
                }
            }
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
            'location' => 'nullable|string',
            'medium' => [Rule::enum(Medium::class)],
            'full_text' => 'required|array',
            'full_text.*' => 'required|string|max:255',
            'book_url' => 'required|url',
            'video_player' => 'required|string',
            'source_url' => 'required|url',
            'source' => 'required|array',
            'source.*' => 'required|string|max:255',
            'author_url' => 'required|url',
        ]);

        $validated = array_map([$this, 'ensureUtf8'], $validated);

        $material = Material::create($validated);

        if ($request->has('tags')) {
            $material->tags()->sync($request->input('tags', []));
        }

        return response()->json($material->load(['language', 'topic', 'country', 'tags']), 201);
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
            'location' => 'nullable|string',
            'medium' => 'sometimes|in:' . implode(',', array_column(Medium::cases(), 'value')),
            'full_text' => 'nullable|json',
            'book_url' => 'sometimes|url',
            'video_player' => 'sometimes|string',
            'source_url' => 'sometimes|url',
            'source' => 'sometimes|json',
            'author_url' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure UTF-8 compliance for input data
        $validated = array_map([$this, 'ensureUtf8'], $request->except('tags'));

        $material->update($validated);

        if ($request->has('tags')) {
            $material->tags()->sync($request->input('tags', []));
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
//
//namespace App\Http\Controllers;
//
//use App\Models\Material;
//use App\Enums\Medium;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Validator;
//use Illuminate\Validation\Rule;
//
//class MaterialController extends Controller
//{
//
//    public function index()
//    {
//        $materials = Material::with(['language', 'topic', 'country', 'tags'])->get();
//        return response()->json($materials, 200);
//    }
//
//
//    public function show($id)
//    {
//        $material = Material::with(['language', 'topic', 'country', 'tags'])->find($id);
//
//        if (!$material) {
//            return response()->json(['message' => 'Material not found'], 404);
//        }
//
//        return response()->json($material, 200);
//    }
//
//
//    public function store(Request $request)
//    {
//        $validated = $request->validate([
//            'language_id' => 'required|exists:languages,id',
//            'topic_id' => 'required|exists:topics,id',
//            'country_id' => 'required|exists:countries,id',
//            'poster' => 'nullable|string',
//            'title' => 'required|array',
//            'title.*' => 'required|string|max:255',
//            'author' => 'required|array',
//            'author.*' => 'required|string|max:255',
//            'short_description' => 'required|array',
//            'short_description.*' => 'required|string|max:255',
//            'start_year' => 'required|integer',
//            'end_year' => 'required|integer',
//            'tags' => 'nullable|array',
//            'tags.*' => 'exists:tags,id',
//            'location' => 'nullable|string',
//            'medium' => [Rule::enum(Medium::class)],
//            'full_text' => 'required|array',
//            'full_text.*' => 'required|string|max:255',
//            'book_url' => 'required|url',
//            'video_player' => 'required|string',
//            'source_url' => 'required|url',
//            'source' => 'required|array',
//            'source.*' => 'required|string|max:255',
//            'author_url' => 'required|url',
//        ]);
//
//
//        $material = Material::create($request->except('tags'));
//
//        if ($request->has('tags')) {
//            $material->tags()->sync($request->input('tags', []));
//        }
//
//        return response()->json($material->load(['language', 'topic', 'country', 'tags']), 201);
//    }
//
//    public function update(Request $request, $id)
//    {
//        $material = Material::find($id);
//
//        if (!$material) {
//            return response()->json(['message' => 'Material not found'], 404);
//        }
//
//        $validator = Validator::make($request->all(), [
//            'language_id' => 'sometimes|exists:languages,id',
//            'topic_id' => 'sometimes|exists:topics,id',
//            'country_id' => 'sometimes|exists:countries,id',
//            'poster' => 'nullable|string',
//            'title' => 'nullable|json',
//            'author' => 'nullable|json',
//            'short_description' => 'nullable|json',
//            'start_year' => 'sometimes|integer',
//            'end_year' => 'sometimes|integer',
//            'tags' => 'nullable|array',
//            'tags.*' => 'exists:tags,id',
//            'location' => 'nullable|string',
//            'medium' => 'sometimes|in:' . implode(',', array_column(Medium::cases(), 'value')),
//            'full_text' => 'nullable|json',
//            'book_url' => 'sometimes|url',
//            'video_player' => 'sometimes|string',
//            'source_url' => 'sometimes|url',
//            'source' => 'sometimes|json',
//            'author_url' => 'sometimes|url',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()], 422);
//        }
//
//        $material->update($request->except('tags'));
//
//        if ($request->has('tags')) {
//            $material->tags()->sync($request->input('tags', []));
//        }
//
//        return response()->json($material->load(['language', 'topic', 'country', 'tags']), 200);
//    }
//
//    public function destroy($id)
//    {
//        $material = Material::find($id);
//
//        if (!$material) {
//            return response()->json(['message' => 'Material not found'], 404);
//        }
//
//        $material->delete();
//
//        return response()->json(['message' => 'Material deleted'], 200);
//    }
//}
