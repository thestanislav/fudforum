        <div id="table_wrapper" class="pure-g">
          <div class="pure-u-3-24">&nbsp;</div>
          <div class="pure-u-18-24">
            <div>
              You are not logged in. This could be due to one of several reasons:
              <ol>
                <li class="GenText">Your cookie has expired, and you need to login to renew your cookie.</li>
                <li class="GenText">You do not have permission to access the requested resource as an anonymous user.
                  You must login to gain permission.</li>
              </ol>
            </div>
            {error_message}
            <form id="login" method="post" action="{login_url}"
              class="pure-form-aligned pure-form">
              <fieldset>
                <div class="pure-control-group">
                  <label>Login:</label>
                  <input tabindex="1" name="login" type="text">
                  <a href="{register_url}">Want to register?</a>
                </div>                
                <div class="pure-control-group">
                  <label>Password:</label>
                  <input tabindex="2" name="password" type="password">
                  <a href="{passowrd_reset_url}">Forgot password</a>
                </div>
                <div class="pure-control-group">
                  <label></label>
                  <button name="submit" type="submit"  
                          class="pure-button pure-button-primary">Submit</button>
                </div>
              </fieldset>
            </form>
          </div>
          <div class="pure-u-3-24">&nbsp;</div>
        </div>