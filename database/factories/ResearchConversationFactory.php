<?php

namespace Database\Factories;

use App\Models\ResearchConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchConversation>
 */
class ResearchConversationFactory extends Factory
{
    /**
     * 旧企業研究会話のテストデータを作る。追加属性は呼び出し側から渡す。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 会話機能は廃止済みのため、空の初期値を維持する。
        ];
    }
}
