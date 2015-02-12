		<nav id="fud_site_navigation">
			<ul>
				<li><a href="#">Members</a></li>
				<li><a href="#">Calendar</a></li>
				<li><a href="#">Search</a></li>
				<li><a href="#">Help</a></li>
				<li>{cp_or_register_link}</li>
				<li>{login_logout_link}</li>
				<?php if( !empty($administration_link) ) { ?>
				<li>{administration_link}</li>
				<?php } ?>
				<li><a href="{home_url}">Home</a></li>
			</ul>
		</nav>
