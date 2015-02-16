		<nav id="fud_site_navigation">
			<ul>
				<li><a href="#">Members</a></li>
				<li><a href="#">Calendar</a></li>
				<li><a href="#">Search</a></li>
				<li><a href="#">Help</a></li>
				<li><a href="{cp_or_register_url}">{cp_or_register_text}</a></li>
				<li><a href="{login_logout_url}">{login_logout_text}</a></li>
				<?php if( !empty($administration_link) ): ?>
				<li><a href="{administration_url}">{administration_text}</a></li>
				<?php endif; ?>
				<li><a href="{home_url}">Home</a></li>
			</ul>
		</nav>
