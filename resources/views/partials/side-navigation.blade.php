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
        <li class="{{Request::is('workflow') || Request::is('workflow/*') ? 'active' : ''}}"><a href="/workflow" class="has-tip tip-right touchbutton" data-options="disable_for_touch:true" aria-haspopup="true" title="Workflows"><i class="icon-tiks-report"></i><span><b></b>Workflows</span></a></li>
    @endif
@endif