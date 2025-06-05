<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $languageCodes = Language::pluck('code')->toArray();

        $rules = [
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ];

        foreach ($languageCodes as $code) {
            $rules["name.$code"] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($languageCodes, $request) {
            $hasValidLanguage = false;
            foreach ($languageCodes as $code) {
                if (
                    $request->input("name.$code")
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


        $topic = Tag::create($validated);


        return response()->json([
            'error' => false,
            'message' => 'Tag created successfully!',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $names = $request->input('name', []);

            $hasValidName = collect($names)->filter(function ($name) {
                return !empty($name);
            })->isNotEmpty();

            if (!$hasValidName) {
                $validator->errors()->add(
                    'name',
                    'At least one language must have the "name" field filled.'
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag = Tag::findOrFail($id);
        $tag->update(['name' => $request->input('name')]);

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
