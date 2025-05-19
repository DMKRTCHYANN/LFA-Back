<?php

namespace App\Http\Controllers;

use App\Enums\Medium;
use Illuminate\Http\Request;

class MediumController extends Controller
{
    public function getMediums()
    {
        $mediums = [];
        foreach (Medium::cases() as $medium) {
            $mediums[] = [
                'value' => $medium->value,
                'label' => ucfirst($medium->value),
            ];
        }

        return response()->json(['data' => $mediums], 200);
    }
}
