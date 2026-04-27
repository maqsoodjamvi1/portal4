<?php include("header.php"); ?> 
<div class="container-fluid top_banner">
	<div class="row">
    <div class="col-md-12" style="padding:0px;"> 
      <div class="container"> 
        <div class="row"> 
          <div class="col-md-5 col-md-offset-7"> 
            <div style="padding-bottom:20px;padding-left:15px;padding-right:15px;">
			<div><h2 style="font-weight:bold;">Best Online School Management System</h2></div>
			<span class="free_txt" style="float:left;padding-bottom:10px;">Enjoy Demo </span><span class="free_txt_sub" style="float:right;padding-top:20px;">With  Sample Records</span>
			</div>
            <form  name="frm_new" id="frm_new" action="demo_process.php" method="post">
             <input type="hidden" name="ACT" value="NEW_REGISTER">
			  <input type="hidden" name="plan" value="STARTER">
              <div class="form-group" style="margin-bottom:5px;"> 
                <div class="col-xs-12" style="margin-bottom:5px;"><input name="school_name" type="text" class="form-control txt_reg" id="school_name" size="40" placeholder="Your School Name" ></div>
              </div>
			  <div class="form-group" style="margin-bottom:5px;">
			  	<div class="col-xs-6" style="margin-bottom:5px;"><input name="first_name" type="text" class="form-control txt_reg" id="first_name" size="40" placeholder="First Name" ></div>
				<div class="col-xs-6" style="margin-bottom:5px;"><input name="last_name" type="text" class="form-control txt_reg" id="last_name" size="40" placeholder="Last Name" ></div>
              </div>
              <div class="form-group" style="margin-bottom:5px;"> 
                <div class="col-xs-12" style="margin-bottom:5px;"><input name="phone_no" type="text" class="form-control txt_reg" id="phone_no" size="40" placeholder="Your Valid Phone Number" ></div>
              </div>
			  <div class="form-group" style="margin-bottom:5px;"> 
               <div class="col-xs-12" style="margin-bottom:5px;">
               	<input type="email" name="email" class="form-control" placeholder="Email"  maxlength="254" autocomplete="off" />
               </div>
              </div>
              <div class="form-group" style="margin-bottom:5px;"> 
                <div class="col-xs-12" style="margin-bottom:5px;">
                	<input name="password" type="password" class="form-control txt_reg" id="password" size="40" placeholder="Secure Password (8-15 characters)" >
                </div>
              </div>
			  <div class="form-group"> 
                <div class="col-xs-12">
                	 <!--  <button type="submit" class="btn btn-primary">Save</button> -->
                	<input type="submit" name="sub_btn" id="sub_btn" value="TRY FREE" class="btn btn-success form-control" style="height:50px;font-size:16px;"></div>
                <p class="alert alert-danger" style="display:none;" id="msgs"></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
	</div>
</div>
<div class="container">
	<div class="row" style="margin-bottom:20px;margin-top:20px;">
		<div class="col-md-12 text-center"><img src="imgs/200.jpg"></div>
	</div>
</div>
<a name="features"></a>
<div class="container">
	<div class="row" style="margin-bottom:40px;">
    <div class="col-md-12" style="margin-top:50px;"> 
	  <h1 class="text-center">Easy, Effective and <strong>Powerful Features</strong></h1>
	</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/001.png"></div>
				<div class="box_txt">MANAGE TEACHERS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/002.png"></div>
				<div class="box_txt">MANAGE STUDENTS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/003.png"></div>
				<div class="box_txt">ATTENDANCE</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/004.png"></div>
				<div class="box_txt">FEE COLLECTION</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/005.png"></div>
				<div class="box_txt">PROMOTE STUDENTS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/006.png"></div>
				<div class="box_txt">EXAM MANAGEMNET</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/007.png"></div>
				<div class="box_txt">DATESHEET</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/008.png"></div>
				<div class="box_txt">RESULT CARD</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/income.png"></div>
				<div class="box_txt">MANAGE ACADEMIC</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/expense.png"></div>
				<div class="box_txt">MANAGE TIME TABLE</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/014.png"></div>
				<div class="box_txt">MANAGE SCHOOL TIMINGS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/school-bus.png"></div>
				<div class="box_txt">MANAGE STUDENT COMPLAINTS</div>
			</div>
		</div>
	</div>
    <div class="row">
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/009.png"></div>
				<div class="box_txt">CLASS / SECTION</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/010.png"></div>
				<div class="box_txt">SUBJECTS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/011.png"></div>
				<div class="box_txt">NOTICE BOARD</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/012.png"></div>
				<div class="box_txt">REPORTING</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/sms.png"></div>
				<div class="box_txt">TEACHER PARENT CHAT</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/013.png"></div>
				<div class="box_txt">MULTI CAMPUSES</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/014.png"></div>
				<div class="box_txt">MULTI USERS</div>
			</div>
		</div>
		<div class="col-md-3 col-xs-6">
			<div class="sub_box">
				<div class="box_icon"><img src="png/014.png"></div>
				<div class="box_txt">MOBILE PORTAL</div>
			</div>
		</div>
	</div>
