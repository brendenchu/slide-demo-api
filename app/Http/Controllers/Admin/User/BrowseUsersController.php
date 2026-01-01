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

        // Whitelist allowed sort columns to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $sortBy = in_array($validated['sort'] ?? 'created_at', $allowedSortColumns)
            ? $validated['sort'] ?? 'created_at'
            : 'created_at';

        $order = in_array(strtolower($validated['order'] ?? 'asc'), ['asc', 'desc'])
            ? $validated['order']
            : 'asc';

        $perPage = $validated['per_page'] ?? 10;
        $page = $request->input('page') ?? 1;

        $users = User::query()
            ->when(! empty($validated['first_name']), function ($query) use ($validated): void {
                $query->whereHas('profile', fn ($q) => $q->where('first_name', $validated['first_name']));
            })
            ->when(! empty($validated['last_name']), function ($query) use ($validated): void {
                $query->whereHas('profile', fn ($q) => $q->where('last_name', $validated['last_name']));
            })
            ->when(! empty($validated['email']), function ($query) use ($validated): void {
                $query->where('email', $validated['email']);
            })
            ->with(['profile', 'teams', 'roles', 'permissions'])
            ->orderBy($sortBy, $order)
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return inertia('Admin/BrowseUsers', [
            'users' => UserResource::collection($users->items()),
            'paginator' => $users,
        ]);
    }
}
