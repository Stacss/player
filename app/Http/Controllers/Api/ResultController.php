<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Result;
use App\Services\RankService;
use App\Services\TopResultsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ResultController extends Controller
{
    /**
     * @var \App\Services\TopResultsService
     */
    protected $topResultsService;

    /**
     * Конструктор контроллера.
     *
     * @param \App\Services\TopResultsService $topResultsService Сервис для работы с топ-результатами.
     */
    public function __construct(TopResultsService $topResultsService)
    {
        $this->topResultsService = $topResultsService;
    }

    /**
     * @OA\Post(
     *     path="/api/save-result",
     *     tags={"Results"},
     *     summary="Сохранение результата для участника",
     *     description="Сохраняет результат для участника игры.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="example@email.com", description="Email участника"),
     *             @OA\Property(property="milliseconds", type="integer", example=5000, description="Время за которое пользователь прошел игру")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Результат успешно сохранен",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Результат успешно сохранен")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object", description="Сообщения об ошибках валидации")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Произошла ошибка при сохранении результата",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Произошла ошибка при сохранении результата")
     *         )
     *     )
     * )
     */
    public function saveResult(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|nullable',
                'milliseconds' => 'required|integer',
            ]);

            $member = null;

            if ($request->filled('email')) {

                $member = Member::firstOrNew(['email' => $request->input('email')]);

                if (!$member->exists) {
                    $member->save();
                }
            }

            Result::updateOrCreate(
                ['member_id' => $member ? $member->id : null],
                ['milliseconds' => $request->input('milliseconds')]
            );

            return response()->json(['message' => 'Результат успешно сохранен']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при сохранении результата'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/top-results",
     *     summary="Получить топ-10 результатов",
     *     tags={"Results"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email участника",
     *         @OA\Schema(type="string", format="email"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="top",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="place", type="integer"),
     *                         @OA\Property(property="milliseconds", type="integer"),
     *                     ),
     *                 ),
     *                 @OA\Property(
     *                     property="self",
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="place", type="integer"),
     *                     @OA\Property(property="milliseconds", type="integer"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *         ),
     *     ),
     * )
     */
    public function getTopResults(Request $request)
    {
        try {
            $response = $this->topResultsService->getTopResults($request);

            return response()->json($response);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при получении топ-10 результатов'], 500);
        }
    }
}
