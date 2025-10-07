<?php

namespace SolutionForest\WorkflowEngine\Core;

use InvalidArgumentException;

// Use opis/json-schema only if installed. We typehint as mixed to avoid hard dependency.
/**
 * Validates workflow JSON against the bundled JSON Schema (optional dependency).
 */
class JsonSchemaValidator
{
    /** @var object|null */
    private $validator;

    /**
     * @param object|null $validator Optional preconfigured Opis\\JsonSchema\\Validator instance
     */
    public function __construct(object $validator = null)
    {
        if ($validator !== null) {
            $this->validator = $validator;
        } elseif (class_exists('Opis\\JsonSchema\\Validator')) {
            $this->validator = new \Opis\JsonSchema\Validator();
        } else {
            $this->validator = null;
        }
    }

    /**
     * Validate the given workflow data using the bundled schema.
     *
     * @param array<string, mixed> $data
     */
    public function validate(array $data): void
    {
        if ($this->validator === null) {
            // No-op if opis/json-schema is not installed
            return;
        }

        $schemaPath = dirname(__DIR__) . '/Schema/workflow.schema.json';
        $schemaContent = @file_get_contents($schemaPath);
        if ($schemaContent === false) {
            throw new InvalidArgumentException('Unable to read workflow schema at ' . $schemaPath);
        }

        $schema = json_decode($schemaContent);

        // @phpstan-ignore-next-line Dynamic call to optional dependency
        $result = $this->validator->validate($data, $schema);

        // @phpstan-ignore-next-line Dynamic call to optional dependency
        if (! $result->isValid()) {
            // @phpstan-ignore-next-line Dynamic call to optional dependency
            $formatter = new \Opis\JsonSchema\Errors\ErrorFormatter();
            // @phpstan-ignore-next-line Dynamic call to optional dependency
            $errors = $formatter->format($result->error());

            throw new InvalidArgumentException('Workflow JSON does not match schema: ' . json_encode($errors));
        }
    }
}

