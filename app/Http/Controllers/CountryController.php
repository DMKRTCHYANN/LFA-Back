<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use function Laravel\Prompts\error;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::all();

        return response()->json([
            'error' => false,
            'data' => $countries
        ]);
    }

    public function show($id)
    {
        $country = Country::findOrFail($id);

        return response()->json([
            'error' => false,
            'data' => $country
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $country = Country::create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Country created successfully!',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $country = Country::findOrFail($id);
        $country->update($validated);

        return response()->json([
            'error' => false,
            'message' => 'Country updated successfully!',
        ], 200);
    }

    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return response()->json([
            'error' => false,
            'message' => 'Country deleted successfully',
        ]);
    }
}
