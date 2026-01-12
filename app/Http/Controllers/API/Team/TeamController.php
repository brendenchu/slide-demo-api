<?php

namespace App\Http\Controllers\API\Team;

use App\Enums\Account\TeamStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Team\CreateTeamRequest;
use App\Http\Requests\API\Team\UpdateTeamRequest;
use App\Http\Resources\API\Account\TeamResource;
use App\Models\Account\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        // Get teams that the authenticated user is a member of
        $teams = $request->user()->teams()->get();

        return $this->success(TeamResource::collection($teams));
    }

    public function show(string $id): JsonResponse
    {
        $team = Team::where('public_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Authorization check - user must be a member of the team
        if (! auth()->user()->teams->contains($team)) {
            return $this->forbidden('You do not have access to this team');
        }

        return $this->success(new TeamResource($team));
    }

    public function store(CreateTeamRequest $request): JsonResponse
    {
        $statusValue = match ($request->input('status', 'active')) {
            'active' => TeamStatus::ACTIVE->value,
            'inactive' => TeamStatus::INACTIVE->value,
            default => TeamStatus::ACTIVE->value,
        };

        $team = Team::create([
            'label' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $statusValue,
        ]);

        // Attach the authenticated user to the team
        $team->users()->attach($request->user()->id);

        return $this->created(new TeamResource($team), 'Team created successfully');
    }

    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        $team = Team::where('public_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Authorization check - user must be a member of the team
        if (! auth()->user()->teams->contains($team)) {
            return $this->forbidden('You do not have access to this team');
        }

        $data = [];

        if ($request->filled('name')) {
            $data['label'] = $request->input('name');
        }

        if ($request->has('description')) {
            $data['description'] = $request->input('description');
        }

        if ($request->filled('status')) {
            $data['status'] = match ($request->input('status')) {
                'active' => TeamStatus::ACTIVE->value,
                'inactive' => TeamStatus::INACTIVE->value,
                default => $team->status,
            };
        }

        if (! empty($data)) {
            $team->update($data);
        }

        return $this->success(new TeamResource($team->fresh()), 'Team updated successfully');
    }
}
