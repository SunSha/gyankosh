<!doctype html>
<?php
// header("Location: http://practice.quizus.co"); /* Redirect browser */
// exit();

include_once (dirname ( __FILE__ ) . "/lib/include_js_css.php");
include_once (dirname ( __FILE__ ) . "/lib/session_manager.php");
include_once (dirname ( __FILE__ ) . "/lib/site_config.php");
include_once (dirname ( __FILE__ ) . "/lib/utils.php");
include_once (dirname ( __FILE__ ) . "/database/config.php");
include_once (dirname ( __FILE__ ) . "/database/mcat_db.php");
include_once (dirname ( __FILE__ ) . "/3rd_party/recaptcha/recaptchalib.php");

$page_id = CSiteConfig::HF_INDEX_ID;
$login = CSessionManager::Get ( CSessionManager::BOOL_LOGIN );

$jsonCartItems = CSessionManager::Get ( CSessionManager::JSON_CART_ITEMS );
$aryCartItems = json_decode ( $jsonCartItems, TRUE );

$parsAry = parse_url ( CUtils::curPageURL () );
$qry = split ( "[=&]", $parsAry ["query"] );

/*
 * if($login) { CUtils::Redirect("core/dashboard.php"); } else
 * if(CSiteConfig::DEBUG_SITE == true && stristr($parsAry["host"],
 * strtolower(CConfig::SNC_SITE_NAME).".com") == FALSE) { if($qry[0] != "debug"
 * && $qry[1] != "639") { CUtils::Redirect(CSiteConfig::ROOT_URL, true); } }
 */

$login_name = $_GET ['ln'];
if (! empty ( $login_name )) {
	CSessionManager::Set ( CSessionManager::STR_LOGIN_NAME, $login_name );
} else if (! $login) {
	CSessionManager::UnsetSessVar ( CSessionManager::STR_LOGIN_NAME );
}

$objDB = new CMcatDB ();

$objIncludeJsCSS = new IncludeJSCSS ();

$disableCheckout = "";
$fSumCost = 0;
$fTax = CConfig::$BA_TAX_APPLIED_ARY ["Service Tax"] / 100;

$aryProductsInCart = array ();

function PopulateCart() {
	if (empty ( $GLOBALS ['aryCartItems'] ) || count($GLOBALS ['aryCartItems']) <= 1) {
		printf ( "<div class='row'>" );
		printf ( "<div class='col-lg-12 col-md-12 col-sm-12'>Cart is empty... </div>" );
		printf ( "</div>" );
		
		$GLOBALS ['disableCheckout'] = "disabled";
	} else {
		foreach ( $GLOBALS ['aryCartItems'] as $key => $cartItem ) {
			if (is_int ( $key )) {
				$aryProdDetails = $GLOBALS ['objDB']->GetPublishedProductDetails ( $cartItem ['id'], $cartItem ['type'] );
				// print_r($aryProdDetails);
				$aryProdInfo = json_decode ( $aryProdDetails ['published_info'], TRUE );
				
				array_push ( $GLOBALS ['aryProductsInCart'], array ("id" => $aryProdDetails ['product_id'], "name" => $aryProdDetails ['product_name'], "cost_inr" => $aryProdInfo ['cost'] ['inr'] ) );
				$GLOBALS ['fSumCost'] += $aryProdInfo ['cost'] ['inr'];
				
				printf ( "<div class='row'>" );
				printf ( "<div class='col-lg-8 col-md-8 col-sm-8'>%s</div>", $aryProdDetails ['product_name'] );
				/*
				printf ( "<div class='col-lg-2 col-md-2 col-sm-2'>" );
				printf ( "<div class='input-group input-group-sm spinner' id='spinner_%d'>",$aryProdDetails ['product_id']);
				printf ( "<input type='text' class='form-control' value='1'>" );
				printf ( "<div class='input-group-btn-vertical'>" );
				printf ( "<button class='btn btn-default' type='button'>" );
				printf ( "<i class='fa fa-caret-up'></i>" );
				printf ( "</button>" );
				printf ( "<button class='btn btn-default' type='button'>" );
				printf ( "<i class='fa fa-caret-down'></i>" );
				printf ( "</button>" );
				printf ( "</div>" );
				printf ( "</div>" );
				printf ( "</div>" );
				*/
				printf ( "<div class='col-lg-2 col-md-2 col-sm-2'>&#x20B9;%.2f</div>", $aryProdInfo ['cost'] ['inr'] );
				printf ( "<div class='col-lg-2 col-md-2 col-sm-2'><button class='btn btn-danger btn-xs' prod_id='%d' prod_type='%d' onclick='OnRemove(this);'>Remove&nbsp;&nbsp;<i class='fa fa-times-circle'></i></button></div>", $aryProdDetails ['product_id'], $aryProdDetails ['product_type']);
				printf ( "</div><br />" );
			}
		}
	}
}

