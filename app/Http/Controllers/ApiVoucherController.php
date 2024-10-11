<?php

namespace App\Http\Controllers;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Http\Requests\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Vouchers\FreezeVoucherRequest;
use App\Http\Requests\Vouchers\RedeemVoucherRequest;
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

//    /**
//     * @return JsonResponse
//     */
//    public function index(): JsonResponse
//    {
//        /** @var CustomerApiToken $user */
//        $vouchers = $this->user->customer->vouchers;
//
//        return response()->json([
//            "status" => "success",
//            "vouchers" => VoucherResource::collection($vouchers)
//        ]);
//    }

    /**
     * @param CreateVoucherRequest $request
     * @return JsonResponse
     */
    public function create(CreateVoucherRequest $request): JsonResponse
    {
        $count = $request->count ?? 1;
        $amount = $request->amount;

        try {
            $vouchers = $this->customerService->generateVoucher($this->user->customer, $amount, $count);

            $response = response()->json([
                'status' => "success",
                'message' => 'Vouchers created successfully.',
                "vouchers" => $count > 1 ? VoucherResource::collection($vouchers) : [VoucherResource::make($vouchers)]
            ]);
        } catch (Exception $exception) {
            $this->activityService->apiException($exception);

            $response = response()->json([
                'status' => "error",
                'message' => $exception->getMessage()
            ], 400);
        }

        $this->activityService->apiActivity("generate", [
            "ip" => $request->ip(),
            "body" => $request->all(),
            "route" => $request->route(),
            "headers" => $request->header()
        ], [
            "headers" => $response->headers,
            "response" => $response->getData(),
            "status" => $response->status(),
        ]);

        return $response;
    }

    /**
     * @param FreezeVoucherRequest $request
     * @return JsonResponse
     */
    public function freeze(FreezeVoucherRequest $request): JsonResponse
    {
        try {
            $voucher = Voucher::findByCode($request->code);

            if (empty($voucher) || $voucher->customer_id !== $this->user->customer_id) {
                $response = response()->json([
                    "status" => "error",
                    "message" => "Voucher not found"
                ], 404);
            } else {
                if ($voucher->is_frozen) {
                    $description = "Voucher has already been frozen";
                } else {
                    $this->voucherService->freeze($voucher);
                    $description = "Voucher successfully frozen";
                }

                $response = response()->json([
                    "status" => "success",
                    "message" => $description,
                    "voucher" => VoucherResource::make($voucher)
                ]);
            }
        } catch (Exception $exception) {
            $this->activityService->apiException($exception);

            $response = response()->json([
                "status" => "error",
                "message" => $exception->getMessage()
            ], 400);
        }

        $this->activityService->apiActivity("freeze", $request, $response, ! empty($voucher) ? ["voucher" => $voucher] : []);

        return $response;
    }

    /**
     * @param FreezeVoucherRequest $request
     * @return JsonResponse
     */
    public function unfreeze(FreezeVoucherRequest $request): JsonResponse
    {
        try {
            $voucher = Voucher::findByCode($request->code);

            if (empty($voucher) || $voucher->customer_id !== $this->user->customer_id) {
                $response = response()->json([
                    "status" => "error",
                    "message" => "Voucher not found"
                ], 404);
            } else {
                if ($voucher->is_active) {
                    $description = "Voucher has already been active";
                } else {
                    $this->voucherService->activate($voucher);
                    $description = "Voucher successfully activated";
                }

                $response = response()->json([
                    "status" => "success",
                    "message" => $description,
                    "voucher" => new VoucherResource($voucher)
                ]);
            }
        } catch (Exception $exception) {
            $this->activityService->apiException($exception);

            $response = response()->json([
                "status" => "error",
                "message" => $exception->getMessage()
            ], 400);
        }

        $this->activityService->apiActivity("activate", $request, $response, ! empty($voucher) ? ["voucher" => $voucher] : []);

        return $response;
    }

    /**
     * @param RedeemVoucherRequest $request
     * @return JsonResponse
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

        $this->activityService->apiActivity("redeem", $request, $response, ["voucher" => $voucher ?? null,]);

        return $response;
    }
}
