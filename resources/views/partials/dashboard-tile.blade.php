{{-- Workflows Dashboard Tile --}}
@if(config('workflow.show_dashboard_tile', true))
    @php
        $allowedRoles = config('workflow.dashboard_tile_roles', ['super_admin']);
        $userHasRole = false;
        foreach ($allowedRoles as $role) {
            if (Entrust::hasRole($role)) {
                $userHasRole = true;
                break;
            }
        }
    @endphp
    @if($userHasRole)
        <a href="/workflow" class="touchbutton">
            <div class="admin-tile">
                <p><i class="workflow-icon fi icon-tiks-report"></i></p>
                <p class="tile-label workflows-label">Workflows</p>
            </div>
        </a>
    @endif
@endif

