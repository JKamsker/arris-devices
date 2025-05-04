











<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">





<head>
  <title>ARRIS</title>
  <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">

  <!--CSS-->
  <link rel="stylesheet" type="text/css" media="screen" href="/cmn/css/common-min.css?m=1718271171" /><link rel="stylesheet" type="text/css" media="screen" href="/custom/1/branding.css?m=1718271172" />  <!--[if IE 6]>
  <link rel="stylesheet" type="text/css" href="/cmn/css/ie6-min.css" />
  <![endif]-->
  <!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="/cmn/css/ie7-min.css" />
  <![endif]-->
  <link rel="stylesheet" type="text/css" media="print" href="/cmn/css/print.css?m=1718271171" /><link rel="stylesheet" type="text/css" media="screen" href="/cmn/css/lib/jquery.radioswitch.css?m=1718271171" /><link rel="stylesheet" type="text/css" media="screen" href="/cmn/css/lib/jquery.sliderbar.css?m=1718271171" /><link rel="stylesheet" type="text/css" media="screen" href="/cmn/css/lib/jquery.password.css?m=1718271171" /><link rel="stylesheet" type="text/css" media="screen" href="/cmn/css/lib/jquery.inputGroup.css?m=1718271171" />  <!--Character Encoding-->
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <script type="text/javascript" src="/languages/script_en.js?m=1718271172"></script>  <script type="text/javascript" src="/cmn/js/lib/jquery-3.3.1.js"></script>
  <script type="text/javascript" src="/cmn/js/lib/jquery-migrate-3-0.1.js"></script>
  <script type="text/javascript" src="/cmn/js/lib/jquery.validate.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.alerts.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.ciscoExt.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.highContrastDetect.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.radioswitch.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.virtualDialog.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.sliderbar.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.password.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/lib/jquery.inputGroup.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/commonValidations.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/utilityFunctions.js?m=1718271171"></script><script type="text/javascript" src="/cmn/js/arris.js?m=1718271171"></script>  <div class="loading-indicator"></div><div class="loading-indicator-text"></div>  <style>
  #div-skip-to {
    position:relative;
    left: 150px;
    top: -300px;
  }
  #div-skip-to a {
    position: absolute;
    top: 0;
  }
  #div-skip-to a:active, #div-skip-to a:focus {
    top: 300px;
    color: #0000FF;
    /*background-color: #b3d4fc;*/
  }
  .emptydiv {
    float: left;
    width: 182px;
    position: relative;
    padding: 0 20px 0 10px;
  }
  .emptydiv1 {
    float: left;
    width: 182px;
    position: relative;
    padding: 0 20px 0 10px;
  }

  .globalbanner {
    float: left;
    color: red;
    background:#39baf1;
    width:684px;
    padding:8px;
    position:relative;
    overflow:hidden;
  }
  </style>
</head>


<script type="text/javascript">
  var operationStatusTimer;
  var serverRequestUserInterractor = {
    showOperationStatus: function(inProgress, timeout) {
      if (inProgress) {
        if (timeout > 0) {
          operationStatusTimer = setTimeout(function() {
            addProgressIndicator("This may take several seconds...", timeout);
          }, 1000);
        }
      } else {
        clearTimeout(operationStatusTimer);

        // Only remove the progress indicator if not still waiting for data.  If still waiting, the
        // page will be refreshed once the data becomes available.
        if (typeof WAITING_FOR_DATA === "undefined" && !notificationStopped) {
          removeProgressIndicator();
        }
      }
    },

    handleIncorrectCaptcha: function() {
      jAlert("The CAPTCHA code was incorrect. Please try again.");
    },
    handleDecryptionFailure: function(response) {
      if (response != undefined && response.session_expired) {
        jAlert("Your session has expired. Please try again.", null, function() {location.reload(true);});
      } else {
        jAlert("Invalid Login Credentials!");
      }
    },
    handleNotAuthenticated: function() {
      location.href = "/login.php";
    },
    handleNetworkDisconnected: function() {
      if(!notificationStopped) {
        jAlert("The connection to your Gateway was lost and prevented the operation from completing.<br>You may wish to try again.");
      }
    },
    handleValidationProblem: function(problem) {
      jAlert(problem.replace(/\&amp;/g, '&'));
    },
    handleParameterValidationProblem: function(invalidParams, parameterDisplayNameMap) {
      let invalidParamError = retrieveInvalidParamError(null, invalidParams, parameterDisplayNameMap);
      jAlert($.validator.format("Incorrect parameter values: {0}", invalidParamError));
    },
    handleGeneralProblem: function() {
      jAlert("Operation Failed.");
    }
  };
</script>

