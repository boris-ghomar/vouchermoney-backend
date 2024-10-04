<?php

namespace App\Models\Voucher;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  int          $id
 * @property  string       $code
 * @property  string       $state
 * @property  array        $properties
 * @property  string|null  $user_id
 * @property  array        $user_data
 * @property  Carbon       $time
 *
 * @property  User|null    $user
 */
class VoucherActivity extends Model
{
    protected $table = "voucher_activity";
    public $timestamps = false;

    const STATE_CREATED = "created";
    const STATE_ACTIVATED = "activated";
    const STATE_FROZEN = "frozen";
    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $fillable = [
        "code",
        "state",
        "description",
        "user",
        "time"
    ];

    protected $casts = [
        "properties" => "array",
        "user_data" => "array",
        "time" => "datetime"
    ];

    public function getUser(): User|null
    {
        if (empty($this->user_data["id"]))
            return null;

        return User::find($this->user_data["id"]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function makeCreated(string $code, array $properties = []): static
    {
        return self::make($code, static::STATE_CREATED, $properties);
    }

    public static function makeRedeemed(string $code, array $properties = []): static
    {
        return self::make($code, static::STATE_REDEEMED, $properties);
    }

    public static function makeFrozen(string $code, array $properties = []): static
    {
        return self::make($code, static::STATE_FROZEN, $properties);
    }

    public static function makeActivated(string $code, array $properties = []): static
    {
        return self::make($code, static::STATE_ACTIVATED, $properties);
    }

    public static function makeExpired(string $code, array $properties = []): static
    {
        return self::make($code, static::STATE_EXPIRED, $properties);
    }

    private static function make(
        string $code,
        string $state,
        array $properties = []
    ): static {
        $activity = new static();
        $activity->code = $code;
        $activity->state = $state;

        if (! empty($properties)) $activity->properties = $properties;

        /** @var User $user */
        $user = request()->user();

        if (! empty($user)) {
            $activity->user_id = $user->id;
            $activity->user_data = $user;
        }

        $activity->save();

        return $activity;
    }
}
