<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>QLC+ Web API Test</title>
<script type="text/javascript">

// the WebSocket instance
var websocket;
var isConnected = false;
// the websocket host location
var wshost = "192.168.1.99:9999";
var onload = function() {
	connectToWebSocket(wshost);
};
// helper function to send QLC+ API commands
function requestAPI(cmd) 
{
  if (isConnected == true)
    websocket.send("QLC+API|" + cmd);
  else
    alert("You must connect to QLC+ WebSocket first !");
}

// helper function to send a QLC+ API with one parameter.
// The specified parameter is not a value, but a CSS object
// from which a value is retrieved (usually a <input> box)
function requestAPIWithParam(cmd, paramObjName) 
{
  var obj = document.getElementById(paramObjName);
  if (obj)
  {
    if (isConnected == true)
      websocket.send("QLC+API|" + cmd + "|" + obj.value);
    else
      alert("You must connect to QLC+ WebSocket first !");
  }
}

function requestChannelsRange(cmd, uniObjName, addressObjName, rangeObjName) 
{
  var uniObj = uniObjName;
  var addrObj = addressObjName;
  var rangeObj = rangeObjName;
  if (uniObj && addrObj && rangeObj)
  {
    if (isConnected == true) {
	var message = "QLC+API|" + cmd + "|" + uniObj + "|" + addrObj + "|" + rangeObj;
	console.log(message);
      websocket.send(message);
    } else
      alert("You must connect to QLC+ WebSocket first !");
  }
}

function setSimpleDeskChannel(addressObjName, channelValueObjName)
{
    if (isConnected == true) {
	var message = "CH|" + addressObjName + "|" + channelValueObjName;
	console.log("SETTING:");
	console.log(message);
      websocket.send(message);
    } else
      alert("You must connect to QLC+ WebSocket first !");
}

function vcWidgetSetValue(wIDObjName, wValueObjName)
{
  var wObj = document.getElementById(wIDObjName);
  var valObj = document.getElementById(wValueObjName);
  if (wObj && valObj)
  {
    if (isConnected == true)
      websocket.send(wObj.value + "|" + valObj.value);
    else
      alert("You must connect to QLC+ WebSocket first !");
  }
}

function vcCueListControl(clIDObjName, clOpObjName, clStepObjName)
{
  var clObj = document.getElementById(clIDObjName);
  var opObj = document.getElementById(clOpObjName);
  var stepObj = document.getElementById(clStepObjName);
  if (clObj && opObj)
  {
    if (isConnected == true)
    {
      if (opObj.value == "STEP")
        websocket.send(clObj.value + "|" + opObj.value + "|" + stepObj.value);
      else
        websocket.send(clObj.value + "|" + opObj.value);
    }
    else
      alert("You must connect to QLC+ WebSocket first !");
  }
}

function vcFrameControl(frIDObjName, frOperation)
{
  var frObj = document.getElementById(frIDObjName);
  var opObj = document.getElementById(frOperation);

  if (frObj && opObj)
  {
    if (isConnected == true)
    {
        websocket.send(frObj.value + "|" + opObj.value);
    }
    else
      alert("You must connect to QLC+ WebSocket first !");
  }
}

