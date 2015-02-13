        <div id="reply_wrapper_grid" class="pure-g">
          <div id="reply_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Reply form -->
              <form action="{site_url}/newtopic/{fid}" method="post"
                    class="pure-form-aligned pure-form">
                <fieldset>
                  <div class="pure-control-group">
                    <label >Logged in as </label>
                    <input class="pure-input-2-3" name="username" type="text" readonly="true" value="{username}">
                    <br>
                  <div class="pure-control-group">
                  </div>
                    <label >Post to forum</label>
                    <input class="pure-input-2-3" name="forum" type="text" readonly="true" value="{forum}">
                  </div>
                  <div class="pure-control-group">
                    <label >Subject </label>
                    <input class="pure-input-2-3" name="subject" type="text" value="{subject}">
                  </div>
                  <div class="pure-control-group">
                    <label >Description </label>
                    <input class="pure-input-2-3" name="description" type="text" value="{description}">
                  </div>
                  <div class="pure-control-group">
                    <textarea name="message_contents" >{quote}</textarea>
                  </div>
                  <div class="fud_text_right"> 
                    <button name="cancel" type="cancel" 
                            class="pure-button">Cancel</button>
                    <button name="preview" type="submit" 
                            class="pure-button">Preview</button>
                    <button name="submit" type="submit" 
                            class="pure-button pure-button-primary">Submit</button>
                  </div>
                </fieldset>         
              </form>
            </div>
          </div>
        </div>
      
      <script type="text/javascript">
            CKEDITOR.replace( 'message_contents',
              {
                width: '66%',
                extraPlugins : 'bbcode',
                // Remove unused plugins.
                removePlugins : 'bidi,dialogadvtab,div,filebrowser,flash,format,forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,stylescombo,table,tabletools,templates',
                // Width and height are not supported in the BBCode format, so object resizing is disabled.
                disableObjectResizing : true,
                // Define font sizes in percent values.
                fontSize_sizes : "30/30%;50/50%;100/100%;120/120%;150/150%;200/200%;300/300%",
                toolbar :
                [
                  ['Find','Replace','-','SelectAll','RemoveFormat'],
                  ['Link', 'Unlink', 'Image', 'Smiley'],
                  // '/',
                  ['Bold', 'Italic','Underline'],
                  ['FontSize'],
                  ['TextColor'],
                  ['NumberedList','BulletedList','-','Blockquote'],
                  ['Source','-','Undo','Redo']
                ],
                // Strip CKEditor smileys to those commonly used in BBCode.
                smiley_images :
                [
                  'regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','tounge_smile.gif',
                  'embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angel_smile.gif','shades_smile.gif',
                  'cry_smile.gif','kiss.gif'
                ],
                smiley_descriptions :
                [
                  'smiley', 'sad', 'wink', 'laugh', 'cheeky', 'blush', 'surprise',
                  'indecision', 'angel', 'cool', 'crying', 'kiss'
                ]
            } );
          </script>