<aside class="left-sidebar" data-sidebarbg="skin6">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar" data-sidebarbg="skin6">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="sidebar-item">
                    <a class="sidebar-link sidebar-link" href="{{ URL('/home') }}" aria-expanded="false">
                        <img src="{{ asset('img/logo/logo_telkom_akses.png') }}" alt="Logo" class="feather-icon">
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="list-divider"></li>

                @if (getAuthRole() == 3)
                    <li class="nav-small-cap"><span class="hide-menu">Workflow Management</span></li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('workflows.index') }}" aria-expanded="false">
                            <i data-feather="file-text" class="feather-icon"></i>
                            <span class="hide-menu">List Workflows</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('workflows.create') }}" aria-expanded="false">
                            <i data-feather="plus-circle" class="feather-icon"></i>
                            <span class="hide-menu">Create Workflow</span>
                        </a>
                    </li>

                    {{-- <li class="nav-small-cap"><span class="hide-menu">Buat Ticket</span></li>


                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('user/ticket/create') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Kirim Ticket Baru
                            </span>
                        </a>
                    </li>
                    <li class="list-divider"></li>
                    <li class="nav-small-cap"><span class="hide-menu">Tracking Status Ticket</span></li> --}}


                    {{-- <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('user/ticket/pending') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Pending
                            </span>
                        </a>
                    </li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('user/ticket/progress') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Progress
                            </span>
                        </a>
                    </li> --}}
                    {{-- <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('user/ticket/complete') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Complete
                            </span>
                        </a>
                    </li> --}}
                @endif


                @if (getAuthRole() == 1)
                    <li class="nav-small-cap"><span class="hide-menu">Karyawan & User</span></li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('karyawan/tambah') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Tambah User
                            </span>
                        </a>
                    </li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ URL('karyawan/manage') }}" aria-expanded="false">
                            <i data-feather="tag" class="feather-icon"></i>
                            <span class="hide-menu">Manage User
                            </span>
                        </a>
                    </li>

                    <li class="nav-small-cap"><span class="hide-menu">Anggaran</span></li>

                    <li class="sidebar-item active">
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

                    {{-- <li class="nav-small-cap"><span class="hide-menu">Kategori</span></li>

                        <li class="sidebar-item active">
                            <a class="sidebar-link" href="{{ URL('kategori/tambah') }}" aria-expanded="false">
                                <i data-feather="tag" class="feather-icon"></i>
                                <span class="hide-menu">Tambah Kategori
                                </span>
                            </a>
                        </li>

                        <li class="sidebar-item active">
                            <a class="sidebar-link" href="{{ URL('kategori/manage') }}" aria-expanded="false">
                                <i data-feather="tag" class="feather-icon"></i>
                                <span class="hide-menu">Manage Kategori
                                </span>
                            </a>
                        </li> --}}
                @endif


                <!-- Add this section to your sidebar.blade.php file inside the admin section -->

                @if (getAuthRole() == 1)
                    <li class="nav-small-cap"><span class="hide-menu">Master User</span></li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ route('admin.master-user.index') }}" aria-expanded="false">
                            <i data-feather="users" class="feather-icon"></i>
                            <span class="hide-menu">Manage Master User</span>
                        </a>
                    </li>

                    <li class="sidebar-item active">
                        <a class="sidebar-link" href="{{ route('admin.master-user.create') }}" aria-expanded="false">
                            <i data-feather="user-plus" class="feather-icon"></i>
                            <span class="hide-menu">Add Master User</span>
                        </a>
                    </li>
                @endif


            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
