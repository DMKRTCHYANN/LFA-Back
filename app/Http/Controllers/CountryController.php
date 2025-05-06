<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::all()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->getTranslations('name'),
                'created_at' => $country->created_at,
                'updated_at' => $country->updated_at,
            ];
        });

        return response()->json([
            'error' => false,
            'data' => $countries
        ]);
    }

    public function show($id)
    {
        $country = Country::findOrFail($id);
        $countryData = [
            'id' => $country->id,
            'name' => $country->getTranslations('name'),
            'created_at' => $country->created_at,
            'updated_at' => $country->updated_at,
        ];

        return response()->json([
            'error' => false,
            'data' => $countryData
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
        ]);

        $country = Country::create($validated);

        return response()->json([
            'message' => 'Country created successfully!',
            'data' => [
                'id' => $country->id,
                'name' => $country->getTranslations('name'),
                'created_at' => $country->created_at,
                'updated_at' => $country->updated_at,
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
        ]);

        $country = Country::findOrFail($id);
        $country->update($validated);

        return response()->json([
            'message' => 'Country updated successfully!',
            'data' => [
                'id' => $country->id,
                'name' => $country->getTranslations('name'),
                'created_at' => $country->created_at,
                'updated_at' => $country->updated_at,
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return response()->json([
            'message' => 'Country deleted successfully',
            'data' => [
                'id' => $country->id,
                'name' => $country->getTranslations('name'),
                'created_at' => $country->created_at,
                'updated_at' => $country->updated_at,
            ]
        ]);
    }
}