function connectToWebSocket(host) {
  var url = 'ws://' + host + '/qlcplusWS';
  websocket = new WebSocket(url);
  // update the host information
  wshost = "http://" + host;

  websocket.onopen = function(ev) {
    //alert("QLC+ connection successful");
    document.getElementById('connStatus').innerHTML = "<font color=green>Connected</font>";
    isConnected = true;
get_all_values();
  };

  websocket.onclose = function(ev) {
    alert("QLC+ connection lost !");
  };

  websocket.onerror = function(ev) {
    alert("QLC+ connection error!");
  };
 
  // WebSocket message handler. This is where async events
  // will be shown or processed as needed
  websocket.onmessage = function(ev) {
    // Uncomment the following line to display the received message
    //alert(ev.data);

    // Event data is formatted as follows: "QLC+API|API name|arguments"
    // Arguments vary depending on the API called

    var msgParams = ev.data.split('|');
    
    if (msgParams[0] == "QLC+API")
    {
      if (msgParams[1] == "getFunctionsNumber")
	document.getElementById('getFunctionsNumberBox').innerHTML = msgParams[2];
      
      // Arguments is an array formatted as follows: 
      // Function ID|Function name|Function ID|Function name|...
      else if (msgParams[1] == "getFunctionsList")
      {
	var tableCode = "<table class='apiTable'><tr><th>ID</th><th>Name</th></tr>";
	for (i = 2; i < msgParams.length; i+=2)
	{
	  tableCode = tableCode + "<tr><td>" + msgParams[i] + "</td><td>" + msgParams[i + 1] + "</td></tr>";
	}
	tableCode += "</table>";
	document.getElementById('getFunctionsListBox').innerHTML = tableCode;
      }

      else if (msgParams[1] == "getFunctionType")
	document.getElementById('getFunctionTypeBox').innerHTML = msgParams[2];

      else if (msgParams[1] == "getFunctionStatus")
	document.getElementById('getFunctionStatusBox').innerHTML = msgParams[2];

      else if (msgParams[1] == "getWidgetsNumber")
	document.getElementById('getWidgetsNumberBox').innerHTML = msgParams[2];
      
      // Arguments is an array formatted as follows: 
      // Widget ID|Widget name|Widget ID|Widget name|...
      else if (msgParams[1] == "getWidgetsList")
      {
	var tableCode = "<table class='apiTable'><tr><th>ID</th><th>Name</th></tr>";
	for (i = 2; i < msgParams.length; i+=2)
	{
	  tableCode = tableCode + "<tr><td>" + msgParams[i] + "</td><td>" + msgParams[i + 1] + "</td></tr>";
	}
	tableCode += "</table>";
	document.getElementById('getWidgetsListBox').innerHTML = tableCode;
      }
      
      else if (msgParams[1] == "getWidgetType")
	document.getElementById('getWidgetTypeBox').innerHTML = msgParams[2];
	
      else if (msgParams[1] == "getWidgetStatus")
      {
	var status = msgParams[2];
	if (msgParams[2] == "PLAY")
	  status = msgParams[2] + "(Step: " + msgParams[3] + ")";
	document.getElementById('getWidgetStatusBox').innerHTML = status;
      }
      
      else if (msgParams[1] == "getChannelsValues")
      {
	set_inputs_from_message(msgParams);
/*	var tableCode = "<table class='apiTable'><tr><th>Index</th><th>Value</th><th>Type</th></tr>";
	for (i = 2; i < msgParams.length; i+=3)
	{
	  tableCode = tableCode + "<tr><td>" + msgParams[i] + "</td><td>" + msgParams[i + 1] + "</td><td>" + msgParams[i + 2] + "</td></tr>";
	}
	tableCode += "</table>";
	document.getElementById('requestChannelsRangeBox').innerHTML = tableCode;*/
      }
    }
  };
};

function loadProject () {
  var formAction = wshost + "/loadProject";
  document.getElementById('lpForm').action = formAction;
}

</script>

<style type="text/css">

body { 
  background-color: #45484d;
  color: white;
  font:normal 18px/1.2em sans-serif;
}

iframe {
  position: absolute;
  display: block;

  height: 100%;
  width: 100%;

  -moz-border-radius: 12px;
  -webkit-border-radius: 12px; 
  border-radius: 12px; 

  -moz-box-shadow: 4px 4px 14px #000; 
  -webkit-box-shadow: 4px 4px 14px #000; 
  box-shadow: 4px 4px 14px #000; 
}

#prjBox {
  position: absolute;
  width: 50%;
  height: 70%;
  margin-top: 0px;
  margin-left: 47%;
}

.apiTable {
  border-collapse: collapse;
}

.apiTable th {
  font-size: 18px;
  color: white;
  border: solid 1px white;
}

.apiTable tr {
  font-size: 14px; 
  border: solid 1px white;
}

.apiTable td {
  border: solid 1px white;
  padding: 2px 5px 2px 5px;
  margin: 0 5px 0 5px;
}

