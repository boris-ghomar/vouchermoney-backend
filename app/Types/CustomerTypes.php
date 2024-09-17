<?php

namespace App\Types;

class CustomerTypes
{
    //user fields
    public string $name;
    public string $email;
    public string $username;
    public string $password;

    //customer fields
    public ?float $balance;
    public ?string $avatar;
    public ?int $max_vouchers_count;
    public ?int $max_voucher_amount;
    public ?int $role_id;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->balance = $data['balance'];
        $this->avatar = $data['avatar'] ?? null;
        $this->max_vouchers_count = $data['max_vouchers_count'] ?? null;
        $this->max_voucher_amount = $data['max_vouchers_amount'] ?? null;
        $this->role_id = $data['role_id'] ?? null;
     }
}
