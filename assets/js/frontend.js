jQuery( document ).ready( function ( e ) {

  window.ORCSNET_LOGGING = false;
  var ORCSNET_PLUGIN_LOG = function(msg) {
    if(window.ORCSNET_LOGGING) {
      if(typeof(msg)==="object") {
        try {
          msg = JSON.stringify(msg);        
        }
        catch(e) {
          msg = '(could not serialize message)';
        }
      }
      console.log('[orcsnet-plugin]: ' + msg);
    }
  };

  var isElementInViewport = function(el) {
    if (el && el.getBoundingClientRect()) {
      var rect = el.getBoundingClientRect();
      var visible = (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
      return visible;
    }
    return false;
  };

  window.addEventListener('message',function(event) {
    if(event.origin!=='https://plugin.orcsnet.com') {
      return;
    }

    if(event.data) {
      var data = event.data;
      ORCSNET_PLUGIN_LOG(data);
      var el = document.getElementById(data.iframe_id);
      if (el) {
        if(data.action==='height') {
          ORCSNET_PLUGIN_LOG('animating iframe.height to ' + String(data.height));
          jQuery(el).animate({height:data.height},200);

        } else if(data.action==='send-positioning') {
          var iframeCR = el.getClientRects()[0];
          var positioning = {
            scrollTop: document.scrollingElement.scrollTop,
            iftop: iframeCR.top,
            ifbottom: iframeCR.bottom,
            ifleft: iframeCR.left,
            ifright: iframeCR.right,
            ifheight: iframeCR.height,
            ifwidth: iframeCR.width,
            ifx: iframeCR.x,
            ify: iframeCR.y,
          };
          ORCSNET_PLUGIN_LOG('sending positioning to plugin:');
          ORCSNET_PLUGIN_LOG(positioning);
          event.source.postMessage({positioning:positioning},event.origin);

        } else if(data.action==='am-i-in-viewport') {
          ORCSNET_PLUGIN_LOG('isElementInViewport=' + String(isElementInViewport(el)));
          event.source.postMessage({inViewport: isElementInViewport(el)},event.origin);

        } else if(data.action==='set-positioning') {
          if(data.positioning.hasOwnProperty('scrollTop')) {
            ORCSNET_PLUGIN_LOG('animating document.scrollingElement.scrollTop to ' + String(data.positioning.scrollTop));
            jQuery(document.scrollingElement).animate({scrollTop:data.positioning.scrollTop},200);
          } else {
            ORCSNET_PLUGIN_LOG('set-positioning ignored, no positioning available');
          }

        } else if(data.action==='scroll-into-view') {
          if(el.scrollIntoView) {
            ORCSNET_PLUGIN_LOG('calling scrollIntoView on iframe');
            el.scrollIntoView({behavior:'smooth'});
          } else {
            ORCSNET_PLUGIN_LOG('no scrollIntoView');
          }

        } else if(data.action==='send-meta') {
          ORCSNET_PLUGIN_LOG('sending meta-tags to plugin:');
          var metaTags = {};
          var mTags = document.getElementsByTagName('meta');
          for(var mIdx=0; mIdx < mTags.length; mIdx++) {
            var attrs = mTags[mIdx].attributes;
            var key = null;
            var value = null;
            if(attrs.content) { value = attrs.content.value; }
            if(attrs.name) { key = attrs.name.value; } 
            else if(attrs.property) { key = attrs.property.value; }
            if(key && value) {
              metaTags.push({k:key, v:value});
            }
          }
          ORCSNET_PLUGIN_LOG(metaTags);
          event.source.postMessage({metaTags:metaTags},event.origin);

        } else {
          ORCSNET_PLUGIN_LOG('unknown action ' + data.action);
        }
      } else {
        ORCSNET_PLUGIN_LOG('no iframe with id=' + data.iframe_id);
      }
    } else {
      ORCSNET_PLUGIN_LOG('no data in event');
    }
  });

});