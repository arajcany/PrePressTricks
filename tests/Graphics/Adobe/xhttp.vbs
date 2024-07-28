dim xHttp: Set xHttp = createobject("MSXML2.ServerXMLHTTP")
dim responseBody
dim responseText
dim method
dim url
dim data
dim returnValue

On Error Resume Next

method = WScript.Arguments(0)
url = WScript.Arguments(1)
data = WScript.Arguments(2)

rem 'Method, URL, Async, User, Password
xHttp.Open method, url, False

rem '2 stands for SXH_OPTION_IGNORE_SERVER_SSL_CERT_ERROR_FLAGS
rem '13056 means ignore all server side cert error
xHttp.setOption 2, 13056

xHttp.setRequestHeader "Content-Type", "application/json"
xHttp.setRequestHeader "Content-Length", Len(data)

If method = "POST" Then
    xHttp.send data
Else
    xHttp.send
End If

If Err.Number <> 0 Then
    WScript.Echo "Error: " & Err.Description
Else
    rem 'read response body
    responseBody = xHttp.responseBody
    responseText = xHttp.responseText
    returnValue = responseText
    WScript.Echo returnValue
End If

On Error GoTo 0
