<?php

namespace App\Http\Controllers;

use App\Helpers\ApiJsonResponseHelper;
use App\Models\Animal;
use Illuminate\Http\Request;
use Validator;

class AnimalController extends Controller
{

    /**
     * Then it redirect the user to Animal model to showAllAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/web/show-all-animals",
     * security={{"bearer_token":{}}},
     * summary="Show animal by seller",
     * tags={"User Pet"},
     * @OA\Parameter(
     *       name="categoryId",
     *       in="query",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="keyword",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="gender",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="region",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="race_id",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showAllAnimal(Request $request)
    {
        $animal = new Animal();
        $data = $animal->showAllAnimal($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showFeaturePet function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-all-featured-animals",
     * security={{"bearer_token":{}}},
     * summary="Show all Animals",
     * tags={"Animal"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */

    public function showFeaturePet()
    {
        $animal = new Animal();
        $data = $animal->showFeaturePet();
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to Animal model to store function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/seller/add-animal",
     * summary="Store New Animal",
     * security={{"bearer_token":{}}},
     * tags={"Seller-Animal"},
     * @OA\Parameter(
     *       name="category_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="race_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="name",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="vaccine",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="preffernces",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="age",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="date_of_birth",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="address",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="city",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="country",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="postal_code",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="longitude",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="latitude",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="gender",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="region",
     *       in="query",
     *      required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="meta_data",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="media",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     *     @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
            'vaccine' => 'required',
            'age' => 'required',
            'date_of_birth' => 'required|date',
            'address' => 'required',
            'city' => 'required',
            'country' => 'required',
            'postal_code' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'media' => 'required',
            'gender' => 'required',
            'race_id' => 'required|exists:categories,id',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        foreach ($request->media as $key => $value){
            if (!is_string($value)) {
                return ApiJsonResponseHelper::errorResponse('Sorry For inconvenience, Please Hard Refresh and try again.');
            }
        }
        if (!isset($request->adoption) && !isset($request->co_adoption)) {
            return ApiJsonResponseHelper::errorResponse("Please select any one adoption type");
        }
        $Animal = new Animal();
        $data = $Animal->store($request->all());

        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to Animal model to updateAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/seller/update-animal/{id}",
     * summary="Update a existing Animal",
     * security={{"bearer_token":{}}},
     * tags={"Seller-Animal"},
     * @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="category_id",
     *       in="query",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="race_id",
     *       in="query",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="name",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="vaccine",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="preffernces",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="age",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="date_of_birth",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="address",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="city",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="country",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="postal_code",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="longitude",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="latitude",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="gender",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="media",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     *     @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'exists:categories,id',
            'race_id' => 'exists:categories,id',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $Animal = new Animal();
        $data = $Animal->updateAnimal($request->all(), $id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showSingleAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-single-animal/{id}",
     * security={{"bearer_token":{}}},
     * summary="show single animal",
     * tags={"Animal"},
     * @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function show($id)
    {
        $animal = new Animal();
        $data = $animal->showSingleAnimal($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to deleteAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Delete(
     * path="/api/seller/delete-animal/{id}",
     * security={{"bearer_token":{}}},
     * summary="Delete a animal",
     * tags={"Seller-Animal"},
     * @OA\Parameter(
     *       name="id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function destroy($id)
    {
        $post = new Animal();
        $data = $post->deleteAnimal($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showAnimalsByCategory function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-by-category-animal/{category_id}",
     * security={{"bearer_token":{}}},
     * summary="Show animal by category",
     * tags={"Animal"},
     * @OA\Parameter(
     *       name="category_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showByCategory($category_id)
    {
        $animal = new Animal();
        $data = $animal->showAnimalsByCategory($category_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showAnimalsBySeller function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/seller/show-by-seller-animal",
     * security={{"bearer_token":{}}},
     * summary="Show animal by seller",
     * tags={"Seller-Animal"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showBySeller()
    {
        $animal = new Animal();
        $data = $animal->showAnimalsBySeller();
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showSellerAnimalDetail function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/seller/show-seller-animal-detail/{animal_id}",
     * security={{"bearer_token":{}}},
     * summary="Show animal details for seller",
     * tags={"Seller-Animal"},
     * * @OA\Parameter(
     *       name="animal_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showSellerAnimalDetail($animal_id)
    {
        $animal = new Animal();
        $data = $animal->showSellerAnimalDetail($animal_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to likeAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/user/like-animal/{animal_id}",
     * security={{"bearer_token":{}}},
     * summary="Like animal",
     * tags={"Animal"},
     * @OA\Parameter(
     *       name="animal_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function likeAnimal($animal_id)
    {
        $animal = new Animal();
        $data = $animal->likeAnimal($animal_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to dislikeAnimal function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/user/dislike-animal/{animal_id}",
     * security={{"bearer_token":{}}},
     * summary="Dislike animal",
     * tags={"Animal"},
     * @OA\Parameter(
     *       name="animal_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function dislikeAnimal($animal_id)
    {
        $animal = new Animal();
        $data = $animal->dislikeAnimal($animal_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Animal model to showAnimalTinderView function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/user/show-tinder-animals/{pagintatePage}",
     * security={{"bearer_token":{}}},
     * summary="Show animals for tinder view",
     * tags={"Animal"},
     * @OA\Parameter(
     *       name="pagintatePage",
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showAnimalTinderView($pagintatePage = 0)
    {
        $animal = new Animal();
        $data = $animal->showAnimalTinderView($pagintatePage);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }
}
