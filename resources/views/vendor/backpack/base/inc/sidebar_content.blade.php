<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ url('/') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
@if(backpack_auth()->check())
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('dataset') }}'><i class='nav-icon la la-database'></i> Dataset</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('dmodel') }}'><i class='nav-icon la la-file-text-o'></i> Model</a></li>
@endif
<li class='nav-item'><a class='nav-link' href="{{ url('klasifikasi') }}"><i class='nav-icon la la-gears'></i> Klasifikasi</a></li>
<li class='nav-item'><a class='nav-link' href='{{ url('tweet') }}'><i class='nav-icon la la-twitter'></i> Tweets</a></li>