</div>
<a name="pricing"></a>
<div class="container-fluid" style="background: #eaeaea;margin-top:50px;">
	<div class="container">
	<h1 class="text-center" style="margin-top:60px;margin-bottom:10px;"><strong>Choose a Plan</strong> That's Right for You</h1>
		<p class="text-center" style="margin-bottom:50px;">TIME School pricing model offers three payment plans for Starter, Advance and Multi Branch.</p>
		<div class="row" style="margin-bottom:50px;">
			<div class="col-md-4" style="margin-bottom:10px;"> 
				<p class="text-center slctd_txt_sub" style="font-weight:bold;">&nbsp;</p>
			  <table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tbody><tr> 
				  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
					  <tbody><tr> 
						<td height="50" align="center" class="pricing_top"> <h3 style="color:#FFF;">STARTER</h3></td>
					  </tr>
					  <tr> 
						<td height="85" align="center" bgcolor="#006cb7" class="price_head"><span class="price">0.75% of FEE</span> / STUDENT</td>
					  </tr>
					  <tr> 
						<td height="50" align="center" bgcolor="#01538c" class="price_head">30 Days Free Trial</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"><strong>Admission</strong> Management</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Fee Management</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Exam Management</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Not Allowed</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Not Allowed</td>
					  </tr>
					  <tr> 
						<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">E-mail Supports (24/7)</td>
					  </tr>
					  <tr> 

						<td height="35" align="center" class="base"><a href="http://portal.timeschoolsystem.pk/school/register.php?p=1&amp;plan=STARTER" class="btn btn-primary" style="width:100%;border-top-left-radius: 0px;border-top-right-radius: 0px;">TRY FOR FREE</a></td>
					  </tr>
					</tbody></table></td>
				</tr>
			  </tbody></table>
			</div>
			<div class="col-md-4" style="margin-bottom:10px;"> 
	<p class="text-center slctd_txt_sub" style="font-weight:bold;">&nbsp;</p> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tbody><tr> 
		  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tbody><tr> 
				<td height="50" align="center" class="pricing_top"> <h3 style="color:#FFF;">ADVANCED</h3></td>
			  </tr>
			  <tr> 
				<td height="85" align="center" bgcolor="#006cb7" class="price_head"><span class="price">1.0% of FEE</span> / STUDENT</td>
			  </tr>
		<tr> 
			<td height="50" align="center" bgcolor="#01538c" class="price_head">30 Days Free Trial</td>
		</tr>
		<tr> 
			<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"><strong>Admission</strong> Management</td>
			  </tr>
		<tr> 
			<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Fee Management</td>
		</tr>
		<tr> 
			<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Exam Management</td>
		</tr>
		<tr> 
			<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Attendance system</td>
		</tr>
		<tr> 
			<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Time Table</td>
		</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Student Dairy</td>
	</tr><tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Notices and complaints</td>
	</tr>
	<tr> 
<td height="35" align="center" class="base"><a href="<?=base_url('school/register.php?p=2&amp;plan=ADVANCE'); ?>" class="btn btn-success" style="width:100%;border-top-left-radius: 0px;border-top-right-radius: 0px;">TRY FOR FREE</a></td>
</tr>
</tbody></table></td>
</tr>
</tbody></table>
</div>
<div class="col-md-4" style="margin-bottom:10px;">
<p class="text-center slctd_txt" style="font-weight:bold;">RECOMMENDED</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody><tr> 
	<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody><tr> 
		<td height="50" align="center" class="pricing_top"> <h3 style="color:#FFF;">PREMIUM</h3></td>
	</tr>
	<tr> 
		<td height="85" align="center" bgcolor="#006cb7" class="price_head"><span class="price">1.25% of FEE</span><span>/STUDENT</span></td>
	</tr>
	<tr> 
		<td height="50" align="center" bgcolor="#01538c" class="price_head">30 Days Free Trial</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"><strong>Admission</strong> Management</td>
	</tr>
	<tr>
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Fee Management</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Exam Management</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;"> Attendance system</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Time Table</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Student Dairy</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Notices and complaints</td>
	</tr>
	<tr>
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">
			Role Based User Accounts
		</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Web Based Parent Portal</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">Andriod APP</td>
	</tr>
	<tr> 
		<td height="35" align="center" bgcolor="#FFFFFF" class="base" style="border-bottom:solid 1px #CCC;">E Learning</td>
	</tr>
	<tr> 
		<td height="35" align="center" class="base"><a href="<?=base_url('assets/school/register.php?p=3&amp;plan=PREMIUM'); ?>" class="btn btn-info" style="width:100%;border-top-left-radius: 0px;border-top-right-radius: 0px;">TRY FOR FREE</a></td>
	</tr>
	</tbody></table></td>
	</tr> 
</tbody></table>
</div>
</div>
</div>
</div>
<?php include("footer.php"); ?> 