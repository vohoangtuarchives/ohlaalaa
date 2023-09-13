@extends('layouts.load')
@section('content')

						<div class="content-area">

							<div class="add-product-content1">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
											@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{route('admin-packageconfig-create')}}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Name *</h4>
																<p class="sub-heading">(Name of the package - Unique)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="name" placeholder="name" required="" value="">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Price *</h4>
																<p class="sub-heading">(Price of the package)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="price" placeholder="price" required="" value="0">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">SP Bonus *</h4>
																<p class="sub-heading">(Shopping Point bonus)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="bonus_sp" placeholder="sp bonus" required="" value="0">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">RP Bonus *</h4>
																<p class="sub-heading">(Reward Point bonus)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="bonus_rp" placeholder="rp bonus" required="" value="0">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Rebate Bonus *</h4>
																<p class="sub-heading">(Rebate bonus in %)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="rebate_bonus" placeholder="% rebate bonus" required="" value="0">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Allow to Buy *</h4>
																<p class="sub-heading">(Allow users buy this package)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<div class="action-list">
                                                            <select class="input-field process select drop-danger" name="allow_buy">
                                                              <option data-val="1" value="1">{{ __('Yes') }}</option>
                                                              <option data-val="0" value="0" selected>{{ __('No') }}</option>
                                                            </select>
                                                        </div>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Sort *</h4>
																<p class="sub-heading">(Unique - Sorting - Ascending)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="sort_index" placeholder="sort index" required="" value="0">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Content</h4>
																<p class="sub-heading">(Content of the package)</p>
														</div>
													</div>
													<div class="col-lg-7">
														{{-- <input type="text" class="input-field" name="content" placeholder="content" value=""> --}}
                                                        <div class="text-editor">
                                                            <textarea class="nic-edit-p" name="content"></textarea>
                                                        </div>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">T&C</h4>
																<p class="sub-heading">(Content of the Term and Commissioning)</p>
														</div>
													</div>
													<div class="col-lg-7">
                                                        <div class="text-editor">
                                                            <textarea class="nic-edit-p" name="tnc"></textarea>
                                                        </div>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Remarks</h4>
																<p class="sub-heading">(Remarks of the package)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="remarks" placeholder="remarks" value="">
													</div>
												</div>

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">

														</div>
													</div>
													<div class="col-lg-7">
														<button class="addProductSubmit-btn" type="submit">{{ __('Create') }}</button>
													</div>
												</div>
											</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

@endsection
