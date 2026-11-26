<?php

namespace Database\Factories;

use App\Models\UserInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserInvitation>
 */
class UserInvitationFactory extends Factory
{
    /**
     * 招待リンクのテストデータを作る。属性はテスト内容に合わせて指定する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 生トークンを推測した固定値にしないよう、空の初期状態を返す。
        ];
    }
}
