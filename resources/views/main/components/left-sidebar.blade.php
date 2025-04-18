<aside class="left-sidebar" data-sidebarbg="skin6">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar" data-sidebarbg="skin6">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                {{-- <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="{{ URL('/home') }}" aria-expanded="false">
                        <img src="{{ asset('img/logo/logo_telkom_akses.png') }}" alt="Logo" class="feather-icon">
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li> --}}
                {{-- <li class="list-divider"></li> --}}

                {{-- @if (in_array('Creator', getAuthRole()) || in_array('Admin', getAuthRole())) --}}
                    <li class="nav-small-cap"><span class="hide-menu">Workflow Management</span></li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('workflows.index') }}" aria-expanded="false">
                            <i data-feather="file-text" class="feather-icon"></i>
                            <span class="hide-menu">Pengajuan</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('workflows.create') }}" aria-expanded="false">
                            <i data-feather="plus-circle" class="feather-icon"></i>
                            <span class="hide-menu">Buat Pengajuan</span>
                        </a>
                    </li>
                {{-- @endif --}}

                @if (in_array('Admin', getAuthRole()))
                    <li class="nav-small-cap"><span class="hide-menu">Anggaran</span></li>

                    <li class="sidebar-item active d-none">
                        <a class="sidebar-link" href="{{ route('admin.jenis-anggaran.create') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Tambah Jenis Anggaran</span>
                        </a>
                    </li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ route('admin.jenis-anggaran.index') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Manage Jenis Anggaran</span>
                        </a>
                    </li>

                    <li class="nav-small-cap"><span class="hide-menu">Master User</span></li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ route('admin.master-user.index') }}" aria-expanded="false">
                            <i data-feather="users" class="feather-icon"></i>
                            <span class="hide-menu">Manage Master User</span>
                        </a>
                    </li>

                    <li class="sidebar-item active d-none">
                        <a class="sidebar-link" href="{{ route('admin.master-user.create') }}" aria-expanded="false">
                            <i data-feather="user-plus" class="feather-icon"></i>
                            <span class="hide-menu">Add Master User</span>
                        </a>
                    </li>

                    <li class="nav-small-cap"><span class="hide-menu">Approval Matrix</span></li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ route('admin.approval-matrix.index') }}" aria-expanded="false">
                            <i data-feather="layers" class="feather-icon"></i>
                            <span class="hide-menu">Manage Approval Matrix</span>
                        </a>
                    </li>

                    <li class="sidebar-item active d-none">
                        <a class="sidebar-link" href="{{ route('admin.approval-matrix.create') }}"
                            aria-expanded="false">
                            <i data-feather="plus-square" class="feather-icon"></i>
                            <span class="hide-menu">Add Approval Matrix</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
