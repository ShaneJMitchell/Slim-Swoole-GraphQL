<?php

namespace App\Controllers;

use \Slim\Http\Request;
use \Slim\Http\Response;

use GraphQL\GraphQL;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\QueryDepth;

/**
 * Class BaseController
 */
class BaseController
{
    /**
     * @var mixed \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Slim\Container
     */
    protected $container;

    /**
     * BaseController constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function healthz(Request $request, Response $response)
    {
        return $response->withJson('success');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function graphql(Request $request, Response $response)
    {
        $schema = require __DIR__ . '/../../app/Schema.php';
        $maxDepth = getenv('GRAPHQL_MAX_DEPTH') ?: 4;
        $debug = getenv('GRAPHQL_DEBUG_LEVEL') ?: 15;
        $introspection = getenv('GRAPHQL_INTROSPECTION') ?: true;
        $input = json_decode($request->getBody(), true);
        $query = isset($input['query']) ? $input['query'] : null;
        $variables = isset($input['variables']) ? $input['variables'] : null;

        $this->logger->addInfo($request->getBody());
        $this->logger->addInfo($request->getHeaders());
        $this->logger->addInfo($request->getParsedBody());
        $this->logger->addInfo($request->getParams());

        if (!$introspection) {
            DocumentValidator::addRule(new DisableIntrospection());
        }

        DocumentValidator::addRule(new QueryDepth($maxDepth));

        try {
            $schema->assertValid();
            $result = GraphQL::executeQuery($schema, $query, null, $this->container, $variables);

            $output = $result->toArray($debug);
        } catch (\Exception $e) {
            $output = ['error' => $e->getMessage()];
        }

        return $response->withAddedHeader('Access-Control-Allow-Origin', '*')
            ->withAddedHeader('Access-Control-Allow-Headers', 'content-type')
            ->withAddedHeader('Content-Type', 'application/json')
            ->withJson($output);
    }
}
