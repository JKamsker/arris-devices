













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
              sjclEncryptObj.salt = "4e0b74b9e1150d44";
        sjclEncryptObj.iv = "c60406f0d92b228c";
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
  <script>
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
    var systemState = {"battery":{"level":-1},"internet":{"connected":true,"ip":"212.241.85.84"},"ethernet":{"enabled":true,"connected_devices":10},"wifi":{"enabled":true,"connected_devices":8},"moca":{"enabled":false,"connected_devices":""},"firewall":{"level":"Medium"}};
    refreshUserbar(systemState);
  </script>
</ul>
    </div>
    <div id="promote-sbc">
          </div>
    <!--Main Content-->
    <div id="main-content">
            


<script  type="text/javascript">  $(document).ready(function() {    arris.page.init("Login", "menu-login");  });</script><script type="text/javascript" src="/cmn/js/lib/qrcode.js?m=1718271171"></script>
<!--div id="nav"-->
<div style="float: left; margin: 0 20px 20px 0; width: 182px;">
  <form id="pageForm">


    <div class="login-block">
      <label for="username"><b>Username:</b></label>
      <input type="text" id="username" name="username" style="width: 175px; margin: 3px 0px 3px 0px" class="text" autocomplete="off" value="admin" />
      <label for="password" style="display: inherit; margin-top: 12px"><b>Password:</b></label>
      <input type="password" id="password" name="password" style="width: 175px; margin: 3px 0px 3px 0px" class="text" maxlength="64" autocomplete="off" />
      <input type="submit" class="btn" id="loginbtn" style="margin-top: 12px" value="Login" />

              <p style="color: #39baf1; font-weight: bold">Notice:  You have not yet created your own password. The default password is the same as the Preshared Key printed on your Gateway’s label.</p>
          </div>
  </form>

  
</div>

<script type="text/javascript">
$(document).ready(function() {
    let observedElement = $("#password")[0];
  let observer = new MutationObserver(function(mutationList) {
    for (let i = 0; i < mutationList.length; i++) {
      if (mutationList[i].type === "attributes" && observedElement.type !== "password") {
        observedElement.type = "password";
      }
    }
  });
  observer.observe(observedElement, {attributes: true});

  // Add QR codes
  
  var parameterMap = {username: "Username",
                      password: "Password"};
  $("#pageForm").validate({
    errorClass: "login-error error",
    errorElement: "p",
    errorContainer: "#error-msg-box",
    errorPlacement: function(error, element) {
      error.insertAfter(element);
    },
    invalidHandler: function(form, validator) {
      var errors = validator.numberOfInvalids();
      if (errors) {
        var message = $.validator.format("You missed {0} field(s). Missing fields are highlighted.", errors);
        $("div.error").html(message);
        $("div.error").show();
      } else {
        $("div.error").hide();
      }
    },
    rules: {
      username: {
        required: true,
        minlength: 3
      },
      password: {
        required: true
      }
    },
    messages: {
      username: {
        required: "Username cannot be blank. Please enter a valid username.",
        minlength: "Username must be at least 3 characters."
      },
      password: {
        required: "Password cannot be blank. Please enter a valid password."
      }
    },
    submitHandler: function() {
      var username = document.getElementById("username");
      var password = document.getElementById("password");
            username.value = username.value.toLowerCase();
      
      var loginConfig = {username: username.value, password: password.value};
      addUserInfoToEncryptObj((false ? "admin": username.value), password.value);

      updateServerRecord("actionHandler/ajaxSet_login.php", loginConfig, parameterMap, 60, function(data, statusCode, response) {
        if (data.session_overtake) {
          jConfirm("A user is already logged in to your Gateway.\nDo you want to logout the current user and continue to login?", null, function(ret) {
            if(ret) {
              updateServerRecord("actionHandler/ajaxSet_SessionActive.php", {session_takeover: true}, null, 60, function(data, statusCode, response) {
                sessionStorage.setItem('CsrfToken', response.getResponseHeader('X-CSRF-Token'));
                window.location = "at_a_glance.php"});
            }
          });
        } else {
          sessionStorage.setItem('CsrfToken', response.getResponseHeader('X-CSRF-Token'));
          window.location = "at_a_glance.php";
        }
      });
    }
  });
  if ($("#username").val() != "") {
    $("#password").focus();
  } else {
    $("#username").focus();
  }
  $("#password").val("");
  if (!isSupportedVersion()) {
    showBrowserDialog();
  }
  if (sessionStorage.getItem('session_expired')) {
    sessionStorage.removeItem('session_expired');
    jAlert(
      "Your session is not active. Please log in again.",
      "You have been logged out due to inactivity!"
    );
  }
});
</script>
<div id="content">
  <div id="educational-tip">
    <p class="tip">Please login to view your Wi-Fi password or to view and edit detailed network settings.</p>
  </div>
  <!-- Device Info -->
  <div class="module forms" id="device-info">
    <div>
      <h2>Device Info</h2>
      <div class="form-row">
        <span style="text-align: left" class="readonlyLabel">Software Image Name:</span>
        <span class="value">AR01.05.048.01_061324_735.NCS.10</span>
      </div>
    </div>
  </div>
  <!-- Wi-Fi Status -->
<div class="module forms block" id="wifi-status"><div><h2>Wi-Fi Status</h2></div><div class="form-row even"><span style="text-align: left" class="readonlyLabel"><u>Home</u></span></div><div class="form-row even"><span class="on"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" /></span></span><span class="locks locked"><img src="/cmn/img/locks.png?m=1718271172" /></span><span class="readonlyLabel ssidLabel">2.4GHz SSID:</span><span class="value ssidValue">sk_1337_2.4</span></div><div class="form-row even"><span class="on"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" /></span></span><span class="locks locked"><img src="/cmn/img/locks.png?m=1718271172" /></span><span class="readonlyLabel ssidLabel">5GHz SSID:</span><span class="value ssidValue">sk_1337</span></div><div class="form-row odd"><span style="text-align: left" class="readonlyLabel"><u>Guest</u></span></div><div class="form-row odd"><span class="off"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" /></span></span><span class="locks locked"><img src="/cmn/img/locks.png?m=1718271172" /></span><span class="readonlyLabel ssidLabel">2.4GHz SSID:</span><span class="value ssidValue">Liwest1662-4</span></div><div class="form-row odd"><span class="off"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" /></span></span><span class="locks locked"><img src="/cmn/img/locks.png?m=1718271172" /></span><span class="readonlyLabel ssidLabel">5GHz SSID:</span><span class="value ssidValue">Liwest1662-5G-4</span></div></div><div class="module block" id="home-network"><div><h2>Home Network</h2><div class="form-row"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" alt="Ethernet On" /></span> <span class="readonlyLabel">Ethernet</span></div><div class="form-row odd"><span class="on-off sprite_cont"><img src="/cmn/img/icn_on_off.png?m=1718271172" alt="Wi-Fi On" /></span> <span class="readonlyLabel">Wi-Fi</span></div><div class="form-row">        <span class="readonlyLabel">Firewall Security Level:</span> <span class="value">Medium</span>
      </div>
    </div>
  </div> <!-- end .module -->
</div><!-- end #content -->
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
