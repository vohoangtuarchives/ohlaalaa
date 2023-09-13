@extends('layouts.load')
@section('content')

						<div class="content-area">
							<div class="add-product-content1">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
											@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{route('admin-affiliatelevel-package-update',$data->id)}}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Affiliate Bonus *</h4>
																<p class="sub-heading">(Bonus value in %)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="affiliate_bonus" placeholder="% bonus" required="" value="{{$data->affiliate_bonus}}">
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
