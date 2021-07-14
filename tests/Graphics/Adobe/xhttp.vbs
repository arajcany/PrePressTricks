dim xHttp: Set xHttp = createobject("MSXML2.ServerXMLHTTP")
dim responseBody
dim responseText
dim method
dim url
dim data
dim returnValue

method = arguments(0)
url = arguments(1)
data = arguments(2)

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

rem 'read response body
responseBody = xHttp.responseBody
responseText = xHttp.responseText

returnValue = responseText
