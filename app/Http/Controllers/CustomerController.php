<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CustomerIndexRequest;
use App\Http\Requests\Customers\CustomerStoreRequest;
use App\Http\Requests\Customers\CustomerUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\FileUploadService;
use App\Types\CustomerTypes;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CustomerService   $customerService,
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Display a listing of the customers.
     *
     * @throws AuthorizationException
     */
    public function index(CustomerIndexRequest $request)
    {

        $this->authorize('viewAny', Customer::class);

        return $this->customerService->getAllCustomers();
    }

    /**
     * Display the specified customer.
     *
     * @param Customer $customer
     * @return CustomerResource
     * @throws NotFoundHttpException|AuthorizationException
     */
    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);
        $customer->load('user', 'user.children');
        return new CustomerResource($customer);
    }


    /**
     * Create a new customer/user
     * @param CustomerStoreRequest $request
     * @return CustomerResource
     * @throws Exception
     */
    public function store(CustomerStoreRequest $request): CustomerResource
    {
        $this->authorize('create', [Customer::class, null]);

        $dataArray = $request->all();
        $dataArray['avatar'] = $request->hasFile('avatar') ? $this->fileUploadService->uploadFile($request->file('avatar'), 'avatars') : null;
        $data = new CustomerTypes($dataArray);
        $result = $this->customerService->createCustomer($data);
        return new CustomerResource($result['customer']);
    }

    /**
     * Update customer/user details
     * @param CustomerUpdateRequest $request
     * @param int $id
     * @return CustomerResource
     * @throws ValidationException|Exception
     */
    public function update(CustomerUpdateRequest $request, int $id): CustomerResource
    {
        $customer = Customer::findOrFail($id);

        $this->authorize('update', $customer);

        $dataArray = $request->all();

        if ($request->hasFile('avatar')) {
            $dataArray['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $dataArray['avatar'] = $customer->avatar;
        }
        $data = new CustomerTypes($dataArray);
        $result = $this->customerService->updateCustomer($data, $customer, $customer->user);
        return new CustomerResource($result['customer']);
    }


    /**
     * Remove the specified customer from storage.
     *
     * @param Customer $customer
     * @return Response
     * @throws NotFoundHttpException|AuthorizationException
     */
    public function destroy(Customer $customer): Response
    {
        $this->authorize('delete', $customer);

        $this->customerService->deleteCustomer($customer->id);
        return response()->noContent();
    }

    /**
     * @param int $id
     * @return Response
     * @throws AuthorizationException
     */
    public function deactivateCustomer(int $id): Response
    {
        $customer = Customer::findOrFail($id);

        $this->authorize('deactivate', $customer);

        $this->customerService->deactivateCustomer($id);
        return response()->noContent();
    }

    /**
     * @param UserRequest $request
     * @param int $customerId
     * @return UserResource
     * @throws AuthorizationException
     */
    public function attachUserToCustomer(UserRequest $request,int $customerId) : UserResource
    {
        $customer = Customer::findOrFail($customerId);

        $this->authorize('attachUser', $customer);

        $data = $request->all();
        $childUser = $this->customerService->attachCustomerChild($data, $customer->user_id);

        return new UserResource($childUser);
    }
}
