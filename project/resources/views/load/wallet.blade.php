									@if(Auth::check() && Auth::user())
										<div class="dropdownmenu-wrapper">
                                            <div class="membership-title">
                                                <p class="sub-heading">
                                                    {{ Auth::user()->email }}
                                                </p>
                                                <h3>{{ Auth::user()->rank_name() }}
                                                    <p class="sub-heading">
													    {{ isset(Auth::user()->ranking_end_date) ? Auth::user()->ranking_end_date : '' }}
												    </p>

                                                </h3>

                                            </div>

                                            <div class="dropdown-cart-total" style="color: red">
                                                    <span>Reward Point</span>

                                                    <span class="cart-total-price">
                                                        <span class="cart-total">{{ number_format(Auth::user()->reward_point) }}
                                                        </span>
                                                    </span>
                                            </div>

                                            <div class="dropdown-cart-total" style="color: green">
                                                <span>Shopping Point</span>

                                                <span class="cart-total-price">
                                                    <span class="cart-total">{{ number_format(Auth::user()->shopping_point) }}
                                                    </span>
                                                </span>
                                            </div>
										</div>
									@else
									<p class="mt-1 pl-3 text-left"></p>
									@endif
