<?php

namespace App\Http\Controllers;

use App\CompanyResearchAssistant;
use App\Models\CareerApplication;
use App\Models\ResearchConversation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ResearchChatController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureLocalPreview();
        $user = Auth::user();
        $conversations = $user->researchConversations()->latest('updated_at')->limit(30)->get();
        $conversationId = $request->integer('conversation');
        $activeConversation = $conversationId ? $user->researchConversations()->find($conversationId) : null;

        return view('research.index', [
            'applications' => CareerApplication::query()->orderBy('company')->get(),
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }

    public function send(Request $request, CompanyResearchAssistant $assistant): JsonResponse
    {
        $this->ensureLocalPreview();
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:main,summer,autumn'],
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'integer'],
            'career_application_id' => ['nullable', 'integer'],
        ]);
        $user = Auth::user();
        $application = null;
        if (! empty($validated['career_application_id'])) {
            $application = $user->careerApplications()->findOrFail($validated['career_application_id']);
        }
        $conversation = ! empty($validated['conversation_id'])
            ? $user->researchConversations()->findOrFail($validated['conversation_id'])
            : null;
        if ($conversation && $conversation->company_name !== $validated['company_name']) {
            abort(422, '会話の企業名が一致しません。');
        }

        $messages = $conversation?->messages ?? [];
        try {
            $reply = $assistant->reply($validated['company_name'], $validated['message'], $messages);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => ($exception instanceof \RuntimeException) ? $exception->getMessage() : 'AIの調査に失敗しました。通信状態を確認して、もう一度お試しください。'], 422);
        }

        $messages[] = ['role' => 'user', 'text' => $validated['message'], 'sources' => []];
        $messages[] = ['role' => 'assistant', 'text' => $reply['answer'], 'sources' => $reply['sources']];
        $conversation ??= new ResearchConversation([
            'company_name' => $validated['company_name'],
            'category' => $validated['category'],
            'title' => mb_substr($validated['message'], 0, 255),
            'messages' => [],
        ]);
        $conversation->career_application_id = $application?->id;
        $conversation->category = $validated['category'];
        $conversation->messages = $messages;
        $conversation->save();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages,
            'answer' => $reply['answer'],
            'sources' => $reply['sources'],
        ]);
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
