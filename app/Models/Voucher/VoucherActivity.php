<?php

namespace App\Models\Voucher;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Voucher\VoucherActivityLog\VoucherActivityLog;

/**
 * @property  int          $id
 * @property  string       $code
 * @property  string       $from_state
 * @property  string       $to_state
 * @property  string|null  $description
 * @property  string|null  $user_data
 * @property  Carbon       $time
 *
 * @property  User|null    $user
 */
class VoucherActivity extends Model
{
    protected $table = "voucher_activity";
    public $timestamps = false;

    const STATE_CREATED = "created";
    const STATE_ACTIVE = "active";
    const STATE_FROZEN = "frozen";
    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $fillable = [
        "code",
        "from_state",
        "to_state",
        "description",
        "user",
        "time"
    ];

    protected $casts = [
        "user" => "array",
        "time" => "datetime"
    ];

    public function getUserAttribute(): User|null
    {
        if (empty($this->user_data["id"]))
            return null;

        $user = User::find($this->user_data["id"]);

        if (!$user)
            return null;

        return $user;
    }

    public static function make(string $code, string $from, string $to, User|null $user = null, string|null $description = null): static
    {
        $activity = new static();
        $activity->code = $code;
        $activity->from_state = $from;
        $activity->to_state = $to;

        if (!empty($user)) $activity->user_data = $user->toJson();

        if (!empty($description)) $activity->description = $description;

        $activity->save();

        return $activity;
    }

    public static function log(string $voucherCode, User $user = null): VoucherActivityLog
    {
        $voucherActivity = new static();
        $voucherActivity->code = $voucherCode;

        if (!empty($user)) $voucherActivity->user_data = $user;

        return new VoucherActivityLog($voucherActivity);
    }
}