<script type="text/javascript" src="/common/js/lib/sjcl.js?m=1718269129"></script><script type="text/javascript" src="/common/js/sjclCrypto.js?m=1718271172"></script>  <script type="text/javascript" nonce="">
    // This constant should be used as an operationTimeout parameter
    var BACKGROUND_OPERATION = -1;

    function updateServerRecord(resourceUrl, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {
      communicateToServer("PUT", resourceUrl, "application/json", params, parameterDisplayNameMap, operationTimeout, successCallback, ((asyncSetting === undefined) ? true : asyncSetting));
    }

    function createServerRecord(resourceUrl, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {
      communicateToServer("POST", resourceUrl, "application/json", params, parameterDisplayNameMap, operationTimeout, successCallback, ((asyncSetting === undefined) ? true : asyncSetting));
    }

    function deleteServerRecord(resourceUrl, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {
      communicateToServer("DELETE", resourceUrl, "application/json", params, parameterDisplayNameMap, operationTimeout, successCallback, ((asyncSetting === undefined) ? true : asyncSetting));
    }

    function readServerRecord(resourceUrl, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {
      communicateToServer("GET", resourceUrl, "application/json", params, parameterDisplayNameMap, operationTimeout, successCallback, ((asyncSetting === undefined) ? true : asyncSetting));
    }

    function uploadFileToServer(resourceUrl, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {
      // Set contentType to false to upload file with FormData.
      communicateToServer("POST", resourceUrl, false, params, parameterDisplayNameMap, operationTimeout, successCallback, ((asyncSetting === undefined) ? true : asyncSetting));
    }



    function communicateToServer(method, resourceUrl, contentType, params, parameterDisplayNameMap, operationTimeout, successCallback, asyncSetting) {

      var userInteractor = serverRequestUserInterractor;

              if (params != null && contentType == "application/json") {
          params = encryptParametersIfNeeded(params);
        }
      
      userInteractor.showOperationStatus(true, operationTimeout);

      $.ajax({
        type: method,
        url: resourceUrl,
        async: asyncSetting,
        contentType: contentType,
        processData: contentType == "application/json" ? true : false,
        data: params != null ? (contentType == "application/json" ? JSON.stringify(params) : params) : null
      }).done(function(data, textStatus, xhr) {
        function success() {
          userInteractor.showOperationStatus(false);

          if (successCallback) {
            successCallback(data, xhr.status, xhr);
          }
        }

        var delayTimeInSeconds = parseInt(xhr.getResponseHeader('X-Client-Delay-Time-In-Seconds'));

        if (!isNaN(delayTimeInSeconds) && (delayTimeInSeconds > 0)) {
          setTimeout(success, (delayTimeInSeconds * 1000));
        } else {
          if (data.save_encryption_params != undefined) {
            setEncryptionParamsToSession();
          } else {
                          if ((typeof data == "object") || ((typeof data == "string") && (data.trim() != ""))) {
                data = decryptDataIfNeeded(data);
              }
                      }
          success();
        }
      }).fail(function(xhr, textStatus, errorThrown) {
        //Report errors only for non-background operations.
        if (operationTimeout != BACKGROUND_OPERATION) {
          userInteractor.showOperationStatus(false);

          if (xhr.status == "400") {
            try {
              var invalidParams = JSON.parse(xhr.responseText);
              userInteractor.handleParameterValidationProblem(invalidParams, parameterDisplayNameMap);
            } catch (e) {
              console.error("Server reported that the input was generally malformed / misformatted");
              userInteractor.handleGeneralProblem();
            }
          } else if (xhr.status == "409") {
            userInteractor.handleValidationProblem(xhr.responseText.trim());
          } else if (xhr.status == "470") {
            userInteractor.handleIncorrectCaptcha();
          } else if (xhr.status == "401") {
            // Server-side detected authentication loss
            userInteractor.handleNotAuthenticated();
          } else if (xhr.status == "0") {
            userInteractor.handleNetworkDisconnected();
          } else if (xhr.status == "471") {
            userInteractor.handleDecryptionFailure(xhr.responseJSON);
          } else {
            console.error("Server reported some unexpected problem: status=" + xhr.status + ", error=" + errorThrown);
            userInteractor.handleGeneralProblem();
          }
        }
      });
    }

    function retrieveInvalidParamError(param, paramError, parameterDisplayNameMap) {
      if (typeof(paramError) == "object") {
        var errors = "";
        for (var nestedParam in paramError) {
          if (errors != "") {
            errors += ", ";
          }
          errors += retrieveInvalidParamError(nestedParam, paramError[nestedParam], parameterDisplayNameMap);
        }

        return errors;
      } else {
        return parameterDisplayNameMap != null && parameterDisplayNameMap[param] != null ? parameterDisplayNameMap[param] : param;
      }
    }

    var sjclEncryptObj = {};

    // Adds user info to sjclEncryptObj. This function needs to be called on pages where we start using our GUI.
    // Example - Wizard step 1, Login page.
    function addUserInfoToEncryptObj(user, password) {
              sjclEncryptObj.salt = "";
        sjclEncryptObj.iv = "";
        sjclEncryptObj.user = user;
        sjclEncryptObj.password = password;
    }

    // Gets encryption params from session storage(post login) while making a request for encrypting
    function getEncryptionParamsFromSession() {
      return {iv:   sessionStorage.getItem('sjcl_iv'),
              key:  sessionStorage.getItem('sjcl_key'),
              user: sessionStorage.getItem('user')};
    }

    // Updates browser session storage with encryption params after successful login.
    function setEncryptionParamsToSession() {
      sessionStorage.setItem('sjcl_iv', sjclEncryptObj.iv);
      sessionStorage.setItem('sjcl_key', sjclEncryptObj.key);
      sessionStorage.setItem('user', sjclEncryptObj.user);
    }

    function encryptQueryParametersIfNeeded(params) {
      if (true) {
        var paramsObj = {};
        params = params.split("&");
        for (var i = 0; i < params.length; i++) {
          var value = params[i].split("=");
          paramsObj[value[0]] = value[1];
        }

        var encryptedData = encryptParametersIfNeeded(paramsObj);
        return "EncryptedData" + "=" + encryptedData.EncryptedData + "&user=" + encryptedData.user;
      } else {
        return params;
      }
    }

    function convertEncryptedQueryParameters(text) {
      if (true) {
        return encryptQueryParametersIfNeeded(text)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }
      else
      {
        return encryptQueryParametersIfNeeded(text);
      }
    }

    // Client side encryption api to encrypt parameters for all requests.
    function encryptParametersIfNeeded(data) {
      if (true) {
        var encryptedData = {};
        if (!jQuery.isEmptyObject(sjclEncryptObj)) {
          sjclEncryptObj.key = sjclPbkdf2(sjclEncryptObj.password, sjclEncryptObj.salt, DEFAULT_SJCL_ITERATIONS, DEFAULT_SJCL_KEYSIZEBITS);
          encryptedData.EncryptedData = sjclCCMencrypt(sjclEncryptObj.key, JSON.stringify(data), sjclEncryptObj.iv, "ARRIS", DEFAULT_SJCL_TAGLENGTH);
          encryptedData.user = sjclEncryptObj.user;
        } else {
          var encryptionParams = getEncryptionParamsFromSession();
          encryptedData.EncryptedData = sjclCCMencrypt(encryptionParams.key, JSON.stringify(data), encryptionParams.iv, "ARRIS", DEFAULT_SJCL_TAGLENGTH);
          encryptedData.user = encryptionParams.user;
        }
        return encryptedData;
      } else {
        return data;
      }
    }

    function decryptDataIfNeeded(receivedData) {
      if (true) {
        var encryptParams = !jQuery.isEmptyObject(sjclEncryptObj) ? sjclEncryptObj : getEncryptionParamsFromSession();
        return JSON.parse(sjclCCMdecrypt(encryptParams.key, receivedData["EncryptedData"],
                                         encryptParams.iv, "ARRIS", DEFAULT_SJCL_TAGLENGTH));
      } else {
        return receivedData;
      }
    }

  </script>


<script type="text/javascript">
  function stopSessionNotifications() {
    notificationStopped = true;
    clearTimeout(queueTimer);
  }

  var notificationStopped = false;
  var queueTimer = null;
  var inQueueingPeriod = false;
  var hasQueuedNotification = false;

  function sendActiveNotification() {
    hasQueuedNotification = true;
    if (!inQueueingPeriod) {
      inQueueingPeriod = true;
      $.ajax({
        type: "POST",
        url: "actionHandler/ajaxSet_SessionActive.php",
        success: function(msg) {
          hasQueuedNotification = false;
          queueTimer = setTimeout(function() {
            inQueueingPeriod = false;
            if (hasQueuedNotification) {
              sendActiveNotification();
            }
          }, 10000);
        },
        error: function(xhr) {
          if (xhr.status == 401 && notificationStopped == false) {
            updateServerRecord("actionHandler/ajaxSet_logout.php", null, null, BACKGROUND_OPERATION, function() {
              jAlert(
                "Your session is not active. Please log in again.",
                "You are being logged out!",
                function(ret) {location.href = "login.php"}
              );
            });
          }
        }
      });
    }
  }

  function showBrowserDialog() {
    var message;
    //Check if atleast one browser is from the supported list.
    if (isSupportedBrowser()) {
      message = $.validator.format("The version of <strong>{0}</strong> you are using is unsupported. Your Gateway's web interface has been tested to work with the minimum version: <strong>{1}</strong>. <br><br> You may proceed with an unsupported version, but you might experience issues using the pages.", $.browser.name, $.browser.min_version);
    } else {
      var str = '';
      for (var browser in MIN_BROWSER_VERSIONS) {
        str += '<strong>' + browser + ':' + '</strong> ' + MIN_BROWSER_VERSIONS[browser] + '<br>';
      }
      message = $.validator.format("Your Gateway's web interface has been tested to work with the following minimum browser versions: <br> <br> {0} <br> You may proceed with an unsupported version, but you might experience issues using the pages.", str);
    }

    $.virtualDialog("hide");
    $.virtualDialog({
      title: "Supported Browsers Versions",
      content: '<div class="content_message">' +
                 '<div class="form-row odd">'+ message + '</div>' +
               '</div>',
      footer: '<input id="unsupported_browser_button" type="button" value="CLOSE" style="float: right;" />',
      width: "600px"
    });
    $("#unsupported_browser_button").off("click").on("click", function() {
      $.virtualDialog("hide");
    });
  }

      $.ajaxSetup({
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-CSRF-Token", sessionStorage.getItem("CsrfToken"));
      }
    });
  
  $(document).ready(function() {
    // If still waiting for data, do nothing and the page will be reloaded when the data is ready.
    if (typeof WAITING_FOR_DATA !== "undefined") {
      return;
    }

    // focus current page link, must after page.init()
    //$('#nav [href="'+location.href.replace(/^.*\//g, '')+'"]').focus();   // need a "skip nav" function
    $("#skip-link").click(function () {
      $('#content').attr('tabIndex', -1).focus();  //this is to fix skip-link doesn't work on webkit-based Chrome
    });
    // change radio-btn status and do ajax when press "enter"
    $(".radio-btns a").keypress(function(event) {
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if (13 == keycode) {
        if (!$(this).parent(".radio-btns").find("li").hasClass("selected"))  {
          return;   // do nothing if has disabled class, don't detect disabled attr for radio-btn
        }
        // console.log($(this).find(":radio").hasClass("disabled"));
        $(this).find(":radio").trigger('click');
        $(this).find(":radio").trigger('change');
        $(this).parent(".radio-btns").radioToButton();
      }
    });
    // press Esc to skip menu and goto first control of content
    // Esc:keypress:which is zero in FF, Esc:keypress is not work in Chrome
    $("#nav").keydown(function(event) {
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if (27 == keycode) {
        $("#content textarea:eq(0)").focus();
        $("#content input:eq(0)").focus();
        $("#content a:eq(0)").focus();      // high priority element to focus
      }
    });
    /* changes for high contrast mode */
    $.highContrastDetect({useExtraCss: true, debugInNormalMode: false});
    if ($.__isHighContrast) {
      /* change plus/minus tree indicator of nav menu */
      $("#nav a.top-level").prepend('<span class="hi_nav_top_indi">[+]</span>');
      $("#nav a.folder").prepend('<span class="hi_nav_folder_indi">[+]</span>');
      $("#nav a.top-level-active span.hi_nav_top_indi").text("[-]");
      $("#nav a.folder").click(function() {
        /* this should be called after nav state changed */
        var $link = $(this);
        if ($link.hasClass("folder-open")) {
          $link.children("span.hi_nav_folder_indi").text("[-]");
        }
        else {
          $link.children("span.hi_nav_folder_indi").text("[+]");
        }
      });
    }
    /*
    * these 3 sections for radio-btn accessibility, as a workaround, maybe should put at the front of .ready().
    */
    // add "role" and "title" for ARIA, attr may need to be embedded into html
    $(".radio-btns a").each(function() {
      $(this).attr("role", "radio").attr("title", $(this).closest("ul").prev().text() + $(this).find("label").text());
    });
    // monitor "aria-checked" status for JAWS, NOTE: better depends on input element
    $(".radio-btns").change(function() {
      $(this).find("a").each(function() {
        $(this).attr("aria-checked", $(this).find("input").attr("checked") ? "true" : "false");
      });
    });
    //give the initial status, do not trigger change above
    $(".radio-btns").find("a").each(function(){
      $(this).attr("aria-checked", $(this).find("input").attr("checked") ? "true" : "false");
    });


    
        //when clicked on this page, restart timer
        var jsInactTimeout = parseInt("540") * 1000;
        var h_timer = null;
        var sessionActive = true;
        $(document).click(function() {
          // do not handle click event when count-down show up
          if ($("#count_down").length > 0 || !sessionActive) {
            return;
          }

          clearTimeout(h_timer);
          sendActiveNotification();
          h_timer = setTimeout(function() {
            var cnt = 60;
            var h_cntd = setInterval(function() {
              $("#count_down").text(--cnt);
              // (1)stop counter when less than 0, (2)hide warning when achieved 0, (3)add another alert to block user action if network unreachable
              if (cnt <= 0) {
                sessionActive = false;
                clearInterval(h_cntd);
                updateServerRecord("actionHandler/ajaxSet_logout.php", null, null, BACKGROUND_OPERATION, function() {
                  sessionStorage.setItem('session_expired', true);
                  location.href = "login.php";
                });
              }
            }, 1000);

            // use jAlert instead of alert, or it will not auto log out untill OK pressed!
            jAlert($.validator.format("Press OK to continue session. Otherwise you will be logged out in <span id='count_down' style='font-size: 200%; color: red;'>{0}</span> seconds.", cnt),
                   "You are being logged out due to inactivity!",
                   function() {
                     clearInterval(h_cntd);
                     sendActiveNotification();
                   });
          }, jsInactTimeout);
        }).trigger("click");
    $(".loading-indicator").remove();$(".loading-indicator-text").remove();
    document.body.style.overflow = "visible";
    document.getElementById("container").style.opacity = 1;

    window.addEventListener('online', function() {
      if (!notificationStopped) {
        $.virtualDialog("hide");
      }
    });
    window.addEventListener('offline', function() {
      if (!notificationStopped) {
        $.virtualDialog("hide");
        $.virtualDialog({
          title: "Connection to the gateway is lost.",
          content: "The connection to your Gateway has been lost!<br>Please ensure that your device is properly connected and then try refreshing the page in your browser.",
          footer: '<input id="pop_button" type="button" value="CLOSE" style="float: right;" />',
        });
        $("#pop_button").off("click").on("click", function(){
          $.virtualDialog("hide");
        });
      }
    });

    if (!isSupportedVersion()) {
      $("#unsupported_browser_note").html('<b><font color="red" >Warning:</font> <a href="#" onClick="showBrowserDialog()">Unsupported Browser</a></b>');
    }
    function dismissMobileBanner() {
      sessionStorage.setItem("MobileBannerDismissed", "dismissed");
      $("#MobilePinRequired").hide();
      $(".emptydiv").hide();
    }
    if (document.getElementById("dismissMobileBanner")) document.getElementById("dismissMobileBanner").addEventListener("click", dismissMobileBanner);
    if (document.getElementById("continueEnterPIN")) document.getElementById("continueEnterPIN").addEventListener("click", dismissMobileBanner);
    if(sessionStorage.getItem("MobileBannerDismissed") == "dismissed"){
      $("#MobilePinRequired").hide();
      $(".emptydiv").hide();
    }
  });

</script>
<body style="overflow: hidden;">
  <!--Main Container - Centers Everything-->
  <div id="container">
    <!--Header-->
    <div id="header">
      <p style="margin: 0">&nbsp;</p>      <div id="logo">
        <img src="/custom/1/logo.png?m=1718271172" alt="Logo" title="Logo" />      </div>
      <div id="halogo">
        <img src="/custom/1/halogo.png?m=1718271172" alt="HALogo" title="HALogo" />      </div>

      <div id="connection_lost_warning"><strong>Gateway connection lost!</strong></div>
    </div> <!-- end #header -->
    <div id='div-skip-to' style="display: none;">
      <a id="skip-link" name="skip-link" href="#content">Skip to content</a>
    </div>
    <div id="sub-header">
      <!--dynamic generate user bar icon and tips-->



<ul id="userToolbar" class="on">
  <li class="first-child">Hi, admin</li><li style="list-style:none outside none; margin-left:0">&nbsp;&nbsp;&#8226;&nbsp;&nbsp;<a href="javascript:void(0)" id="logout" tabindex="0">Logout</a></li><li style="list-style:none outside none; margin-left:0">&nbsp;&nbsp;&#8226;&nbsp;&nbsp;<a href="account_management.php" tabindex="0">Change Password</a></li><script>
            function setLanguage(language) {
              $.ajax({
                type: "PUT",
                url: "actionHandler/ajax_language.php",
                data: JSON.stringify({lang: language})
              }).done(function() {
                location.reload(true);
              });
            }
          </script><select id="languageSelector" onchange="setLanguage(this.value)">  <option value="en" selected>English</option>  <option value="es" >Español</option>  <option value="fr" >Français</option>  <option value="de" >Deutsch</option>  <option value="pt" >Português</option>  <option value="pl" >Polski</option></select></ul>





<script type="text/javascript">

function refreshUserbar(state) {
  refreshBattery(state.battery);
  refreshInternet(state.internet);
  refreshEthernet(state.ethernet);
  refreshWifi(state.wifi);
  refreshMoca(state.moca);
  refreshFirewall(state.firewall);
}


function refreshBattery(batteryState) {
  if (batteryState.level == -1) {
    $("#status_battery a label").text("N/A");
    $("#status_battery .tooltip").html("Battery not installed");
  } else {
    $("#status_battery a label").text(batteryState.level + "%");
    $("#status_battery .tooltip").html("Battery level" + " " + batteryState.level + "%");
  }

  var batteryClass;
  if (batteryState.level > 90) {
    batteryClass = "bat-100";
  } else if (batteryState.level > 60) {
    batteryClass = "bat-75";
  } else if (batteryState.level > 39) {
    batteryClass = "bat-50";
  } else if (batteryState.level > 18) {
    batteryClass = "bat-25";
  } else if (batteryState.level > 8) {
    batteryClass = "bat-10";
  } else {
    batteryClass = "bat-0";
  }
  $("#status_battery span").removeClass("bat-0 bat-10 bat-25 bat-50 bat-75 bat-100");
  $("#status_battery span").addClass(batteryClass);
}

function refreshInternet(internetState) {
  turnOnUserbarElement("status_inet", internetState.connected);
  if (internetState.ip != null) {
    if (internetState.connected) {
      $("#status_inet .tooltip").html("Connected<br>WAN IP: " + internetState.ip);
    } else {
      $("#status_inet .tooltip").html("Not Connected");
    }
  } else {
     $("#status_inet .tooltip").html("Loading...");
  }
}

function refreshEthernet(ethernetState) {
  turnOnUserbarElement("status_enet", ethernetState.enabled);
  if (ethernetState.connected_devices != null) {
    if (ethernetState.enabled) {
      $("#status_enet .tooltip").html("Active<br>" + $.validator.format("{0} device(s) connected", ethernetState.connected_devices));
    } else {
      $("#status_enet .tooltip").html("Inactive");
    }
  } else {
     $("#status_enet .tooltip").html("Loading...");
  }
}

function refreshWifi(wifiState) {
  turnOnUserbarElement("status_wifi", wifiState.enabled);
  if (wifiState.connected_devices != null) {
    if (wifiState.enabled) {
      $("#status_wifi .tooltip").html("Enabled<br>" + $.validator.format("{0} device(s) connected", wifiState.connected_devices));
    } else {
      $("#status_wifi .tooltip").html("Disabled");
    }
  } else {
    $("#status_wifi .tooltip").html("Loading...");
  }
}

function refreshMoca(mocaState) {
  turnOnUserbarElement("status_moca", mocaState.enabled);
  if (mocaState.connected_devices != null) {
    if (mocaState.enabled) {
      $("#status_moca .tooltip").html("Enabled<br>" + $.validator.format("{0} device(s) connected", mocaState.connected_devices));
    } else {
      $("#status_moca .tooltip").html("Disabled");
    }
  } else {
    $("#status_moca .tooltip").html("Loading...");
  }
}

function refreshFirewall(firewallState) {
  turnOnUserbarElement("status_firewall", firewallState.level == "Low" || firewallState.level == "Medium" || firewallState.level == "High");

  var firewallDisplayLevel = "";
  if (firewallState.level == "High") {
    firewallDisplayLevel = "High";
  } else if (firewallState.level == "Medium") {
    firewallDisplayLevel = "Medium";
  } else if (firewallState.level == "Low") {
    firewallDisplayLevel = "Low";
  } else if (firewallState.level == "Custom") {
    firewallDisplayLevel = "Custom";
  } else {
    firewallDisplayLevel = "Disabled";
  }

  $("#status_firewall a label").text($.validator.format("{0} Security", firewallDisplayLevel));
  $("#status_firewall .tooltip").html($.validator.format("Firewall is set to {0}", firewallDisplayLevel));
}




function turnOnUserbarElement(element, isOn) {
  if (isOn) {
    $("#" + element).removeClass("off");
  } else {
    $("#" + element).addClass("off");
  }
}


function updateUserbar() {
  $.ajax({
    type: "GET",
    url: "actionHandler/ajax_userbar.php",
    dataType: "json",
    success: function(state) {
      $("#connection_lost_warning").css('display', 'none');
      if (state != null) {
        refreshUserbar(state);
      }
    }, error: function() {
      $("#connection_lost_warning").css('display', 'inline-block');
    }
  });
}


$(document).ready(function() {
  setInterval(updateUserbar, 1000 * 30); //refresh status bar every 30 seconds

  $("#logout").click(function() {
    updateServerRecord("actionHandler/ajaxSet_logout.php", null, null, BACKGROUND_OPERATION, function() {location.href = "login.php";});
  });

  // show pop-up info when focus
  $("#status a").focus(function() {
    $(this).mouseenter();
  });
  // disappear previous pop-up
  $("#status a").blur(function() {
    $(".tooltip").hide();
  });

  // Add shortcut links if logged in.
      $("#status_battery a").attr("href", "battery.php");
    $("#status_inet a").attr("href", "wan.php");
    $("#status_enet a").attr("href", "connected_devices.php");
    $("#status_wifi a").attr("href", "wireless_network_configuration.php");
    $("#status_moca a").attr("href", "moca.php");
    $("#status_firewall a").attr("href", "firewall_settings_ipv4.php");
  });
</script>



<ul id="status">
  

  <li id="status_inet" class="internet">
    <span class="value on-off sprite_cont">
      <img src="/cmn/img/icn_on_off.png?m=1718271172" />    </span>
    <a href="javascript: void(0);" tabindex="0">
      <label>Internet</label>
      <div class="tooltip"></div>
    </a>
  </li>

  <li id="status_enet" class="ethernet">
    <span class="value on-off sprite_cont">
      <img src="/cmn/img/icn_on_off.png?m=1718271172" />    </span>
    <a href="javascript: void(0);" tabindex="0">
      <label>Ethernet</label>
      <div class="tooltip"></div>
    </a>
  </li>

  <li id="status_wifi" class="wifi">
    <span class="value on-off sprite_cont">
      <img src="/cmn/img/icn_on_off.png?m=1718271172" />    </span>
    <a href="javascript: void(0);" tabindex="0">
      <label>Wi-Fi</label>
      <div class="tooltip"></div>
    </a>
  </li>

  
  <li id="status_firewall" class="security">
    <span class="value on-off sprite_cont">
      <img src="/cmn/img/icn_on_off.png?m=1718271172" />    </span>
    <a href="javascript: void(0);" tabindex="0">
      <label></label>
      <div class="tooltip">Loading...</div>
    </a>
  </li>

  <script type="text/javascript">
    //request the initial update right away
    var systemState = {"battery":{"level":-1},"internet":{"connected":true,"ip":"212.241.85.84"},"ethernet":{"enabled":true,"connected_devices":10},"wifi":{"enabled":true,"connected_devices":7},"moca":{"enabled":false,"connected_devices":""},"firewall":{"level":"Medium"}};
    refreshUserbar(systemState);
  </script>
</ul>
    </div>
    <div id="promote-sbc">
          </div>
    <!--Main Content-->
    <div id="main-content">
            

<!-- $Id: nav.dory.php 3155 2010-01-06 19:36:01Z slemoine $ -->
<!--Nav-->

<div id="nav"><ul><li id="menu-gateway"><a role="menuitem" title="click to toggle submenu" class="top-level" href="at_a_glance.php">Gateway</a><ul><li id="menu-at-a-glance"><a role="menuitem" href="at_a_glance.php">Summary</a></li><li id="menu-connection"><a role="menuitem" title="click to toggle submenu" href="javascript:;">Connection</a><ul style="padding-left:10px"><li id="menu-connection-status"><a role="menuitem" href="connection_status.php">Status</a></li><li id="menu-wan-network"><a role="menuitem" href="wan.php">WAN</a></li><li id="menu-local-ip-config"><a role="menuitem" title="click to toggle submenu" href="javascript:;">Local IP Network</a><ul style="padding-left:10px"><li id="menu-local-ip-config-ipv4"><a role="menuitem" href="local_ipv4_configuration.php">IPv4</a></li><li id="menu-local-ip-config-ipv6"><a role="menuitem" href="local_ipv6_configuration.php">IPv6</a></li></ul></li><li id="menu-wifi-config"><a role="menuitem" title="click to toggle submenu" href="javascript:;">Wi-Fi</a><ul style="padding-left:10px"><li id="menu-wifi-config-networks"><a role="menuitem" href="wireless_network_configuration.php">Networks</a></li><li id="menu-wifi-config-radio-1"><a role="menuitem" href="wireless_radio_configuration.php?EncryptedData=5f1dd9fbf6905d1d308b887c68db9606c93ebe7113c38e664a37&amp;user=admin">2.4 GHz Radio</a></li><li id="menu-wifi-config-radio-2"><a role="menuitem" href="wireless_radio_configuration.php?EncryptedData=5f1dd9fbf6905d1e308b1287379de59de397fd4b1292e47ce216&amp;user=admin">5 GHz Radio</a></li><li id="menu-wifi-config-mac-filter"><a role="menuitem" href="wireless_mac_filtering.php">MAC Filtering</a></li><li id="menu-wifi-config-wps"><a role="menuitem" href="wireless_network_configuration_wps.php">WPS</a></li></ul></li></ul></li><li id="menu-firewall"><a role="menuitem" title="click to toggle submenu" href="javascript:;">Firewall</a><ul style="padding-left:10px"><li id="menu-firewall-ipv4"><a role="menuitem" href="firewall_settings_ipv4.php">IPv4</a></li><li id="menu-firewall-ipv6"><a role="menuitem" href="firewall_settings_ipv6.php">IPv6</a></li></ul></li><li id="menu-software"><a role="menuitem" href="software.php">Software</a></li><li id="menu-hardware"><a role="menuitem" title="click to toggle submenu" href="javascript:;">Hardware</a><ul style="padding-left:10px"><li id="menu-system-hardware"><a role="menuitem" href="hardware.php">System Hardware</a></li><li id="menu-lan"><a role="menuitem" href="lan.php">Ethernet</a></li><li id="menu-wifi"><a role="menuitem" href="wifi.php">Wireless</a></li></ul></li><li id="menu-time"><a role="menuitem" href="time.php">Time</a></li><li id="menu-wizard"><a role="menuitem" href="wizard_step1.php">Wizard</a></li></ul></li><li id="menu-connected-devices"><a role="menuitem" title="click to toggle submenu" class="top-level" href="connected_devices.php">Connected Devices</a><ul><li id="menu-cdevices"><a role="menuitem" href="connected_devices.php">Devices</a></li><li id="menu-saddresses"><a role="menuitem" href="static_addresses.php">Static Addresses</a></li></ul></li><li id="menu-parental-control"><a role="menuitem" title="click to toggle submenu" class="top-level" href="managed_sites.php">Parental Control</a><ul><li id="menu-sites"><a role="menuitem" href="managed_sites.php">Managed Sites</a></li><li id="menu-services"><a role="menuitem" href="managed_services.php">Managed Services</a></li><li id="menu-devices"><a role="menuitem" href="managed_devices.php">Managed Devices</a></li><li id="menu-parental-reports"><a role="menuitem" href="parental_reports.php">Reports</a></li></ul></li><li id="menu-advanced"><a role="menuitem" title="click to toggle submenu" class="top-level" href="port_forwarding.php">Advanced</a><ul><li id="menu-port-forwarding"><a role="menuitem" href="port_forwarding.php">Port Forwarding</a></li><li id="menu-port-triggering"><a role="menuitem" href="port_triggering.php">Port Triggering</a></li><li id="menu-remote-management"><a role="menuitem" href="remote_management.php">Remote Management</a></li><li id="menu-dmz"><a role="menuitem" href="dmz.php">DMZ</a></li><li id="menu-alg"><a role="menuitem" href="alg.php">ALG</a></li><li id="menu-dynamic-dns"><a role="menuitem" href="dynamic_dns.php">Dynamic DNS</a></li><li id="menu-device-discovery"><a role="menuitem" href="device_discovery.php">Device Discovery</a></li></ul></li><li id="menu-mesh"><a role="menuitem" title="click to toggle submenu" class="top-level" href="wifi_mesh_settings.php">Wi-Fi MESH</a><ul><li id="menu-mesh-settings"><a role="menuitem" href="wifi_mesh_settings.php">Wi-Fi Mesh Settings</a></li><li id="menu-ahnc"><a role="menuitem" title="click to toggle submenu" href="topology.php">AHNC</a><ul style="padding-left:10px"><li id="menu-ahnc-topology"><a role="menuitem" href="topology.php">Network Topology</a></li></ul></li></ul></li><li id="menu-troubleshooting"><a role="menuitem" title="click to toggle submenu" class="top-level" href="troubleshooting_logs.php">Troubleshooting</a><ul><li id="menu-logs"><a role="menuitem" href="troubleshooting_logs.php">Logs</a></li><li id="menu-diagnostic-tools"><a role="menuitem" href="network_diagnostic_tools.php">Diagnostic Tools</a></li><li id="menu-wifi-spectrum-analyzer"><a role="menuitem" href="wifi_spectrum_analyzer.php">Wi-Fi Spectrum Analyzer</a></li><li id="menu-restore-reboot"><a role="menuitem" href="restore_reboot.php">Restart/Restore</a></li></ul></li><li id="menu-user-account"><a role="menuitem" title="click to toggle submenu" class="top-level" href="account_management.php">User Account</a><ul><li id="menu-account-management"><a role="menuitem" href="account_management.php">Account Management</a></li></ul></li></ul></div>
<script  type="text/javascript">  $(document).ready(function() {    arris.page.init("Connected Devices > Devices", "menu-cdevices");  });</script>


<script>
$(document).ready(function() {
  readServerRecord("actionHandler/ajaxSet_connected_devices.php", null, null, BACKGROUND_OPERATION, function(hosts) {
    removeLoadingBar();
    let tbody = $("tbody");
    if (hosts.length == 0) {
      tbody.append("<tr><td colspan='7'>There are no devices to display.</td></tr>");
      return;
    }

    if( ("HA-V3" == "AHNC") &&  ("true" == "true"))
      jAlert("Your home Wi-Fi Network is being managed and optimized by HomeAssure\u2122 V3. For accurate topology, download HomeAssure V3 mobile application. <br><br>The HomeAssure V3 mobile app is available online as a free download for your mobile device:<br><strong><li;>Apple App Store (for iOS devices)<li;>Google Play (for Android devices)</strong>", "Message");

    // Show static devices before DHCP devices.
    hosts.sort(function(host1, host2) {
      return (host2.source == "Static") - (host1.source == "Static");
    });
    hosts.forEach(function(host, index) {
      let formattedRSSI = (host.rssi && host.rssi != 0 && host.active != "false") ? host.rssi + " dBm" : "N/A";
      let formattedSpeed = (host.speed && host.active != "false") ? ((host.speed == 0) ? "Auto" : (host.speed + " Mbps")) : "N/A";
      let changeSourceButton = (host.source == "Static" ? '<button class="btn del-device" title="Release this device&#039;s IP address back to the DHCP server.">X</button>' : (host.network_type == "guest" ? '<button disabled class="btn disabled add-device" title="This device is connected to the guest network and its IP address cannot be reserved.">+</button>' : '<button class="btn add-device" title="Statically reserve this device&#039;s IP address.">+</button>'));
      let row = $('<tr>' +
                  '<td><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" /></td></span>' +
                  '<td><u id="edit_host_name' + host.id + '" class="edit_host_name" >' + host.hostname + '</u><button class="edit-icon-class" id="edit_icon' + host.id +'"></button></td>' +
                  '<td>' + host.ipv4 + '</td>' +
                  '<td>' + formattedRSSI + '</td>' +
                  '<td>' + formattedSpeed + '</td>' +
                  '<td>' + host.connection + '</td>' +
                  '<td>' + changeSourceButton + '</td>' +
                  '</tr>'
      );
      // Create the deviceInfo array to store more information about the host.
      let deviceInfo = [];
      deviceInfo.push({label: "Status:", data: (host.active == "true") ? "Online" : "Offline"});
      deviceInfo.push({label: "Configuration:", data: (host.source == "Static") ? "Static" : "DHCP"});
      deviceInfo.push({label: "MAC Address:", data: host.mac});
      if (host.source == "DHCP") {
        deviceInfo.push({label: "IPv4 Address:", data: host.ipv4});
        deviceInfo.push({label: "Lease Time Remaining (IPv4):", data: host.ipv4_lease});
      }
      if (host.ipv6_global != "" && host.ipv6_global != "EMPTY") {
        deviceInfo.push({label: "Global Address (IPv6):", data: host.ipv6_global});
      }
      if (host.ipv6_local != "" && host.ipv6_local != "EMPTY") {
        deviceInfo.push({label: "Link-Local Address (IPv6):", data: host.ipv6_local});
      }
      if (host.ipv6_global != "" && host.ipv6_global != "EMPTY") {
        deviceInfo.push({label: "Lease Time Remaining (IPv6):", data: host.ipv6_lease});
      }
      if (host.comments) {
        deviceInfo.push({label: "Comments:", data: host.comments});
      }
      let deviceInfoRow = $('<tr id="device-info-row' + host.id + '"><td colspan="7"></td></tr>');
      deviceInfo.forEach(function(info) {
        deviceInfoRow.find('td').append('<b> + ' + info.label + '</b> ' + info.data + '<br>');
      });
      deviceInfoRow.hide();
      // Display the online/offline image depending on whether or not the host is active.
      row.addClass(host.active == "true" ? "on" : "off");
      if (index % 2 != 0) {
        row.addClass("odd");
        deviceInfoRow.addClass("odd");
      }
      // Event handlers
      row.find(".add-device").on("click", function() {
        $.virtualDialog({
          title: 'Manually Add Static Device',
          width: '715px',
          content: '<div class="module forms">' +
                   '  <form id="pageForm">' +
                   '    <div class="form-row odd">' +
                   '      <span class="readonlyLabel">Host Name:</span><span class="value"><input id="edit_host" name="edit_host" type="text"></input></span>' +
                   '    </div>' +
                   '    <div class="form-row">' +
                   '      <span class="readonlyLabel">IPv4 Address:</span><span id="edit_ip" name="edit_ip" class="value"></span>' +
                   '    </div>' +
                   '    <div class="form-row odd">' +
                   '      <span class="readonlyLabel">MAC Address:</span><span id="edit_mac" name="edit_mac" class="value"></span>' +
                   '    </div>' +
                   '    <div class="form-row">' +
                   '      <span class="readonlyLabel">Comments:</span><span class="value"><textarea id="edit_comments" name="edit_comments" rows="3" cols="24" maxlength="63" style="resize:none"></textarea></span>' +
                   '    </div>' +
                   '    <div class="form-row form-btn">' +
                   '      <input id="save" type="button" class="btn" value="Save">' +
                   '      <input id="cancel" type="button" class="btn alt" value="Cancel">' +
                   '    </div>' +
                   '  </form>' +
                   '</div>'
      });
      let edit_ip = $("#edit_ip").inputGroup(INPUTGROUP_IPV4_CONFIG);
      let edit_mac = $("#edit_mac").inputGroup(INPUTGROUP_MAC_CONFIG);
        edit_ip.setValue(host.ipv4);
        for (let i = 1; i <= 3; i++) {
          edit_ip.disableFields([i]);
        }
        edit_mac.setValue(host.mac);
        edit_mac.disableFields();
        $("#edit_host").val(host.hostname);
        $("#edit_comments").val(host.comments);
        $("#save").click(function() {
          if ($("#pageForm").valid()) {
            let staticConfig = {
              hostname: $("#edit_host").val(),
              ipv4: edit_ip.getValue(),
              mac: edit_mac.getValue(),
              comments: $("#edit_comments").val()
            };
            createServerRecord("actionHandler/ajaxSet_static_addresses.php", staticConfig, null, 60, function() {
              jAlert("The client's IP address may change only after it reconnects.",
                     "The changes were saved successfully!",
                     function() {location.reload();}
              );
            });
          }
        });
        $("#cancel").on("click", function() {
          $.virtualDialog("hide");
        });
        $(".inputgroup-box").on("focus", function() {
          validator.resetForm();
        });
        let validator = $("#pageForm").validate({
          errorElement: "div",
          errorClass: "validator-error",
          onfocusout: false,
          rules: {
            edit_host: {
              required: true,
              device_name: true
            },
            edit_comments: {
              maxlength: 63
            }
          }
        });
        edit_ip.applyCommonValidationRules(validator, {
          maxlength: 3,
          required: true,
          max: 255,
          min: 0
        });
        edit_mac.applyCommonValidationRules(validator, {
          inputGroup_mac: edit_mac
        });
      });

      row.find(".del-device").on("click", function() {
        deleteServerRecord("actionHandler/ajaxSet_static_addresses.php", {mac: host.mac}, null, 60, function(hosts) {
          jAlert("The client's IP address may change only after it reconnects.",
                 "The changes were saved successfully!",
                 function() {location.reload();}
          );
        });
      });

      attachHostInfoEvent(row.find("u"), host.id);

      row.find(".edit-icon-class").on("click", function() {
        $("#save_cancel_hostname").show();
        refreshHostNameElements();
        $(this).hide();
        var hostId = getHostIndexWithPrefix(this, "edit_icon");
        switchHostNameElement($("#edit_host_name" + hostId), "input");
        //To focus on input field and place cursor at the end
        var hostName = $("#edit_host_name" + hostId);
        var hostNameLength = hostName.val().length;
        hostName.focus();
        hostName[0].setSelectionRange(hostNameLength, hostNameLength);
      });

      // Add the row to the table and then add the "more-info" row after it (which is hidden until clicked).
      tbody.append(row);
      row.after(deviceInfoRow);
    });

    $("#save_hostname").on("click", function() {
      refreshHostNameElements();
      var hostConfig = [];
      var isHostNameValid = true;
      var allowedChar = /^[A-Za-z0-9_ @.,\-\/]+$/ ;
      hosts.forEach(function(host) {
        var hostName = $("#edit_host_name" + host.id).text();
        if ((hostName == "") || (!(allowedChar.test(hostName)))) {
          isHostNameValid = false;
          jAlert("Please input a valid host name!");
          return;
        } else if (host.hostname != hostName) {
          hostConfig.push({id: host.id, hostname: hostName});
        }
      });
      if (isHostNameValid) {
        updateServerRecord("actionHandler/ajaxSet_connected_devices.php", {host_info: hostConfig}, null, 60, function() {
          window.location.reload(true);
        });
      }
    });

    $("#cancel_hostname").on("click", function() {
      window.location.reload(true);
    });
  });

  function getHostIndexWithPrefix(element, prefix) {
    return $(element).attr("id").substring(prefix.length);
  }

  function attachHostInfoEvent(element, id) {
    element.on("click", function() {
      $("#device-info-row" + id).toggle();
      refreshHostNameElements();
    }).on("mouseover", function() {
      $(this).css("cursor", "pointer");
    });
  }

  function refreshHostNameElements() {
    // Show edit icon of previously edited hosts.
    $(".edit-icon-class").show();

    // To switch to "u" label element when clicked elsewhere
    $(".edit_host_name").each(function(i, elem) {
      switchHostNameElement(elem, "label");
    });
  }

  function switchHostNameElement(element, type) {
    if (type == "input" && $(element).is("u")) {
      $(element).replaceWith("<input id='" + $(element).attr('id') + "' class='edit_host_name' value='" + $(element).text() +"' />");
    } else if (type == "label" && $(element).is("input")) {
      $(element).replaceWith("<u id='" + $(element).attr('id') + "' class='edit_host_name'>" + $(element).val() +"</u>");
      attachHostInfoEvent($("#" + $(element).attr('id')), getHostIndexWithPrefix(element, "edit_host_name"));
    }// Else element is already of specified type, no update needed.
  }

  $("#prefer_private").change(function() {
    updateServerRecord("actionHandler/ajaxSet_wireless_network_configuration.php", {prefer_private_enable: $(this).is(":checked")}, null, 60);
  });

  $(document).on('click', function(event) {
    if (!$(event.target).hasClass("edit-icon-class") && !$(event.target).hasClass("edit_host_name")) {
      refreshHostNameElements();
    }
  });
});
</script>

<div id="content">
  <h1 id="pageHeading"></h1>
  <div id="educational-tip">
    <p class="tip">View information about the devices currently connected to your network.</p>
  </div>
    <div id="host-table" class="module data">
    <h2>Online Devices</h2>
    <table class="data">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>Host Name</th>
          <th>IPv4 Address</th>
          <th>RSSI</th>
          <th>Speed</th>
          <th>Connection</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody><tr class="loading loading-row loading-odd"><td colspan="7"></td></tr></tbody>
    </table>
    <div id="save_cancel_hostname" class="form-row form-btn" style="display:none">
      <input id="save_hostname" type="button" class="btn" value="Save">
      <input id="cancel_hostname" type="button" class="btn alt" value="Cancel">
    </div>
    <div class="btn-group">
      <a href="wireless_network_configuration_wps.php" class="btn">Add Wi-Fi Protected Setup (WPS) Client</a>
    </div>
  </div>
</div>
  </div> <!-- end #main-content-->
    <!--Footer-->
    <div id="unsupported_browser_note"></div>
    <div id="footer">
      <ul id="footer-links">
  <li class="first-child">ARRIS</li>
  <li style="list-style:none outside none; margin-left:10px">&#8226;&nbsp;&nbsp;<a href="opensource.php" title="Details about Open Source Software used in this product" target="_blank">Open Source</a></li>
</ul>
    </div> <!-- end #footer -->
  </div> <!-- end #container -->
</body>
</html>
