<?php

namespace App\Services;

use App\Http\Requests\UserRequest;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Types\CustomerTypes;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function __construct(protected UserService $userService){}

    /**
     * Get all customers.
     *
     * @return Collection
     */
    public function getAllCustomers(): Collection
    {
        return Customer::all();
    }

    /**
     * Get a customer by ID.
     *
     * @param int $id
     * @return Customer
     * @throws ModelNotFoundException
     */
    public function getCustomerById(int $id): Customer
    {
        return Customer::findOrFail($id);
    }


    /**
     * @param CustomerTypes $data
     * @return array
     * @throws Exception
     */
    public function createCustomer(CustomerTypes $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'username' => $data->username,
                'password' => Hash::make($data->password),
            ]);
            $customer = Customer::create([
                'user_id' => $user->id,
                'name' => $data->name,
                'balance' => $data->balance,
                'avatar' => $data->avatar,
                'max_vouchers_count' => $data->max_vouchers_count,
                'max_voucher_amount' => $data->max_voucher_amount,
            ]);
            $user->roles()->attach($data->role_id);
            $role = Role::findOrFail($data->role_id);
            $permissions = $role->permissions;

            foreach ($permissions as $permission) {
                $user->permissions()->attach($permission->id);
            }
            DB::commit();

            return [
                'user' => $user,
                'customer' => $customer,
            ];
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing customer.
     *
     * @param CustomerTypes $data
     * @param Customer $customer
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function updateCustomer(CustomerTypes $data,Customer $customer,User $user): array
    {
        DB::beginTransaction();
        try {
            $user->update([
                'name' => $data->name,
                'email' => $data->email,
                'username' => $data->username,
                'password' => $data->password ? Hash::make($data->password) : $user->password,

            ]);
            $customer->update([
                'name' => $data->name,
                'balance'=> $data->balance,
                'avatar' => $data->avatar,
                'max_vouchers_count' => $data->max_vouchers_count,
                'max_voucher_amount' => $data->max_voucher_amount
            ]);
            $user->roles()->sync([$data->role_id]);
            $role = Role::findOrFail($data->role_id);
            $permissions = $role->permissions->pluck('id')->toArray();
            $user->permissions()->sync($permissions);
            DB::commit();
            return [
                'user' => $user,
                'customer' => $customer
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete the customer
     * @param int $id
     * @return bool|null
     */

    public function deleteCustomer(int $id): ?bool
    {
        $customer = $this->getCustomerById($id);
        return $customer->delete();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deactivateCustomer(int $id): bool
    {
        $customer = Customer::findOrFail($id);
        $user = $customer->user;
        $user->is_active = false;
        return $user->save();
    }

    public function attachCustomerChild(array $data, int $customerId)
    {
        $childUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'parent_id' => $customerId
        ]);

        if (isset($data['permissions'])) {
            $childUser->permissions()->sync($data['permissions']);
        }

        return $childUser;

    }
}
