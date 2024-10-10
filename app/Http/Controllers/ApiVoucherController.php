<?php

namespace App\Http\Controllers;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Http\Requests\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Vouchers\FreezeVoucherRequest;
use App\Http\Requests\Vouchers\RedeemVoucherRequest;
use App\Http\Resources\ArchivedVoucherResource;
use App\Http\Resources\VoucherResource;
use App\Models\Customer\Customer;
use App\Models\CustomerApiToken;
use App\Models\Voucher\Voucher;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiVoucherController extends Controller
{
    protected CustomerApiToken $user;

    public function __construct(Request $request)
    {
        $this->user = $request->user();
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var CustomerApiToken $user */
        $vouchers = $this->user->customer->vouchers()->get();
        return response()->json([
            "status" => 201,
            "vouchers" => VoucherResource::collection($vouchers)
        ]);
    }

    /**
     * @param CreateVoucherRequest $request
     * @return JsonResponse
     */
    public function create(CreateVoucherRequest $request): JsonResponse
    {
        for ($i = 0; $i < $request->count; $i++) {
            $this->user->customer->generateVoucher($request->amount);
        }
        return response()->json([
            'status' => 201,
            'message' => 'Vouchers created successfully.'
        ]);
    }

    /**
     * @param FreezeVoucherRequest $request
     * @return JsonResponse
     */
    public function freeze(FreezeVoucherRequest $request): JsonResponse
    {
        dd(4);
        return $this->changeVoucherState($request->code, Voucher::STATE_FROZEN);
    }

    /**
     * @param FreezeVoucherRequest $request
     * @return JsonResponse
     */
    public function unfreeze(FreezeVoucherRequest $request): JsonResponse
    {
        return $this->changeVoucherState($request->code, Voucher::STATE_ACTIVE);
    }

    /**
     * @param string $code
     * @param bool $state
     * @return JsonResponse
     */
    private function changeVoucherState(string $code, bool $state): JsonResponse
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::findByCode($code);

        if (!$voucher || $voucher->customer_id !== $this->user->customer_id) {
            return response()->json([
                'status' => 404,
                'message' => 'Voucher not found or access denied.',
            ]);
        }

        $voucher->changeState($state);

        return response()->json([
            'status' => 200,
            'message' => $state ? 'Voucher unfrozen successfully.' : 'Voucher frozen successfully.',
        ]);
    }

    /**
     * @param RedeemVoucherRequest $request
     * @return JsonResponse
     */
    public function redeem(RedeemVoucherRequest $request): JsonResponse
    {
        $voucher = Voucher::findByCode($request->code);

        if (!$voucher || $voucher->customer_id !== $this->user->customer_id)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Something went wrong'
            ]);
        }

        try {
            $archivedVoucher = $voucher->redeem($request->note, $this->user->customer);

            return response()->json([
                'status' => 200,
                'message' => 'Voucher redeemed successfully.',
                'archivedVoucher' => new ArchivedVoucherResource($archivedVoucher),
            ]);
        } catch (AttemptToRedeemFrozenVoucher $e) {
            return response()->json([
                'status' => 403,
                'message' => 'Cannot redeem a frozen voucher.',
            ], 403);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 403,
                'message' => 'Something went wrong',
            ]);
        }
    }
}
