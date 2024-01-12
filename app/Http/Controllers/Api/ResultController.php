<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResultController extends Controller
{
    public function saveResult(Request $request)
    {
        try {
            $request->validate([
                'milliseconds' => 'required|integer',
            ]);

            $member = null;
            if ($request->has('email')) {
                $member = Member::firstOrCreate(['email' => $request->input('email')]);
            }

            Result::create([
                'member_id' => $member ? $member->id : null,
                'milliseconds' => $request->input('milliseconds'),
            ]);

            // Ответ
            return response()->json(['message' => 'Результат успешно сохранен']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при сохранении результата'], 500);
        }
    }
}
