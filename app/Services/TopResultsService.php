<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Сервис для работы с топ-результатами.
 */
class TopResultsService
{
    /**
     * @var \App\Services\RankService
     */
    protected $rankService;

    /**
     * Конструктор сервиса.
     *
     * @param \App\Services\RankService $rankService Сервис для работы с рейтингом.
     */
    public function __construct(RankService $rankService)
    {
        $this->rankService = $rankService;
    }

    /**
     * Получает топ-10 результатов и информацию о рейтинге участника.
     *
     * @return array Массив с данными топ-результатов и информацией о рейтинге участника.
     */
    public function getTopResults($request)
    {
        // Валидация запроса
        $validator = Validator::make($request->all(), [
            'email' => 'email|nullable',
        ]);

        $validator->validate();

        // Запрос для получения топ-10 результатов
        DB::select('SET @row_number = 0;');

        $topResults = Result::select(
            DB::raw('(@row_number := @row_number + 1) as row_number'),
            'members.email',
            'results.milliseconds'
        )
            ->leftJoin('members', 'results.member_id', '=', 'members.id')
            ->whereNotNull('members.email')
            ->orderBy('results.milliseconds')
            ->limit(10)
            ->get();

        $response = [
            'data' => [
                'top' => $topResults->map(function ($result) {
                    return [
                        'email' => $this->hideEmail($result->email),
                        'place' => $result->row_number,
                        'milliseconds' => $result->milliseconds,
                    ];
                }),
                'self' => $request->filled('email') ? $this->rankService->getRankByEmail($request->input('email')) : null,
            ],
        ];

        return $response;
    }

    /**
     * Скрывает часть email для безопасного отображения.
     *
     * @param string $email Email участника.
     *
     * @return string Скрытая версия email.
     */
    protected function hideEmail($email)
    {
        $parts = explode('@', $email);

        if (count($parts) == 2) {
            $leftPart = $parts[0];
            $rightPart = $parts[1];

            $visibleCharacters = 2;
            $maskedLeftPart = substr($leftPart, 0, $visibleCharacters) . str_repeat('*', strlen($leftPart) - $visibleCharacters);
            $maskedEmail = $maskedLeftPart . '@' . $rightPart;

            return $maskedEmail;
        }

        return $email;
    }
}
