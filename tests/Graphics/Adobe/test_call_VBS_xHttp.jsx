var rnd = Math.random();

var method = "POST"
var url = "https://localhost/load-tests/splat/1/2/3?r=" + rnd;
var data = "[1,2,3]"


var response = xHttp(method, url, data)
$.writeln(response);

function xHttp(method, url, data) {
    if (File.fs == "Windows") {
        var xHttpFilePath;
        xHttpFilePath = $.fileName.split('/');
        xHttpFilePath.pop();
        xHttpFilePath = xHttpFilePath.join('/') + '/xhttp.vbs';
        var args = [method, url, data];

        var result = app.doScript(File(xHttpFilePath), ScriptLanguage.visualBasic, args);
    } else {
        var result = 'Error! You can only run this on a Adobe Windows InDesign Server.';
    }

    return result;
}