function PopulateSummary() {
	if (empty ( $GLOBALS ['aryProductsInCart'] )) {
		printf ( "<div class='row'>" );
		printf ( "<div class='col-lg-12 col-md-12 col-sm-12'>Cart is empty...</div>" );
		printf ( "</div><br/>" );
	} else {
		foreach ( $GLOBALS ['aryProductsInCart'] as $product ) {
			printf ( "<div class='row'>" );
			printf ( "<div class='col-lg-7 col-md-7 col-sm-7'>%s</div>", $product ['name'] );
			printf ( "<div class='col-lg-5 col-md-5 col-sm-5'>&#x20B9;%.2f <small>(x<span id='items_%d'>%d</span>)</small></div>", $product ['cost_inr'], $product['id'], 1 );
			printf ( "</div><br/>" );
		}
	}
}
?>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="Generator" content="">
<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo(CConfig::SNC_SITE_NAME." - ".CConfig::SNC_PUNCH_LINE);?></title>
<?php
$objIncludeJsCSS->CommonIncludeCSS ( "" );
$objIncludeJsCSS->IncludeTVCSS ( "" );
$objIncludeJsCSS->IncludeMipcatCSS ( "" );
$objIncludeJsCSS->IncludeIconFontCSS ( "" );
$objIncludeJsCSS->CommonIncludeJS ( "" );
$objIncludeJsCSS->IncludeMetroDatepickerJS ( "" );
?>
		<!-- CSS -->

<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/responsive.css">
<link rel="icon" href="images/gini-favicon.png" type="image/png">

<style type="text/css">
.spinner {
	width: 50px;
}

.spinner input {
	text-align: center;
}

.input-group-btn-vertical {
	position: relative;
	white-space: nowrap;
	width: 1%;
	vertical-align: middle;
	display: table-cell;
}

.input-group-btn-vertical>.btn {
	display: block;
	float: none;
	width: 100%;
	max-width: 100%;
	padding: 7px;
	margin-left: -1px;
	position: relative;
	border-radius: 0;
}

.input-group-btn-vertical>.btn:first-child {
	border-top-right-radius: 4px;
}

.input-group-btn-vertical>.btn:last-child {
	margin-top: -2px;
	border-bottom-right-radius: 4px;
}

.input-group-btn-vertical i {
	position: absolute;
	top: 0;
	left: 4px;
}

.modal1 {
	display: none;
	position: fixed;
	z-index: 1000;
	top: 50%;
	left: 50%;
	height: 100%;
	width: 100%;
}
</style>

<!-- Js -->
<script src="js/vendor/modernizr-2.6.2.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/main.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/mipcat/utils.js"></script>
<script>
         new WOW(
            ).init();
        </script>
