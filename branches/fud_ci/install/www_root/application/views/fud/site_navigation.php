        <nav id="fud_site_navigation">
          <ul>
            <li><a class="pure-button" href="{home_url}">Home</a></li>
            <li><a class="pure-button" href="#">Members</a></li>
            <li><a class="pure-button" href="#">Calendar</a></li>
            <li><a class="pure-button" href="#">Search</a></li>
            <li><a class="pure-button" href="#">Help</a></li>
            <li><a class="pure-button" href="{cp_or_register_url}">{cp_or_register_text}</a></li>
            <li><a class="pure-button" href="{login_logout_url}">{login_logout_text}</a></li>
            <?php if( !empty($administration_url) ): ?>
            <li><a class="pure-button" href="{administration_url}">{administration_text}</a></li>
            <?php endif; ?>
          </ul>
        </nav>
