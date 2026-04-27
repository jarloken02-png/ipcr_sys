<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');
        $deanRoleId = DB::table('roles')->where('name', 'dean')->value('id');

        $opcrPermissionIds = DB::table('permissions')
            ->whereIn('key', [
                'dean.opcr.templates',
                'dean.opcr.submissions',
                'dean.opcr.saved-copies',
            ])
            ->pluck('id')
            ->all();

        if (!empty($facultyRoleId) && !empty($opcrPermissionIds)) {
            DB::table('role_permissions')
                ->where('role_id', $facultyRoleId)
                ->whereIn('permission_id', $opcrPermissionIds)
                ->delete();
        }

        // Ensure dean retains OPCR permissions even if records were manually removed.
        if (!empty($deanRoleId) && !empty($opcrPermissionIds)) {
            $existingPermissionIds = DB::table('role_permissions')
                ->where('role_id', $deanRoleId)
                ->whereIn('permission_id', $opcrPermissionIds)
                ->pluck('permission_id')
                ->all();

            $missingPermissionIds = array_diff($opcrPermissionIds, $existingPermissionIds);

            if (!empty($missingPermissionIds)) {
                $rows = [];
                foreach ($missingPermissionIds as $permissionId) {
                    $rows[] = [
                        'role_id' => $deanRoleId,
                        'permission_id' => $permissionId,
                    ];
                }

                DB::table('role_permissions')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        $facultyRoleId = DB::table('roles')->where('name', 'faculty')->value('id');

        $opcrPermissionIds = DB::table('permissions')
            ->whereIn('key', [
                'dean.opcr.templates',
                'dean.opcr.submissions',
                'dean.opcr.saved-copies',
            ])
            ->pluck('id')
            ->all();

        if (empty($facultyRoleId) || empty($opcrPermissionIds)) {
            return;
        }

        $existingPermissionIds = DB::table('role_permissions')
            ->where('role_id', $facultyRoleId)
            ->whereIn('permission_id', $opcrPermissionIds)
            ->pluck('permission_id')
            ->all();

        $missingPermissionIds = array_diff($opcrPermissionIds, $existingPermissionIds);

        if (empty($missingPermissionIds)) {
            return;
        }

        $rows = [];
        foreach ($missingPermissionIds as $permissionId) {
            $rows[] = [
                'role_id' => $facultyRoleId,
                'permission_id' => $permissionId,
            ];
        }

        DB::table('role_permissions')->insert($rows);
    }
};
