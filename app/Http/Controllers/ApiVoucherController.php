<?php

namespace App\Http\Controllers;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Http\Requests\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Vouchers\FreezeVoucherRequest;
use App\Http\Requests\Vouchers\RedeemVoucherRequest;
use App\Models\Customer;
use App\Models\Voucher\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class ApiVoucherController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    private function getAuthenticatedUser(Request $request)
    {
        return $request->get('authenticatedUser');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        $vouchers = $user->customer->vouchers()->onlyActive()->get();
        return response()->json([
            "status" => 201,
            "vouchers" =>[$vouchers]
        ]);
    }

    /**
     * @param CreateVoucherRequest $request
     * @return JsonResponse
     */
    public function create(CreateVoucherRequest $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        for ($i = 0; $i < $request->count; $i++) {
            $user->customer->generateVoucher($request->amount);
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
        return $this->voucherState($request->code, Voucher::FREEZE_VOUCHER);
    }

    /**
     * @param FreezeVoucherRequest $request
     * @return JsonResponse
     */
    public function unfreeze(FreezeVoucherRequest $request): JsonResponse
    {
        return $this->voucherState($request->code, Voucher::ACTIVATE_VOUCHER);
    }

    /**
     * @param string $code
     * @param string $action
     * @return JsonResponse
     */
    private function voucherState(string $code, string $action): JsonResponse
    {
        $user = $this->getAuthenticatedUser(request());
        $voucher = Voucher::findOrFail($code);

        if ($voucher->customer_id !== $user->customer_id) {
            return response()->json([
                'status' => 404,
                'message' => 'Voucher not found or access denied.',
            ]);
        }

        $voucher->{$action}();

        return response()->json([
            'status' => 200,
            'message' => $action === 'freeze' ? 'Voucher frozen successfully.' : 'Voucher unfrozen successfully.',
        ]);
    }

    /**
     * @param RedeemVoucherRequest $request
     * @return JsonResponse
     */
    public function redeem(RedeemVoucherRequest $request): JsonResponse
    {
        $user = $request->get('authenticatedUser');
        $voucher = Voucher::findOrFail($request->code);
        if (!$voucher || $voucher->customer_id !== $user->customer_id)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Something went wrong'
            ]);
        }
        try {
            $recipient = $request->recipient_id ? Customer::find($request->recipient_id) : null;

            $archivedVoucher = $voucher->redeem($recipient);

            return response()->json([
                'status' => 200,
                'message' => 'Voucher redeemed successfully.',
                'archivedVoucher' => $archivedVoucher,
            ]);
        } catch (AttemptToRedeemFrozenVoucher $e) {
            return response()->json([
                'status' => 403,
                'message' => 'Cannot redeem a frozen voucher.',
            ], 403);
        }
    }
}
