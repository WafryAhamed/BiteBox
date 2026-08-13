<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\AddressService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AddressService $addressService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->getForUser($request->user());

        return $this->success(
            AddressResource::collection($addresses),
            'Addresses retrieved successfully'
        );
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create($request->user(), $request->validated());

        return $this->created(
            new AddressResource($address),
            'Address created successfully'
        );
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Unauthorized to update this address', 403);
        }

        $address = $this->addressService->update($address, $request->validated());

        return $this->success(
            new AddressResource($address),
            'Address updated successfully'
        );
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Unauthorized to delete this address', 403);
        }

        $this->addressService->delete($address);

        return $this->noContent('Address deleted successfully');
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        $address = $this->addressService->setDefault($address);

        return $this->success(
            new AddressResource($address),
            'Default address set successfully'
        );
    }
}
