<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Admin\CreateUserRequest;
use App\Http\Requests\API\Admin\UpdateUserRequest;
use App\Http\Resources\API\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        // Check if user has permission
        if (! $request->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        $query = User::query()->with('roles');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        $users = $query->latest('created_at')->get();

        return $this->success(UserResource::collection($users));
    }

    public function show(string $id): JsonResponse
    {
        // Check if user has permission
        if (! request()->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        $user = User::with('roles')->findOrFail($id);

        return $this->success(new UserResource($user));
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        // Check if user has permission
        if (! $request->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Assign role
        $user->assignRole($request->input('role'));

        return $this->created(new UserResource($user->load('roles')), 'User created successfully');
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        // Check if user has permission
        if (! $request->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        $user = User::findOrFail($id);

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->input('name');
        }

        if ($request->filled('email')) {
            $data['email'] = $request->input('email');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if (! empty($data)) {
            $user->update($data);
        }

        // Update role if provided
        if ($request->filled('role')) {
            $user->syncRoles([$request->input('role')]);
        }

        return $this->success(new UserResource($user->fresh('roles')), 'User updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        // Check if user has permission
        if (! request()->user()->hasRole(['admin', 'super-admin'])) {
            return $this->forbidden('You do not have permission to access this resource');
        }

        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return $this->error('You cannot delete your own account', 400);
        }

        $user->delete();

        return $this->noContent();
    }
}
