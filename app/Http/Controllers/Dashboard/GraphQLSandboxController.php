<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExecuteGraphsqlQueryRequest;
use App\Services\Shop\ShopService;
use Illuminate\Contracts\View\View;

class GraphQLSandboxController extends Controller
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function index(): View
    {
        return view('graphql-sandbox');
    }

    public function execute(ExecuteGraphsqlQueryRequest $request): View
    {
        $query = $request->validated()['query'];
        $useAdminAPI = $request->boolean('use_admin_api', false);
        $variables = $request->input('variables')
            ? json_decode($request->input('variables'), true)
            : [];

        $resultData = null;
        $graphqlErrors = null;

        try {
            $response = $this->shopService->query(
                $query,
                $variables,
                [],
                'graphql',
                $useAdminAPI,
            );

            $resultData = $response->getData() ?? null;
            $graphqlErrors = $response->getErrors() ?: null;
            $exceptionMessage = $response->getFullErrorMessage() ?? null;
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }

        return view('graphql-sandbox', [
            'query' => $query,
            'variables' => $request->input('variables'),
            'result' => $resultData ?
                json_encode($resultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : null,
            'errors' => $graphqlErrors ?
                json_encode($graphqlErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : null,
            'exception_message' => $exceptionMessage,
        ]);
    }

}
