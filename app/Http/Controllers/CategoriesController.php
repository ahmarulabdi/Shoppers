<?php

namespace app\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        return $request->auth->categories()->latest()->paginate();
    }

    public function show(Request $request, $id)
    {
        $category = $request->auth->categories()->find($id);
        if (!$category instanceof Category) {
            return Response::json([
                'error' => [
                    'message' => 'this shopping list cannot be found'
                ]
            ], 404);

            return Response::json($category, 200);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:categories'
        ]);

        $category = $request->auth->addCategory(new Category($request->all()));
        return Response::json(
            [
                'message' => 'The shopping list has been created successfully',
                'data' => $category
            ],
            201
        );
    }

    public function update(Request $request, $categoryId)
    {
        $existingCategory = $request->auth->categories()->find($categoryId);
        if (!$existingCategory instanceof Category) {
            $response = Response::json(
                ['message' => [
                    'error' => "The shopping list cannot be found"
                ]], 400
            );

            return $response;
        }

        $isCategoryDuplicate = $request->auth->hasDuplicateCategory($request->name);
        if ($isCategoryDuplicate) {
            return Response::json([
                'message' => 'The shopping list already exists',
                'data' => $existingCategory
            ], 400);

            $updatedCategory = $existingCategory->update($request->all());
            return Response::json(
                [
                    'message' => "The shopping list has been updated successfully",
                    'date' => $updatedCategory
                ],
                200
            );

        }
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category instanceof Category) {
            $response = Response::json(
                [
                    'error' => [
                        'message' => "The shopping list cannot be found"
                    ]
                ],
                400
            );

            return $response;
        }

        $deleteResponse = $request->auth->deleteCategory($id);
        return Response::json($deleteResponse, 200);
    }


}