.apiButton {
  display: table-cell; 
  vertical-align: middle;
  text-align: center;
  color: black;
  cursor:pointer;
  height: 30px;
  padding: 0 10px 0 10px;
  background: #4477a1;
  background: -webkit-gradient(linear, left top, left bottom, from(#81a8cb), to(#4477a1) );
  background: -moz-linear-gradient(-90deg, #81a8cb, #4477a1);

  -moz-border-radius: 6px;
  -webkit-border-radius: 6px; 
  border-radius: 6px; 
}

.apiButton:hover {
  background: #81a8cb;
  background: -webkit-gradient(linear, left top, left bottom, from(#4477a1), to(#81a8cb) );
  background: -moz-linear-gradient(-90deg, #4477a1, #81a8cb);
}

.resultBox {
  display: table-cell;
  vertical-align: middle;
  text-align: center;
  color: #000;
  width: 150px;
  height: 30px;
  background-color: #aaaaaa;
  border-radius: 6px; 
}

</style>

<body>
/*****************************************************************************************************/
<h1>Custom</h1>
<style>
	#Bridge input	{ height: 4em; width: 8em; }
</style>
<script>
var get_all_values = function() {
	requestChannelsRange('getChannelsValues', 1, 1, 100);
};
var set_inputs_from_message = function(vals) {
	vals.splice(0, 2); // Get rid of headers

	var inputs = document.querySelectorAll("#Bridge input");
	var map = [];
console.log(vals);
	for (var input of inputs)
		map[Number(input.name)] = input;

	for (var map_num = 1, val = 1; map_num <= 25; map_num += 3, val += 9) {
		map[map_num].value = rgbToHex(vals[val + 3*0], vals[val + 3*1], vals[val + 3*2]);
	}
	for (var map_num = 25+3, val = 1 + 9*9; map_num <= 32; map_num++, val += 3) {
		map[map_num].value = vals[val];
	}
};
function componentToHex(c) {
    var hex = Number(c).toString(16);
    return hex.length == 1 ? "0" + hex : hex;
}
function rgbToHex(r, g, b) {
    return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
}
var set_values_from_form = function() {
	var inputs = document.querySelectorAll("#Bridge input");
	for (var input of inputs) {
		var channel = Number(input.name);
		switch (true) {
			case channel < 28: 
				var hex = hexToRGB(input.value);
console.log(hex);
				if (hex !== false) {
					setSimpleDeskChannel(channel, hex.r);
					setSimpleDeskChannel(channel+1, hex.g);
					setSimpleDeskChannel(channel+2, hex.b);
				}
				break;
			case channel >= 28:
				setSimpleDeskChannel(channel, Number(input.value));
		}
	}
	return false;
};
function hexToRGB(hex) {
	var r = parseInt(hex.slice(1, 3), 16),
		g = parseInt(hex.slice(3, 5), 16),
		b = parseInt(hex.slice(5, 7), 16);
	if (isNaN(r) || isNaN(g) || isNaN(b))
		return false;
	var combined = {r: r, g: g, b: b};
	console.log(combined);
	return combined;
}

</script>
<form id="Bridge" onsubmit="return set_values_from_form()">
<div>
1: <input type="color" name="1"><br>
2: <input type="color" name="4"><br>
3: <input type="color" name="7"><br>
4: <input type="color" name="10"><br>
5: <input type="color" name="13"><br>
6: <input type="color" name="16"><br>
7: <input type="color" name="19"><br>
8: <input type="color" name="22"><br>
9: <input type="color" name="25"><br>
10: <input type="range" name="28" min=0 max=255><br>
11: <input type="range" name="29" min=0 max=255><br>
12: <input type="range" name="30" min=0 max=255><br>
13: <input type="range" name="31" min=0 max=255><br>
14: <input type="range" name="32" min=0 max=255><br>
</div>
<button type="submit" value="Update" onclick="set_values_from_form()">Update</button>
</form>
<h2>Q Light Controller+ Web API test page</h2>

<!-- ############## Project box to display what QLC+ is doing ####################### -->
<div id="prjBox"><iframe name="projectFrame" src="" id="projectFrame"></iframe></div>

<!-- ############## Websocket connection code ####################### -->
QLC+ IP: 
<input type="text" id="qlcplusIP" value="192.168.1.99:9999"/>
<input type="button" value="Connect" onclick="javascript:connectToWebSocket(document.getElementById('qlcplusIP').value);">
<div id="connStatus" style="display: inline-block;"><font color=red>Not connected</font></div>
<br><br>

<!-- ############## Project load code ####################### -->
<form id="lpForm" onsubmit="loadProject()" method="POST" enctype="multipart/form-data" target="projectFrame">
Load a project:
<input id="loadTrigger" type="file" onchange="document.getElementById('submitTrigger').click();" name="qlcprj">
<input id="submitTrigger" type="submit">
</form>
<br><br>

<!-- ############## Individual API tests ####################### -->

<table class="apiTable" width=45%>
 <tr>
  <th width=30%><b>API Function</b></th>
  <th width=30%><b>Description</b></th>
  <th width=40%><b>Result</b></th>
 </tr>
 
<!-- ############## Channels API tests ####################### -->

 <tr>
  <td colspan="3" align="center"><b>Channels APIs</b></td>
 </tr>
  <tr>
  <td>
    <div class="apiButton" onclick="javascript:requestChannelsRange('getChannelsValues', 'chUniIdx', 'chDMXaddr', 'chRange');">getChannelsValues</div>
    Universe index:<input id="chUniIdx" type="text" value="1">
    DMX start address:<input id="chDMXaddr" type="text" value="1">
    Channels count:<input id="chRange" type="text" value="16">
  </td>
  <td>Retrieve the specified number of DMX values for the given universe, starting at the given address.
      Note that indices start from 1 and not from 0.</td>
  <td><div id="requestChannelsRangeBox" style="height: 150px; overflow-y: scroll;"></div></td>
 </tr>

<!-- ############## Functions API tests ####################### -->
 
 <tr>
  <td colspan="3" align="center"><b>Function APIs</b></td>
 </tr>
 <tr>
  <td><div class="apiButton" onclick="javascript:requestAPI('getFunctionsNumber');">getFunctionsNumber</div></td>
  <td>Retrieve the number of functions loaded</td>
  <td><div id="getFunctionsNumberBox" class="resultBox"></div></td>
 </tr>
 <tr>
  <td><div class="apiButton" onclick="javascript:requestAPI('getFunctionsList');">getFunctionsList</div></td>
  <td>Retrieve the list of functions with their ID and name</td>
  <td><div id="getFunctionsListBox" style="height: 150px; overflow-y: scroll;"></div></td>
 </tr>
 <tr>
  <td>
    <div class="apiButton" onclick="javascript:requestAPIWithParam('getFunctionType', 'fTypeID');">getFunctionType</div>
    Function ID:<input id="fTypeID" type="text" value="0">
  </td>
  <td>Retrieve the type of a function with the given ID</td>
  <td><div id="getFunctionTypeBox" class="resultBox"></div></td>
 </tr>
 <tr>
  <td>
    <div class="apiButton" onclick="javascript:requestAPIWithParam('getFunctionStatus', 'fStatusID');">getFunctionStatus</div>
    Function ID:<input id="fStatusID" type="text" value="0">
  </td>
  <td>Retrieve the status of a function with the given ID. Possible values are "Running", "Stopped" and "Undefined"</td>
  <td><div id="getFunctionStatusBox" class="resultBox"></div></td>
  </tr>
  
<!-- ############## Widgets API tests ####################### -->

 <tr>
  <td colspan="3" align="center"><b>Virtual Console Widget APIs</b></td>
 </tr>
 <tr>
  <td><div class="apiButton" onclick="javascript:requestAPI('getWidgetsNumber');">getWidgetsNumber</div></td>
  <td>Retrieve the number of widgets loaded</td>
  <td><div id="getWidgetsNumberBox" class="resultBox"></div></td>
 </tr>
 <tr>
  <td><div class="apiButton" onclick="javascript:requestAPI('getWidgetsList');">getWidgetsList</div></td>
  <td>Retrieve the list of Virtual Console Widgets with their ID and name</td>
  <td><div id="getWidgetsListBox" style="height: 150px; overflow-y: scroll;"></div></td>
 </tr>
 <tr>
  <td>
    <div class="apiButton" onclick="javascript:requestAPIWithParam('getWidgetType', 'wTypeID');">getWidgetType</div>
    Widget ID:<input id="wTypeID" type="text" value="0">
  </td>
  <td>Retrieve the type of a Virtual Console Widget with the given ID</td>
  <td><div id="getWidgetTypeBox" class="resultBox"></div></td>
 </tr>
 <tr>
  <td>
    <div class="apiButton" onclick="javascript:requestAPIWithParam('getWidgetStatus', 'wStatusID');">getWidgetStatus</div>
    Widget ID:<input id="wStatusID" type="text" value="0">
  </td>
  <td>Retrieve the status of a Virtual Console Widget with the given ID</td>
  <td><div id="getWidgetStatusBox" class="resultBox"></div></td>
 </tr>
 
<!-- ############## High rate API tests ####################### -->

 <tr>
  <td colspan="3" align="center"><b>High rate APIs</b></td>
 </tr>
 <tr>
  <td colspan="3">Due to the nature of some type of transmissions (for example a slider changing rapidly),
                  there are a few WebSocket operations stripped down to avoid useless overhead of data.<br>
                  So, instead of transmitting every time the "QLC+API|API name" information, direct calls
                  are here used to accomplish fast operations.
  </td>
 </tr>

 <tr>
  <td>
   <div class="apiButton" onclick="javascript:setSimpleDeskChannel('sdDMXAddress', 'sdDMXValue');">Simple Desk channel set</div>

   Absolute DMX address:<input id="sdDMXAddress" type="text" value="1">
   Value:<input id="sdDMXValue" type="text" value="100">
  </td>
  <td colspan="2">
   This API sets the value of a single channel of the QLC+ Simple Desk. The parameters to send are:<br>
   <b>Absolute DMX address</b>: this is the address of the DMX channel you want to set. It is absolute in the sense
   that the universe information is implicit in the address itself. So for example addresses on the first
   universe will range from 1 to 512, while addresses on the second universe will range from 513 to 1024,
   and so on.<br>
   <b>Value</b>: the value of the DMX channel to set in a range from 0 to 255.
  </td>
 </tr>

 <tr>
  <td>
   <div class="apiButton" onclick="javascript:vcWidgetSetValue('basicWidgetID', 'basicWidgetValue');">Basic widget value set</div>

   Widget ID:<input id="basicWidgetID" type="text" value="0">
   Value:<input id="basicWidgetValue" type="text" value="255">
  </td>
  <td colspan="2">
    This API is the direct way to set a Virtual Console widget value. It can be used for Buttons, Sliders and
    Audio Triggers. The value to set depends on the widget type itself. Buttons and Audio triggers will only
    support values 0 (= off) and 255 (= on) while Sliders will accept all the values in the 0-255 range.
  </td>
 </tr>
 
 <tr>
  <td>
   <div class="apiButton" onclick="javascript:vcCueListControl('clWidgetID', 'clOperation', 'clStep');">Cue list control</div>

   Cue List ID:<input id="clWidgetID" type="text" value="0">
   Operation:<input id="clOperation" type="text" value="PLAY">
   Step (optional):<input id="clStep" type="text" value="1">
  </td>
  <td colspan="2">
    This API demonstrates how to control a Virtual Console Cue List widget. The parameters to be used are:<br>
    <b>Cue List ID</b>: The Cue List widget ID as retrieved with the 'getWidgetsList' API<br>
    <b>Operation</b>: The Cue List operation to perform. Possible values are 'PLAY', 'NEXT', 'PREV' and 'STEP'.
    Only the 'STEP' operation requires a third parameter. The 'PLAY' operation will stop the Cue List if called
    twice.<br>
    <b>Step</b>: The Cue List step index to play. Index starts from 0.
  </td>
 </tr>

 <tr>
  <td>
   <div class="apiButton" onclick="javascript:vcFrameControl('frWidgetID', 'frOperation');">Multipage frame control</div>

   Frame ID:<input id="frWidgetID" type="text" value="0">
   Operation:<input id="frOperation" type="text" value="NEXT_PG">
  </td>
  <td colspan="2">
    This API demonstrates how to change page of a Virtual Console Frame widget in multipage mode. 
    The parameters to be used are:<br>
    <b>Frame ID</b>: The Frame widget ID as retrieved with the 'getWidgetsList' API<br>
    <b>Operation</b>: The Frame operation to perform. Accepted values are 'NEXT_PG' and 'PREV_PG'.
  </td>
 </tr>

</table>
</body>
</html>
