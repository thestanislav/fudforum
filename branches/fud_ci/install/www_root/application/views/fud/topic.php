        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Pagination -->
              {pagination}
              <!-- Contents -->
              <div id="messages">
                {messages}
                <div id="post_{m_id}" class="fud-message" >
                  <div class="fud-message-header">
                    <div class="width_75 inline_block">
                      <span class="fud-message-subject">{m_subject}</span>
                      <span class="fud-message-id">[Message #: {m_id}]</span>
                    </div>
                    <div class="width_auto inline_block text_right">
                      <span class="fud-message-date">{m_date}</span>
                    </div>
                  </div>
                  <div class="fud-message-author-info" >
                    <div>
                      {m_avatar}
                      <div class="inline_block vertical_top">
                        <span class="fud-message-author">{m_login}</span>
                      </div>
                    </div>
                    <div class="fud-contact-actions">
                      <a class="pure-button button-xsmall" href="#">Profile</a>
                    </div>
                    <div class="fud-contact-actions">
                      <a class="pure-button button-xsmall" href="#">PM</a>
                      <a class="pure-button button-xsmall" href="#">Email</a>
                    </div>
                    <div class="fud-contact-actions">
                      <a class="pure-button button-xsmall" href="#">Add buddy</a>
                      <a class="pure-button button-xsmall" href="#">Ignore</a>
                    </div>
                  </div>
                  <div class="clear fud-message-body">
                    <span>
                      {m_body}
                    </span>
                  </div>
                  <div class="fud-message-actions">
                    <?php if($can_reply): ?>
                    <a class="pure-button" href="{m_reply_url}">{m_reply_text}</a>
                    <a class="pure-button" href="{m_quote_url}">{m_quote_text}</a>
                    <?php endif; ?>
                  </div>
                  <div class="clear"></div>
                </div>
                {/messages}
              </div>
              <!-- End Contents -->
            </div>
          </div>
        </div>
