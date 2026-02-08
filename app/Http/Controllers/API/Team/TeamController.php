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
        $teams = $request->user()->teams()
            ->where('status', '!=', TeamStatus::DELETED->value)
            ->get();

        return $this->success(TeamResource::collection($teams));
    }

    public function show(string $teamId): JsonResponse
    {
        $teamExists = Team::where('public_id', $teamId)
            ->orWhere('id', $teamId)
            ->exists();

        if (! $teamExists) {
            return $this->notFound('Team not found');
        }

        $team = auth()->user()->teams()
            ->where(function ($query) use ($teamId): void {
                $query->where('teams.public_id', $teamId)
                    ->orWhere('teams.id', $teamId);
            })
            ->first();

        if (! $team) {
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
            'owner_id' => $request->user()->id,
        ]);

        $team->users()->attach($request->user()->id, ['is_admin' => true]);

        return $this->created(new TeamResource($team), 'Team created successfully');
    }

    public function update(UpdateTeamRequest $request, string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)
            ->orWhere('id', $teamId)
            ->firstOrFail();

        if (! $team->isAdmin(auth()->user())) {
            return $this->forbidden('Only team admins can update team settings');
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

        if ($data !== []) {
            $team->update($data);
        }

        return $this->success(new TeamResource($team->fresh()), 'Team updated successfully');
    }

    public function destroy(string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)
            ->orWhere('id', $teamId)
            ->firstOrFail();

        if (! $team->isAdmin(auth()->user())) {
            return $this->forbidden('Only team admins can delete a team');
        }

        if ($team->is_personal) {
            return $this->error('Your default team cannot be deleted.', 422);
        }

        if (auth()->user()->currentTeam()?->id === $team->id) {
            return $this->error('You cannot delete your current active team. Switch to another team first.', 422);
        }

        // Delete associated projects and detach members
        $team->projects()->each(fn ($project) => $project->delete());
        $team->users()->detach();

        $team->update(['status' => TeamStatus::DELETED->value]);

        return $this->success(null, 'Team deleted successfully');
    }
}
