        <div id="login_wrapper_grid" class="pure-g">
          <div id="login_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <div id="description">
                Please fill in the following infromation to register.
              </div>
              <div id="error-message">
                {error_message}
              </div>
              <form id="registration" method="post" action="{register_url}"
                class="pure-form-aligned pure-form">
                <fieldset>
                  <div class="pure-control-group">
                    <label>Name:</label>
                    <input tabindex="1" name="name" type="text" 
                           placeholder="Your (full) name" autofocus required>
                  </div>
                  <div class="pure-control-group">
                    <label>Login:</label>
                    <input tabindex="2" name="login" type="text" 
                           placeholder="Desired username" required>
                  </div>                
                  <div class="pure-control-group">
                    <label>Password:</label>
                    <input tabindex="3" name="password" type="password" 
                           placeholder="Password" required>
                  </div>
                  <div class="pure-control-group">
                    <label>Repeat Password:</label>
                    <input tabindex="4" name="password2" type="password" 
                           placeholder="Repeat password" required>
                  </div>
                  <div class="pure-control-group">
                    <label>&nbsp;</label>
                    {captcha_image}
                  </div>
                  <div class="pure-control-group">
                    <label>Captcha:</label>
                    <input tabindex="5" type="text" name="captcha" value="" 
                           placeholder="Captcha" required >
                  </div>
                  <div class="pure-control-group">
                    <label></label>
                    <button tabindex="6" type="submit" name="submit"   
                            class="pure-button pure-button-primary">
                            Submit
                    </button>
                  </div>
                </fieldset>
              </form>
            </div>
          </div>
        </div>