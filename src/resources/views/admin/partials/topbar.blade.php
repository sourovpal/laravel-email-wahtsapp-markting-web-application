<header>
    <div class="header_sub_content">
        <div class="row align--center">
            <div class="col-6">
                <div class="d--flex">
                    <div class="company_name pe-2">
                        <i onclick="showSideBar()" class="fs--9 text--dark las la-bars me-2 pointer show-bar-icon"></i>
                        <i id="openFullScreen" onclick="openFull()" class="las la-arrows-alt fs--9 text--dark ms-3"></i>
                        <i id="closeFullScreen" onclick="closeFull()" class="las la-compress fs--9 text--dark ms-3"></i>
                    </div>
                    <div>
                        <a class="d--flex align--center text--light" href="{{route('admin.general.setting.cache.clear')}}">
                            <i class="las la-sync fs--9 text--dark ms-3"></i><span> {{ translate('Refresh')}}</span> 
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="profile_notification">
                    <ul>
                        <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                              <i class="flag-icon flag-icon-{{session('flag')}} flag-icon-squared rounded-circle me-1 fs-2"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @foreach($languages as $language)
                                <li>
                                    <a class="dropdown-item @if(session('lang') == $language->code) selected  @endif" href="javascript:void(0);" data-language="{{$language->code}}" onclick="changeLang('{{$language->id}}')">
                                        <i class="flag-icon flag-icon-{{$language->flag}} flag-icon-squared rounded-circle fs-4 me-1"></i>
                                        <span class="align-middle">{{$language->name}}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="drop-down">
                            <div class="pointer dropdown-toggle d--flex align--center" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="profile-nav-img"><img src="{{showImage(filePath()['profile']['admin']['path'].'/'.auth()->guard('admin')->user()->image)}}" alt="Image" class="rounded-circle"></span>
                                <p class="ms-1 hide_small admin--profile--notification">{{auth()->guard('admin')->user()->name}}</p>
                            </div>
                            <ul class="dropdown-menu drop_down_width" aria-labelledby="dropdownMenuButton1">
                                <li>
                                    <a class="dropdown-item" href="{{route('admin.profile')}}"><i class="me-1 las la-cog"></i> {{ translate('Profile Setting')}}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{route('admin.password')}}"><i class="me-1 las la-lock"></i> {{ translate('Password Update')}}</a>
                                </li> 
                                <li>
                                    <a class="dropdown-item" href="{{route('admin.logout')}}"><i class="me-1 las la-sign-in-alt"></i> {{ translate('Logout')}}</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
