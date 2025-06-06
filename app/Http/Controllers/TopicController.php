<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                if ($request->input("name.$code")) {
                    $hasValidLanguage = true;
                    break;
                }
            }
            if (!$hasValidLanguage) {
                $validator->errors()->add(
                    'language_fields',
                    'At least one language must have a valid name field.'
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validate();


        $topic = Topic::create($validated);


        return response()->json([
            'error' => false,
            'message' => 'Topic created successfully!',
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

        $topic = Topic::findOrFail($id);


        $topic->update([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Topic updated successfully!'
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
