        <div class="col-lg-3">
          <div class="user-profile-info-area">
            <ul class="links">
                @php

                  if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                  {
                    $link = "https";
                  }
                  else
                  {
                    $link = "http";

                    // Here append the common URL characters.
                    $link .= "://";

                    // Append the host(domain name, ip) to the URL.
                    $link .= $_SERVER['HTTP_HOST'];

                    // Append the requested resource location to the URL
                    $link .= $_SERVER['REQUEST_URI'];
                  }

                @endphp
              <li class="{{ $link == route('user-dashboard') ? 'active':'' }}">
                <a href="{{ route('user-dashboard') }}">
                  {{ $langg->lang200 }}
                </a>
              </li>

              @if(Auth::user()->IsVendor())
                <li>
                  <a href="{{ route('vendor-dashboard') }}">
                    {{ $langg->lang230 }}
                  </a>
                </li>
              @endif

              <li class="{{ $link == route('user-orders') ? 'active':'' }}">
                <a href="{{ route('user-orders') }}">
                  {{ $langg->lang201 }}
                </a>
              </li>

                <li class="{{ $link == route('user-affilate-code') ? 'active':'' }}">
                    <a href="{{ route('user-affilate-code') }}">{{ $langg->lang202 }}</a>
                </li>

                {{-- <li class="d-none {{ $link == route('user-affilate-members') ? 'active':'' }}">
                    <a href="{{ route('user-affilate-members') }}">Affiliate Members Directly</a>
                </li> --}}
                <li class="{{ $link == route('user-affilate-tree') ? 'active':'' }}">
                    <a href="{{ route('user-affilate-tree') }}">{{ $langg->lang824 }}</a>
                </li>

                {{-- <li class="{{ $link == route('user-sending-sp') ? 'active':'' }}">
                    <a href="{{ route('user-sending-sp') }}">{{ $langg->lang825 }}</a>
                </li> --}}

              @if($gs->is_affilate == 1)

                {{-- <li class="{{ $link == route('user-affilate-code') ? 'active':'' }}">
                    <a href="{{ route('user-affilate-code') }}">{{ $langg->lang202 }}</a>
                </li> --}}

                {{-- <li class="{{ $link == route('user-wwt-index') ? 'active':'' }}">
                    <a href="{{route('user-wwt-index')}}">{{ $langg->lang203 }}</a>
                </li> --}}

              @endif


              <li class="{{ $link == route('user-order-track') ? 'active':'' }}">
                  <a href="{{route('user-order-track')}}">{{ $langg->lang772 }}</a>
              </li>

              <li class="{{ $link == route('user-favorites') ? 'active':'' }}">
                  <a href="{{route('user-favorites')}}">{{ $langg->lang231 }}</a>
              </li>

              <li class="{{ $link == route('user-messages') ? 'active':'' }}">
                  <a href="{{route('user-messages')}}">{{ $langg->lang232 }}</a>
              </li>

              {{-- <li class="{{ $link == route('user-message-index') ? 'active':'' }}">
                  <a href="{{route('user-message-index')}}">{{ $langg->lang204 }}XXXXXXXX</a>
              </li>

              <li class="{{ $link == route('user-dmessage-index') ? 'active':'' }}">
                  <a href="{{route('user-dmessage-index')}}">{{ $langg->lang250 }}</a>
              </li> --}}

              <li class="{{ $link == route('user-profile') ? 'active':'' }}">
                <a href="{{ route('user-profile') }}">
                  {{ $langg->lang205 }}
                </a>
              </li>

              <li class="{{ $link == route('user-point-logs-index') ? 'active':'' }}">
                <a href="{{ route('user-point-logs-index') }}">
                    {{ $langg->lang826 }}
                </a>
              </li>

            <?php
                if (!isset($user->id) || $user->id == '') {
                    $user = Auth::user();
                }
            ?>

            @if (Session::has('kolbonus') && Session::get('kolbonus') == $user->id)
                <li class="{{ $link == route('user-order-kol-bonus') ? 'active':'' }}">
                    <a href="{{ route('user-order-kol-bonus') }}">
                    {{ $langg->lang918 }}
                    </a>
                </li>
            @endif

              @if ($user->can_transfer_point)
                <li class="{{ $link == route('user-transfer-point') ? 'active':'' }}">
                  <a href="{{ route('user-transfer-point') }}">
                    Chuyển Điểm
                  </a>
                </li>
              @endif
              <li class="{{ $link == route('user-reset') ? 'active':'' }}">
                <a href="{{ route('user-reset') }}">
                 {{ $langg->lang206 }}
                </a>
              </li>

              <li>
                <a href="{{ route('user-logout') }}">
                  {{ $langg->lang207 }}
                </a>
              </li>

            </ul>
          </div>
          @if($gs->reg_vendor == 1)
            <div class="row mt-4">
              <div class="col-lg-12 text-center">
                <a href="{{ route('user-package') }}" class="mybtn1 lg">
                  <i class="fas fa-dollar-sign"></i> {{ Auth::user()->is_vendor == 1 ? $langg->lang233 : (Auth::user()->is_vendor == 0 ? $langg->lang233 : $langg->lang237) }}
                </a>
              </div>
            </div>
          @endif
          @if(Auth::user()->member_package_submitted()->count() == 0)
          <div class="row mt-4">
            <div class="col-lg-12 text-center">
              <a href="{{ route('user-member-package') }}" class="mybtn1 lg">
                <i class="fas fa-dollar-sign"></i> {{ $langg->lang827 }}
              </a>
            </div>
          </div>
          @endif
        </div>
