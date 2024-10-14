<?php

namespace App\Http\Controllers;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Http\Requests\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Vouchers\FreezeVoucherRequest;
use App\Http\Requests\Vouchers\RedeemVoucherRequest;
use App\Http\Requests\Vouchers\ViewVoucherRequest;
use App\Http\Resources\ArchivedVoucherResource;
use App\Http\Resources\VoucherResource;
use App\Models\CustomerApiToken;
use App\Models\Voucher\Voucher;
use App\Services\Activity\Contracts\ActivityServiceContract;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Voucher Money API Documentation",
 *     version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Enter the token as 'Bearer {token}'"
 * )
 *
 * @OA\Tag(
 *     name="Vouchers",
 *     description="Operations related to vouchers"
 * )
 */
class ApiVoucherController extends Controller
{
    protected CustomerApiToken $user;

    public function __construct(
        Request $request,
        protected CustomerServiceContract $customerService,
        protected ActivityServiceContract $activityService,
        protected VoucherServiceContract $voucherService
    ) {
        $this->user = $request->user();
    }

    /**
     * @OA\Get(
     *     path="/vouchers/view",
     *     summary="View Voucher by Code",
     *     description="Get voucher details using a unique voucher code.",
     *     tags={"Vouchers"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="The voucher code to search for",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voucher found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Voucher found."),
     *             @OA\Property(property="voucher", ref="#/components/schemas/VoucherResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Voucher not found or already used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Voucher not found or already used."),
     *             @OA\Property(property="voucher", type="null", example=null)
     *         )
     *     ),
     * )
     *
     * View voucher details by code.
     *
     * @param ViewVoucherRequest $request
     * @return JsonResponse
     */
    public function view(ViewVoucherRequest $request): JsonResponse
    {
        $code = $request->code;

        $voucher = Voucher::findByCode($code);

        if (empty($voucher)) {
            $response = response()->json([
                'status' => "error",
                'message' => 'Voucher not found or already used.',
                "voucher" => null
            ], 400);
        } else {
            $response = response()->json([
                'status' => "success",
                'message' => 'Voucher found.',
                "voucher" => VoucherResource::make($voucher)
            ]);
        }

        $this->activityService->apiActivity("view", $request, $response);

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/v1/vouchers/generate",
     *     tags={"Vouchers"},
     *     summary="Generate vouchers",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateVoucherRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vouchers generated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vouchers generated successfully."),
     *             @OA\Property(property="vouchers", type="array", @OA\Items(ref="#/components/schemas/VoucherResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "amount": {"The amount field is required."},
     *                     "count": {"The count must be at least 1."}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function generate(CreateVoucherRequest $request): JsonResponse
    {
        $count = $request->count ?? 1;
        $amount = $request->amount;

        try {
            $vouchers = $this->customerService->generateVoucher($this->user->customer, $amount, $count);

            $response = response()->json([
                'status' => "success",
                'message' => 'Vouchers generated successfully.',
                "vouchers" => $count > 1 ? VoucherResource::collection($vouchers) : [VoucherResource::make($vouchers)]
            ]);
        } catch (Exception $exception) {
            $this->activityService->apiException($exception);

            $response = response()->json([
                'status' => "error",
                'message' => $exception->getMessage()
            ], 400);
        }

        $this->activityService->apiActivity("generate", $request, $response);

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/v1/vouchers/redeem",
     *     tags={"Vouchers"},
     *     summary="Redeem a voucher",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RedeemVoucherRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voucher redeemed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Voucher redeemed successfully"),
     *             @OA\Property(property="voucher", ref="#/components/schemas/ArchivedVoucherResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Voucher not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function redeem(RedeemVoucherRequest $request): JsonResponse
    {
        try {
            $voucher = Voucher::findByCode($request->code);

            if (empty($voucher) || $voucher->customer_id !== $this->user->customer_id) {
                $response = response()->json([
                    'status' => "error",
                    'message' => 'Voucher not found'
                ], 404);
            } else {
                $archived = $this->customerService->redeemVoucher($this->user->customer, $voucher, $request->note);

                $response = response()->json([
                    'status' => "success",
                    'message' => 'Voucher redeemed successfully',
                    'voucher' => new ArchivedVoucherResource($archived),
                ]);
            }
        } catch (AttemptToRedeemFrozenVoucher $exception) {
            $this->activityService->apiException($exception);
            $response = response()->json([
                'status' => "error",
                'message' => 'Cannot redeem frozen voucher',
            ], 400);
        } catch (Exception $exception) {
            $this->activityService->apiException($exception);
            $response = response()->json([
                'status' => "error",
                'message' => $exception->getMessage(),
            ], 400);
        }

        $this->activityService->apiActivity("redeem", $request, $response);

        return $response;
    }
}
