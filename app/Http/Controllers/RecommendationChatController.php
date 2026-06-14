<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskRecommendationRequest;
use App\Models\Customer;
use App\Neuron\RecommendationAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use NeuronAI\Chat\Messages\UserMessage;
use Throwable;

class RecommendationChatController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('recommendations.chat', [
            'customers' => $customers,
            'customerId' => old('customer_id'),
            'question' => old('question'),
            'answer' => session('recommendation_answer'),
            'error' => session('recommendation_error'),
        ]);
    }

    public function store(AskRecommendationRequest $request): JsonResponse|RedirectResponse
    {
        $customerId = (int) $request->validated('customer_id');
        $question = (string) $request->validated('question');

        try {
            $answer = RecommendationAgent::make()
                ->chat(new UserMessage($this->buildPrompt($customerId, $question)))
                ->getMessage()
                ->getContent();
        } catch (Throwable $exception) {
            report($exception);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'The recommendation assistant could not complete your request. Check OpenRouter credentials and Neo4j connectivity.',
                ], 502);
            }

            return back()
                ->withInput()
                ->with('recommendation_error', 'The recommendation assistant could not complete your request. Check OpenRouter credentials and Neo4j connectivity.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'customer_id' => $customerId,
                'question' => $question,
                'answer' => $answer,
            ]);
        }

        return back()
            ->withInput()
            ->with('recommendation_answer', $answer);
    }

    private function buildPrompt(int $customerId, string $question): string
    {
        return sprintf(
            "The active customer_id for this conversation is %d.\n".
            "Use customer_id %d when calling customer_purchase_history or offer_eligibility unless the user explicitly refers to another customer.\n\n".
            "User question:\n%s",
            $customerId,
            $customerId,
            $question,
        );
    }
}
