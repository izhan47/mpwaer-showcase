<?php

namespace App\Http\Controllers;

use App\Helpers\ApiJsonResponseHelper;
use App\Models\UserAdoptionAnimal;
use Illuminate\Http\Request;
use Validator;

class AdoptionController extends Controller
{
    /**
     * Then it redirect the user to UserAdoptionAnimal model to showUserPet function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/user/show-all-user-pets",
     * security={{"bearer_token":{}}},
     * summary="Show all User Pets",
     * tags={"User Pet"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showUserPet()
    {
        $adoption = new UserAdoptionAnimal();
        $data = $adoption->showUserPet();
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to UserAdoptionAnimal model to addUserPet function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/user/addoption-pet-request",
     * summary="New Animal Adoption Request",
     * security={{"bearer_token":{}}},
     * tags={"User Pet"},
     * @OA\Parameter(
     *       name="animal_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="type",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="detail",
     *       in="query",
     *       @OA\Schema(
     *          type="text"
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
            'animal_id' => 'required|exists:animals,id',
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $adoption = new UserAdoptionAnimal();
        $data = $adoption->addUserPet($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to UserAdoptionAnimal model to deleteAdoption function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Delete(
     * path="/api/user/remove-adoption/{id}",
     * security={{"bearer_token":{}}},
     * summary="Delete a animal adoption request",
     * tags={"User Pet"},
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
    public function destory($id)
    {
        $adoption = new UserAdoptionAnimal();
        $data = $adoption->deleteAdoption($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to UserAdoptionAnimal model to approveAdptionRequest function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\GET(
     * path="/api/seller/approve-adoption/{id}",
     * security={{"bearer_token":{}}},
     * summary="Approve a animal adoption request",
     * tags={"Seller-Adoption-API"},
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
    public function approveAdptionRequest($id)
    {
        $adoption = new UserAdoptionAnimal();
        $data = $adoption->approveAdptionRequest($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }
    /**
     * Then it redirect the user to UserAdoptionAnimal model to rejectAdptionRequest function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\GET(
     * path="/api/seller/reject-adoption/{id}",
     * security={{"bearer_token":{}}},
     * summary="Reject a animal adoption request",
     * tags={"Seller-Adoption-API"},
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
    public function rejectAdptionRequest($id)
    {
        $adoption = new UserAdoptionAnimal();
        $data = $adoption->rejectAdptionRequest($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }
    /**
     * Then it redirect the user to UserAdoptionAnimal model to rejectAdptionRequest function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\GET(
     * path="/api/seller/read-adoption-request/{id}",
     * security={{"bearer_token":{}}},
     * summary="Reject a animal adoption request",
     * tags={"Seller-Adoption-API"},
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
    public function adoptionRequestRead($id)
    {
        $adoption = UserAdoptionAnimal::find($id);
        if (!$adoption) {
            return ApiJsonResponseHelper::errorResponse('Adoption Not Found');
        }
        $adoption->read = 1;
        $adoption->save();
        return ApiJsonResponseHelper::successResponse($adoption, 'Success');
    }
}
