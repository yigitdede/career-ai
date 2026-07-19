@php
    $notifications = trans('admin.notifications');
@endphp

@include('workspace.partials.header', [
    'workspaceApiHealth' => $apiHealth,
    'workspaceBrand' => __('admin.brand'),
    'workspaceLocaleRoute' => 'admin.locale',
    'workspaceMenuLabel' => __('admin.nav.open_menu'),
    'workspaceNotifications' => is_array($notifications) ? $notifications : [],
    'workspaceSidebarId' => 'admin-sidebar',
])
