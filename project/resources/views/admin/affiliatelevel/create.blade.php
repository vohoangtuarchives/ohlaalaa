@extends('layouts.load')
@section('content')

						<div class="content-area">

							<div class="add-product-content1">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
											@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{route('admin-affiliatelevel-create')}}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Name *</h4>
																<p class="sub-heading">(Name of the level - Unique)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="name" placeholder="name" required="" value="">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Level *</h4>
																<p class="sub-heading">(Unique - Sorting - Ascending)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="level" placeholder="level" required="" value="">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">% Bonus *</h4>
																<p class="sub-heading">(Bonus value)</p>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="number" class="input-field" name="level_value" placeholder="% bonus" required="" value="">
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
