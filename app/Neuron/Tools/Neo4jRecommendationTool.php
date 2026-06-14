<?php

namespace App\Neuron\Tools;

use App\Services\Neo4jService;
use App\Support\RunnableCypher;
use Laudis\Neo4j\Databags\SummarizedResult;
use NeuronAI\Tools\Tool;

abstract class Neo4jRecommendationTool extends Tool
{
    protected function neo4j(): Neo4jService
    {
        return app(Neo4jService::class);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function runCypher(string $cypher, array $parameters = []): SummarizedResult
    {
        $this->log('Neo4j query', [
            'cypher' => $cypher,
            'parameters' => $parameters,
            'runnable_cypher' => RunnableCypher::format($cypher, $parameters),
        ]);

        return $this->neo4j()->run($cypher, $parameters);
    }

    public function execute(): void
    {
        $this->log('Recommendation tool invoked', [
            'input' => $this->getInputs(),
        ]);

        try {
            parent::execute();
        } finally {
            $this->log('Recommendation tool completed', [
                'input' => $this->getInputs(),
                'output' => $this->loggedOutput(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function log(string $message, array $context): void
    {
        if (! function_exists('app') || ! app()->bound('log')) {
            return;
        }

        logger()->info($message, ['tool' => $this->getName(), ...$context]);
    }

    /**
     * @return array<string, mixed>|string|null
     */
    protected function loggedOutput(): array|string|null
    {
        if ($this->result === null) {
            return null;
        }

        $decoded = json_decode($this->result, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $this->result;
    }
}
