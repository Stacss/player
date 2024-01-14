<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с рейтингом участников.
 */
class RankService
{
    public function getRankByEmail($email)
    {
        /**
         * Получает рейтинг (порядковый номер) участника по email.
         *
         * Использует подзапрос для нумерации участников в порядке возрастания
         * времени (milliseconds). Затем извлекает информацию о рейтинге участника
         * с указанным email. Возвращает объект с данными рейтинга.
         *
         * @param string $email Email участника, для которого нужно получить рейтинг.
         *
         * @return \Illuminate\Database\Eloquent\Model|null Объект с данными рейтинга участника
         *                                                  или null, если участник не найден.
         */
        $rank = Result::select(DB::raw('subquery.place, subquery.email, subquery.milliseconds'))
            ->from(DB::raw('(SELECT @place := @place + 1 AS place, members.email, results.milliseconds
                   FROM results
                   JOIN members ON results.member_id = members.id
                   CROSS JOIN (SELECT @place := 0) AS init
                   ORDER BY results.milliseconds) AS subquery'))
            ->where('subquery.email', '=', $email)
            ->first();

        return $rank;
    }
}