</head>
<body style="margin-top: 100px;">

	<?php
	include_once (dirname ( __FILE__ ) . "/lib/header.php");
	?>
	
	<!-- ************************* -->
	<div class="fuelux modal1">
		<div class="preloader">
			<i></i><i></i><i></i><i></i>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="panel-group col-lg-8 col-md-8 col-sm-8" id="accordion"
				role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingOne">
						<h4 class="panel-title">
							<!-- data-toggle="collapse" -->
							<span role="button" data-parent="#accordion" href="#collapseOne"
								aria-expanded="true" aria-controls="collapseOne"> Shopping Cart
							</span>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse in"
						role="tabpanel" aria-labelledby="collapseOne">
						<div class="panel-body">
							<?php
							PopulateCart ();
							$fTax = $fSumCost * $fTax;
							?>
							<div class="row">
								<div class="col-sm-offset-7 col-md-offset-7 col-lg-offset-7">
									<button class="btn btn-success" type="button" onclick="">
										Continue Shopping &nbsp;&nbsp; <i class="fa fa-shopping-cart"
											aria-hidden="true"></i>
									</button>
									<button class="btn btn-info" data-toggle="collapse"
										data-parent="#accordion" href="#collapsePersonalInfo"
										aria-expanded="false" aria-controls="collapsePersonalInfo" <?php echo($disableCheckout); ?>>
										Checkout &nbsp;&nbsp; <i class="fa fa-forward"
											aria-hidden="true"></i>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingPersonalInfo">
						<!-- data-toggle="collapse" -->
						<h4 class="panel-title">
							<span class="collapsed" role="button" data-parent="#accordion"
								href="#collapsePersonalInfo" aria-expanded="false"
								aria-controls="collapsePersonalInfo"> Your Personal Information
							</span>
						</h4>
					</div>
					<div id="collapsePersonalInfo" class="panel-collapse collapse"
						role="tabpanel" aria-labelledby="collapsePersonalInfo">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12">
									<div class="col-lg-4 col-md-4 col-sm-4"
										style="padding-right: 50px;">
										<div class="row">If you are an existing user, login now</div>
										<br />
										<div class="row">
											<div class="input-group">
												<span class="input-group-addon" id="basic-addon1">@</span> <input
													type="text" class="form-control" placeholder="Email-ID"
													aria-describedby="basic-addon1" />
											</div>
											<br />
											<div class="input-group">
												<span class="input-group-addon" id="basic-addon2"><i
													class="fa fa-ellipsis-h" aria-hidden="true"></i></span> <input
													type="password" class="form-control" placeholder="Password"
													aria-describedby="basic-addon2" />
											</div>
											<br />
											<div class="col-sm-offset-3 col-md-offset-3 col-lg-offset-3">
												<input class="btn btn-success col-lg-6 col-md-6 col-sm-6"
													type="submit" value="Submit" />
											</div>
											<br /> <br /> <br /> <a href="/login/forgot.php">Click here</a>
											to <a href="/login/forgot.php">recover your password</a>.
										</div>
									</div>
									<div class="col-lg-8 col-md-8 col-sm-8"
										style="padding-left: 0px; border-left: 1px solid #ccc;">
										<div class="row">
											<div
												class="col-lg-12 col-md-12 col-sm-12 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">If
												you are new user, please provide registration details</div>
										</div>
										<br />
										<div class="row">
											<div class="col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="col-lg-6 col-md-6 col-sm-6">
													<div class="input-group">
														<span class="input-group-addon" id="basic-addon5"><i
															class="fa fa-user" aria-hidden="true"></i> </span> <input
															type="text" class="form-control" placeholder="First Name"
															aria-describedby="basic-addon5">
													</div>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6">
													<div class="input-group">
														<span class="input-group-addon" id="basic-addon5"><i
															class="fa fa-users" aria-hidden="true"></i></span> <input
															type="text" class="form-control" placeholder="Last Name"
															aria-describedby="basic-addon5">
													</div>
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-11 col-md-11 col-sm-11 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="input-group">
													<span class="input-group-addon" id="basic-addon1">@</span>
													<input type="text" class="form-control"
														placeholder="Email-ID" aria-describedby="basic-addon1">
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-11 col-md-11 col-sm-11 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="input-group">
													<span class="input-group-addon" id="basic-addon2"><i
														class="fa fa-phone" aria-hidden="true"></i></span> <input
														type="text" class="form-control" placeholder="Phone"
														aria-describedby="basic-addon2">
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-11 col-md-11 col-sm-11 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="input-group">
													<span class="input-group-addon" id="basic-addon3"><i
														class="fa fa-ellipsis-h" aria-hidden="true"></i> </span> <input
														type="password" class="form-control"
														placeholder="Password" aria-describedby="basic-addon3">
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-11 col-md-11 col-sm-11 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="input-group">
													<span class="input-group-addon" id="basic-addon4"><i
														class="fa fa-minus" aria-hidden="true"></i> </span> <input
														type="password" class="form-control"
														placeholder="Confirm Password"
														aria-describedby="basic-addon4">
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div class="col-lg-1 col-md-1 col-sm-1 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<label style="padding-top:5px;" for="datepicker_dob" class="control-label">DOB</label>
											</div>
											<div
												class="col-lg-4 col-md-4 col-sm-4">
												<div class="metro">
													<div class="input-control text" id="datepicker_dob">
														<input id="dob" class="form-control" name="dob" type="text">
														<button class="btn-date" onclick="return false;"></button>
													</div>
												</div>
											</div>
											<div
												class="col-lg-6 col-md-6 col-sm-6">
												<div class="input-group">
													<span class="input-group-addon" id="city"><i
														class="fa fa-location-arrow" aria-hidden="true"></i> </span> <input
														type="text" class="form-control"
														placeholder="City" aria-describedby="city">
												</div>
											</div>
										</div>
										<div class="row">
											<div
												class="col-lg-5 col-md-5 col-sm-5 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="input-group">
													<span class="input-group-addon" id="state"><i
														class="fa fa-map-marker" aria-hidden="true"></i> </span> <input
														type="text" class="form-control"
														placeholder="State" aria-describedby="state">
												</div>
											</div>
											<div
												class="col-lg-6 col-md-6 col-sm-6">
												<div class="input-group">
													<span class="input-group-addon" id="country"><i
														class="fa fa-map" aria-hidden="true"></i> </span> <input
														type="text" class="form-control"
														placeholder="Country" aria-describedby="country">
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-11 col-md-11 col-sm-11 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<div class="checkbox">
													<label> <input class="btn btn-success" type="checkbox"> I
														agree to <b><?php echo(CConfig::SNC_SITE_NAME);?>'s</b> <a
														href="/terms/terms-of-service.php">terms of service</a>
														&amp; <a href="/terms/privacy_policy.php">privacy policy</a>.
													</label>
												</div>
											</div>
										</div>
										<br />
										<div class="row">
											<div
												class="col-lg-4 col-md-4 col-sm-4 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<button class="btn btn-info" data-toggle="collapse"
													data-parent="#accordion" href="#collapseOne"
													aria-expanded="false" aria-controls="collapseOne">
													<i class="fa fa-backward"
														aria-hidden="true"></i>&nbsp;&nbsp; Back to Cart
												</button>
											</div>
											<div
												class="col-lg-6 col-md-6 col-sm-6 col-sm-offset-1 col-md-offset-1 col-lg-offset-1">
												<input class="btn btn-success col-lg-6 col-md-6 col-sm-6"
													type="submit" value="Register Now">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingThree">
						<h4 class="panel-title">
							<!-- data-toggle="collapse" -->
							<span class="collapsed" role="button" data-parent="#accordion"
								href="#collapseThree" aria-expanded="false"
								aria-controls="collapseThree"> Payment details </span>
						</h4>
					</div>
					<div id="collapseThree" class="panel-collapse collapse"
						role="tabpanel" aria-labelledby="headingThree">
						<div class="panel-body">Anim pariatur cliche reprehenderit, enim
							eiusmod high life accusamus terry richardson ad squid. 3 wolf
							moon officia aute, non cupidatat skateboard dolor brunch. Food
							truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor,
							sunt aliqua put a bird on it squid single-origin coffee nulla
							assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft
							beer labore wes anderson cred nesciunt sapiente ea proident. Ad
							vegan excepteur butcher vice lomo. Leggings occaecat craft beer
							farm-to-table, raw denim aesthetic synth nesciunt you probably
							haven't heard of them accusamus labore sustainable VHS.</div>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-3 col-sm-3">
				<div class="panel panel-default">
					<div class="panel-heading col-lg-12 col-md-12 col-sm-12">
						<h3 class="panel-title">Purchase Summary</h3>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12">
								Your purchase summary is as follows,<br /> <br />
							</div>
						</div>
						<?php PopulateSummary();?>
						<hr />
						<div class="row">
							<div class="col-lg-8 col-md-8 col-sm-8">Applicable Taxes</div>
							<div class="col-lg-4 col-md-4 col-sm-4">&#x20B9;<?php printf("%.2f", $fTax);?></div>
						</div>
						<hr />
						<div class="row">
							<div class="col-lg-8 col-md-8 col-sm-8">Total</div>
							<div class="col-lg-4 col-md-4 col-sm-4">&#x20B9;<?php printf("%.2f", $fSumCost + $fTax);?></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<p class="text-center"><?php include ("lib/footer.php"); ?></p>
	</div>

	<!-- ************************* -->
	<script type="text/javascript">
		$( document ).ready(function() {
			var objDate = new Date();
			objDate.setFullYear(objDate.getFullYear() - 12);
			$("#datepicker_dob").datepicker({
				format: "dd mmmm, yyyy",
				date : objDate
			});
		});

		function OnRemove(obj)
		{
			//alert(document.location.href);
			//alert($(obj).attr("prod_id")+ " : " + $(obj).attr("prod_type"));

			$.ajax({
				url: '<?php echo(CSiteConfig::ROOT_URL);?>/core/index/ajax/ajax_remove_from_cart.php',
				type: 'POST',
				data: {'product_id' : $(obj).attr("prod_id"), 
					'product_type' : $(obj).attr("prod_type")},
				dataType: 'json',
				async: false
			});

			$(".modal1").show();
			window.location = window.location;
		}
		
		function stay_connected()
		{
			return objUtils.stay_connected();
		}
		
		$(".icon-home").addClass("glyphicon");
		$(".icon-home").addClass("glyphicon-home");
	
		$(".icon-user").addClass("glyphicon");
		$(".icon-user").addClass("glyphicon-user");

		(function ($) {
			<?php
			if(FALSE)
			{
				foreach ($aryProductsInCart as $product)
				{
				  printf("$('#spinner_%d .btn:first-of-type').on('click', function() {", $product['id']);
				  	printf("$('#spinner_%d input').val( parseInt($('#spinner_%d input').val(), 10) + 1);", $product['id'], $product['id']);
				    printf("$('#items_%d').text($('#spinner_%d input').val());", $product['id'], $product['id']);
				  printf("});");
				  printf("$('#spinner_%d .btn:last-of-type').on('click', function() {", $product['id']);
				  	printf("var iItems = $('#spinner_%d input').val();", $product['id']);
				  	printf("if(iItems > 1)");
				  	printf("{");
				    printf("$('#spinner_%d input').val( parseInt($('#spinner_%d input').val(), 10) - 1);", $product['id'], $product['id']);
				    printf("$('#items_%d').text($('#spinner_%d input').val()); ", $product['id'], $product['id']);
				    printf("}");
				  printf("});");
				}
			}
			?>
			})(jQuery);

		function OnCheckout()
		{
			$("#headingOne").collapse('hide');
			$("#headingPersonalInfo").collapse('show');
		}
	</script>
</body>
</html>
