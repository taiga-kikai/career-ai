<?php

namespace App;

class CareerApplicationDefaults
{
    /** @return array<int, array{category: string, company: string, program: string|null, status: string, detail: string}> */
    public static function records(): array
    {
        $groups = [
            'main' => [
                ['サンプルソフト株式会社', '開発職', '応募予定', 'デモ用の応募先です'],
                ['みらいデータ株式会社', null, '選考中', '一次面接を予定しています'],
                ['あおぞらシステムズ', null, '結果待ち', 'エントリーシートの結果待ちです'],
                ['Career AI テクノロジー', null, '内々定', 'デモ用の記録です'],
            ],
            'summer' => [
                ['サンプルソフト株式会社', '1day・オンライン', '参加済み', 'デモ用の参加記録です'],
                ['みらいデータ株式会社', '開発体験コース', '結果待ち', '参加結果の連絡待ちです'],
                ['あおぞらシステムズ', null, '不合格', 'デモ用の選考記録です'],
                ['Career AI テクノロジー', null, '辞退', 'デモ用の記録です'],
            ],
            'autumn' => [
                ['サンプルソフト株式会社', null, '参加確定', 'オンライン・デモ用の予定です'],
                ['みらいデータ株式会社', null, '選考中', '適性検査を受検予定です'],
                ['あおぞらシステムズ', null, '応募予定', '応募を検討中です'],
                ['Career AI テクノロジー', null, '不合格', 'デモ用の選考記録です'],
            ],
        ];

        $records = [];

        foreach ($groups as $category => $applications) {
            foreach ($applications as [$company, $program, $status, $detail]) {
                $records[] = compact('category', 'company', 'program', 'status', 'detail');
            }
        }

        return $records;
    }
}
