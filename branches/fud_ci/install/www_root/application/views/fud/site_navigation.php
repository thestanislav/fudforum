        <nav id="fud_site_navigation" class="pure-menu pure-menu-horizontal">
          <ul class="pure-menu-list" >
            <li class="pure-menu-item"><a class="pure-menu-link" href="{home_url}">Home</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="#">Members</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="#">Calendar</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="#">Search</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="#">Help</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="{cp_or_register_url}">{cp_or_register_text}</a></li>
            <li class="pure-menu-item"><a class="pure-menu-link" href="{login_logout_url}">{login_logout_text}</a></li>
            <?php if( !empty($administration_url) ): ?>
            <li class="pure-menu-item"><a class="pure-menu-link" href="{administration_url}">{administration_text}</a></li>
            <?php endif; ?>
          </ul>
        </nav>
