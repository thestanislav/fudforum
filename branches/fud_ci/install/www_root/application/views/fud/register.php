        <div id="login_wrapper_grid" class="pure-g">
          <div id="login_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <div id="description">
                Please fill in the following infromation to register.
              </div>
              <div id="error-message">
                {error_message}
              </div>
              <form id="login" method="post" action="{register_url}"
                class="pure-form-aligned pure-form">
                <fieldset>
                  <div class="pure-control-group">
                    <label>Name:</label>
                    <input tabindex="4" name="name" type="text">
                  </div>
                  <div class="pure-control-group">
                    <label>Login:</label>
                    <input tabindex="1" name="login" type="text">
                  </div>                
                  <div class="pure-control-group">
                    <label>Password:</label>
                    <input tabindex="2" name="password" type="password">
                  </div>
                  <div class="pure-control-group">
                    <label>Repeat Password:</label>
                    <input tabindex="3" name="password2" type="password">
                  </div>
                  <div class="pure-control-group">
                    <label></label>
                    <button name="submit" type="submit"  
                            class="pure-button pure-button-primary">Submit</button>
                  </div>
                </fieldset>
              </form>
            </div>
          </div>
        </div>