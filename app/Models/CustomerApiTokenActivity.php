<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  string  $id
 * @property  string  $token_id
 * @property  string  $action
 * @property  array   $request
 * @property  array   $response
 * @property  array   $properties
 * @property  Carbon  $created_at
 * @property  Carbon  $updated_at
 *
 * @property-read  CustomerApiToken  $token
 */
class CustomerApiTokenActivity extends Model
{
    use HasUlids;

    protected $table = "customer_api_token_activities";
    protected $fillable = ["token_id", "request", "response", "properties", "action"];
    protected $casts = ["request" => "array", "response" => "array", "properties" => "array"];

    public function token(): BelongsTo
    {
        return $this->belongsTo(CustomerApiToken::class, "token_id");
    }
}
