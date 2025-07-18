<?php

namespace App\Enums;

enum Permission: string
{
    // User Permissions
    case ViewAnyUser = 'view-any-user';
    case ViewUser = 'view-user';
    case CreateUser = 'create-user';
    case UpdateUser = 'update-user';
    case DeleteUser = 'delete-user';

    // Team Permissions
    case ViewAnyTeam = 'view-any-team';
    case ViewTeam = 'view-team';
    case CreateTeam = 'create-team';
    case UpdateTeam = 'update-team';
    case DeleteTeam = 'delete-team';

    // Project Permissions
    case ViewAnyProject = 'view-any-project';
    case ViewProject = 'view-project';
    case CreateProject = 'create-project';
    case UpdateProject = 'update-project';
    case DeleteProject = 'delete-project';

    public static function getInstances(): array
    {
        return [
            // User Permissions
            self::ViewAnyUser,
            self::ViewUser,
            self::CreateUser,
            self::UpdateUser,
            self::DeleteUser,

            // Team Permissions
            self::ViewAnyTeam,
            self::ViewTeam,
            self::CreateTeam,
            self::UpdateTeam,
            self::DeleteTeam,

            // Project Permissions
            self::ViewAnyProject,
            self::ViewProject,
            self::CreateProject,
            self::UpdateProject,
            self::DeleteProject,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            // User Permissions
            self::ViewAnyUser => 'View Any User',
            self::ViewUser => 'View User',
            self::CreateUser => 'Create User',
            self::UpdateUser => 'Update User',
            self::DeleteUser => 'Delete User',

            // Team Permissions
            self::ViewAnyTeam => 'View Any Team',
            self::ViewTeam => 'View Team',
            self::CreateTeam => 'Create Team',
            self::UpdateTeam => 'Update Team',
            self::DeleteTeam => 'Delete Team',

            // Project Permissions
            self::ViewAnyProject => 'View Any Project',
            self::ViewProject => 'View Project',
            self::CreateProject => 'Create Project',
            self::UpdateProject => 'Update Project',
            self::DeleteProject => 'Delete Project',
        };
    }
}
