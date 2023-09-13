@extends('layouts.load')
@section('content')

						<div class="content-area">
							<div class="add-product-content1">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
											@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{route('admin-packageconfig-update',$data->id)}}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Name *</h4>
																<p class="sub-heading">(Name of the package - Unique)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="name" placeholder="name" required="" value="{{$data->name}}">
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
														<input type="number" class="input-field" name="price" placeholder="price" required="" value="{{$data->price}}">
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
														<input type="number" class="input-field" name="bonus_sp" placeholder="sp bonus" required="" value="{{$data->bonus_sp}}">
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
														<input type="number" class="input-field" name="bonus_rp" placeholder="rp bonus" required="" value="{{$data->bonus_rp}}">
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
														<input type="number" class="input-field" name="rebate_bonus" placeholder="% rebate bonus" required="" value="{{$data->rebate_bonus}}">
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
                                                            <select class="input-field process select {{ $data->allow_buy == 1 ? 'drop-success' : 'drop-danger' }}" name="allow_buy">
                                                              <option data-val="1" value="1" {{ $data->allow_buy == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                                              <option data-val="0" value="0" {{ $data->allow_buy == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
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
														<input type="number" class="input-field" name="sort_index" placeholder="sort index" required="" value="{{$data->sort_index}}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Approval List *</h4>
																<p class="sub-heading">(Admin's emails will receive notification)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="approval_list" placeholder="approval list" required="" value="{{$data->approval_list}}">
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
                                                        <div class="text-editor">
                                                            <textarea class="nic-edit-p" name="content">{{$data->content}}</textarea>
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
                                                            <textarea class="nic-edit-p" name="tnc">{{$data->tnc}}</textarea>
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
														<input type="text" class="input-field" name="remarks" placeholder="remarks" value="{{$data->remarks}}">
													</div>
												</div>


												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">

														</div>
													</div>
													<div class="col-lg-7">
														<button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
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
