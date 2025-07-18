<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrowseUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;

class BrowseUsersController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(
        BrowseUsersRequest $request,
    ) {

        $validated = $request->validated();

        $query = User::query();

        if (! empty($validated['first_name'])) {
            $query->whereHas('profile', function ($query) use ($validated) {
                $query->where('first_name', $validated['first_name']);
            });
        }

        if (! empty($validated['last_name'])) {
            $query->whereHas('profile', function ($query) use ($validated) {
                $query->where('last_name', $validated['last_name']);
            });
        }

        if (! empty($validated['email'])) {
            $query->where('email', $validated['email']);
        }

        $sortBy = $validated['sort'] ?? 'created_at';
        $order = $validated['order'] ?? 'asc';
        $perPage = $validated['per_page'] ?? 10;
        $page = $request->input('page') ?? 1;

        $users = $query->orderBy($sortBy, $order)
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return inertia('Admin/BrowseUsers', [
            'users' => UserResource::collection($users->items()),
            'paginator' => $users,
        ]);
    }
}
