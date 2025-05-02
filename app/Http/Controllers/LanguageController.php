<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return response()->json($languages);
    }


    public function show($id)
    {
        $language = Language::findOrFail($id);
        return response()->json($language);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'image' => 'required|image|max:2048', // Ограничение: только изображения, максимум 2MB
        ]);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName); // Сохраняем файл в папку public/images
            $request->merge(['image' => 'images/' . $imageName]); // Обновляем данные с путем
        }

        $language = Language::create($request->all());
        return response()->json(['message' => 'Язык успешно создан!', 'language' => $language], 201);
    }
    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10',
            'image' => 'sometimes|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Удаляем старое изображение, если оно существует
            if ($language->image && file_exists(public_path($language->image))) {
                unlink(public_path($language->image));
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $request->merge(['image' => 'images/' . $imageName]);
        }

        $language->update($request->all());
        return response()->json(['message' => 'Язык успешно обновлен!', 'language' => $language]);
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);
        $language->delete();
        return response()->json(['message' => 'Language deleted successfully!']);
    }
}
